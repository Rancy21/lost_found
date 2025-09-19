<?php

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_role'] !== 'user') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . "/../../config/config.php";
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn ->prepare("SELECT count(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");


    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }


    $stmt->bind_param("i", $user_id, );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $unread_count = $row['unread_count'];
        echo json_encode(['status' => 'success', 'unread_count' => $unread_count]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No unread messages found']);
    }

    $stmt->close();


} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching unread count: ' . $e->getMessage()
    ]);
}
