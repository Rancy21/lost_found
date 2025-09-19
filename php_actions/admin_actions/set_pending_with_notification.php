<?php

session_start();
require_once '../../config/config.php';
require_once '../../includes/notification_helpers.php';

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
$admin_id = $_SESSION['user_id'];

if (!$post_id) {
    echo json_encode(['status' => 'error', 'message' => 'Post ID is required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get post owner
    $post_owner = getPostOwner($post_id);
    if (!$post_owner) {
        throw new Exception("Post not found");
    }

    // Update post status to pending
    $stmt = $conn->prepare("UPDATE posts SET status = 'pending' WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to update post status");
    }
    $stmt->close();

    // Get post title for notification
    $post_title = getPostTitle($post_id);

    // Get notification template
    $template = getNotificationTemplate('pending', $post_title);

    // Create notification
    if (!createNotification($post_owner, $post_id, 'pending', $template['title'], $template['message'], null, $admin_id)) {
        throw new Exception("Failed to create notification");
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Post set to pending successfully and user notified'
    ]);

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
