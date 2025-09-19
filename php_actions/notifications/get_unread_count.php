<?php

session_start();
require_once __DIR__ . "/../../config/config.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = FALSE");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $unread_count = (int)$row['unread_count'];
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'unread_count' => $unread_count
    ]);
} else {
    $stmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Failed to get unread count']);
}
