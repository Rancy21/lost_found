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


if (!isset($_POST['message_id']) || empty($_POST['message_id'])) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => "Message id is required"]);
    exit;
}


$message_id = $_POST['message_id'];

try {
    $stmt = $conn -> prepare("DELETE FROM messages WHERE message_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt -> bind_param("i", $message_id);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Message not found or already deleted']);
        exit;
    } else {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Message deleted successfully']);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error while deleting message: ' . $e->getMessage()
    ]);
}

$conn -> close();
