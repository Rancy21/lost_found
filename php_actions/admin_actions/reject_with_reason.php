<?php

session_start();
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../includes/notification_helpers.php";

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"] || $_SESSION["user_role"] !== "admin") {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$post_id = $_POST['post_id'] ?? null;
$reason = trim($_POST['reason'] ?? '');
$admin_id = $_SESSION['user_id'];

if (!$post_id || !$reason) {
    echo json_encode(['status' => 'error', 'message' => 'Post ID and reason are required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get post owner before rejection
    $post_owner = getPostOwner($post_id);
    if (!$post_owner) {
        throw new Exception("Post not found");
    }

    // Get post title for notification
    $post_title = getPostTitle($post_id);

    // Update post status to rejected
    $stmt = $conn->prepare("UPDATE posts SET status = 'rejected' WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to reject post");
    }
    $stmt->close();

    // Get notification template
    $template = getNotificationTemplate('rejected', $post_title, $reason);

    // Create notification
    if (!createNotification($post_owner, $post_id, 'rejected', $template['title'], $template['message'], $reason, $admin_id)) {
        throw new Exception("Failed to create notification");
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Post rejected successfully and user notified'
    ]);

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
