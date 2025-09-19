<?php

// File: /home/larryck/Web Projects/lost_found/php_actions/messages/check_existing_conversation.php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_GET['post_id']) || !isset($_GET['other_user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$post_id = intval($_GET['post_id']);
$other_user_id = intval($_GET['other_user_id']);
$current_user_id = $_SESSION['user_id'];

try {
    // Check if there's an existing conversation between the current user and the other user for this post
    $query = "SELECT COUNT(*) as conversation_count 
              FROM messages 
              WHERE post_id = ? 
              AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $post_id, $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $has_conversation = $row['conversation_count'] > 0;

    echo json_encode([
        'status' => 'success',
        'has_conversation' => $has_conversation
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error checking conversation: ' . $e->getMessage()
    ]);
}

$conn->close();
