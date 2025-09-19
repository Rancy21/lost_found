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

if (empty($_POST['message']) || empty($_POST['receiver_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'message content and receiver id is required']);
    exit;
}


if (empty($_POST['post_id'])) {

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'post id is required']);
    exit;

}


$sender_id = $_SESSION['user_id'];

$receiver_id = intval($_POST['receiver_id']);

$post_id = intval($_POST['post_id']);

$content = $_POST['message'];


try {
    $stmt = $conn -> prepare('INSERT into messages(sender_id, receiver_id, content, post_id) values(?,?,?,?)');

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iisi", $sender_id, $receiver_id, $content, $post_id);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }


    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Message successfully sent!",
        "message_id" => $stmt->insert_id,
        "sender_email" => $_SESSION['user_email']
    ]);


    $stmt -> close();


} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Message creation failed: " . $e->getMessage()
    ]);
}


$conn->close();
