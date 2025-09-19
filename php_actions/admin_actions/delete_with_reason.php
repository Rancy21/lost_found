<?php

// File: /home/larryck/Web Projects/lost_found/php_actions/admin_actions/delete_with_reason.php

session_start();
require_once '../../config/config.php';
require_once '../../includes/notification_helpers.php';

header('Content-Type: application/json; charset=UTF-8');

// Check if user is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$post_id = $_POST['post_id'] ?? null;
$reason = $_POST['reason'] ?? null;
$admin_id = $_SESSION['user_id'];

if (!$post_id || !$reason) {
    echo json_encode(['status' => 'error', 'message' => 'Post ID and reason are required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get post details including image_url and user_id before deletion
    $stmt = $conn->prepare('SELECT image_url, user_id, description FROM posts WHERE post_id = ?');
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('i', $post_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if (!$post) {
        throw new Exception("Post not found");
    }

    $image_url = $post['image_url'];
    $user_id = $post['user_id'];
    $post_description = $post['description'] ?? 'Your post';
    $stmt->close();

    // Debug: Log the values we're about to insert
    error_log("DEBUG: About to create notification for user_id: $user_id, post_id: $post_id, admin_id: $admin_id");
    error_log("DEBUG: Reason: $reason");

    // First, let's check the notifications table structure
    $check_table = $conn->query("DESCRIBE notifications");
    if ($check_table) {
        $columns = [];
        while ($row = $check_table->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        error_log("DEBUG: Notifications table columns: " . implode(', ', $columns));
    }

    // Try a simpler notification insert first
    $notification_stmt = $conn->prepare("
        INSERT INTO notifications (user_id, post_id, type, title, message, reason, admin_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$notification_stmt) {
        throw new Exception("Failed to prepare notification statement: " . $conn->error);
    }

    $notification_type = 'deleted';
    $notification_title = "Post Deleted";
    $notification_message = "Your post '{$post_description}' has been deleted by an administrator. Reason: {$reason}";

    $notification_stmt->bind_param("iissssi", $user_id, $post_id, $notification_type, $notification_title, $notification_message, $reason, $admin_id);

    if (!$notification_stmt->execute()) {
        error_log("DEBUG: Notification insert failed: " . $notification_stmt->error);
        throw new Exception("Failed to create notification: " . $notification_stmt->error);
    }

    $notification_id = $conn->insert_id;
    error_log("DEBUG: Notification created with ID: $notification_id");

    $notification_stmt->close();

    // Verify the notification was actually inserted
    $verify_stmt = $conn->prepare("SELECT notification_id FROM notifications WHERE user_id = ? AND post_id = ? ORDER BY created_at DESC LIMIT 1");
    $verify_stmt->bind_param("ii", $user_id, $post_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows > 0) {
        error_log("DEBUG: Notification verified in database");
    } else {
        error_log("DEBUG: WARNING - Notification not found in database after insert!");
    }
    $verify_stmt->close();

    // Delete the post from database
    $delete_stmt = $conn->prepare("UPDATE posts set status = ? WHERE post_id = ?");
    if (!$delete_stmt) {
        throw new Exception("Failed to prepare delete statement: " . $conn->error);
    }

    $delete_stmt->bind_param("si", $notification_type, $post_id);

    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete post: " . $delete_stmt->error);
    }

    if ($delete_stmt->affected_rows === 0) {
        throw new Exception("Post not found or already deleted");
    }

    $delete_stmt->close();

    // Delete associated image file if it exists
    $image_deleted = false;
    if (!empty($image_url)) {
        $image_path = __DIR__ . '/../../' . $image_url;

        if (file_exists($image_path)) {
            if (unlink($image_path)) {
                $image_deleted = true;
            }
        }
    }

    // Commit transaction
    $conn->commit();
    error_log("DEBUG: Transaction committed successfully");

    // Prepare success message
    $success_message = "Post deleted successfully and user notified!";
    if ($image_deleted) {
        $success_message .= " Image file also removed.";
    }

    echo json_encode([
        'status' => 'success',
        'message' => $success_message
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("DEBUG: Transaction rolled back due to error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
