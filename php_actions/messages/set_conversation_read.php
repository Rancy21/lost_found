<?php

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_role'] !== 'user') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . "/../../config/config.php";
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}


if (!isset($_POST['other_user_id']) || empty($_POST['other_user_id']) || !isset($_POST['post_id']) || empty($_POST['post_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "The other user's id and post id are required"]);
    exit;
}


$other_user_id = intval($_POST['other_user_id']);
$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];

$is_read = 1;

try {
    $stmt = $conn -> prepare('UPDATE messages SET is_read = ? WHERE receiver_id = ? AND sender_id = ? AND post_id = ? AND is_read = 0');


    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iiii", $is_read, $user_id, $other_user_id, $post_id);


    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Messages marked as read', 'affected_rows' => $stmt->affected_rows]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error setting messages read: ' . $e->getMessage()
    ]);
}

$conn ->close();
