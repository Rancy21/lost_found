<?php

session_start();
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

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$user_id = $input['user_id'] ?? null;
$post_id = $input['post_id'] ?? null;
$type = $input['type'] ?? null;
$reason = $input['reason'] ?? null;
$admin_id = $_SESSION['user_id'] ?? null;

// Validate required fields
if (!$user_id || !$post_id || !$type) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Validate notification type
$valid_types = ['approved', 'rejected', 'deleted', 'pending'];
if (!in_array($type, $valid_types)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification type']);
    exit;
}

// Check if user exists
if (!userExists($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// Get post title for notification
$post_title = getPostTitle($post_id);

// Get notification template
$template = getNotificationTemplate($type, $post_title, $reason);

// Create notification
if (createNotification($user_id, $post_id, $type, $template['title'], $template['message'], $reason, $admin_id)) {
    echo json_encode(['status' => 'success', 'message' => 'Notification created successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create notification']);
}
