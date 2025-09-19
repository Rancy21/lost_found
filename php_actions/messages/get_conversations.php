<?php

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
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
$post_id = $_GET['post_id'] ?? null;
$other_user_id = $_GET['other_user_id'] ?? null;

if (!$post_id || !$other_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

try {
    $query = "SELECT 
        m.message_id,
        m.content,
        m.sender_id,
        m.timestamp,
        u.full_name as sender_name
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.post_id = ?
        AND (m.sender_id = ? OR m.receiver_id = ?)
        AND (m.sender_id = ? OR m.receiver_id = ?)
        ORDER BY m.timestamp ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $post_id, $user_id, $user_id, $other_user_id, $other_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['message_id'],
            'content' => $row['content'],
            'sender_id' => $row['sender_id'],
            'sender_name' => $row['sender_name'],
            'timestamp' => $row['timestamp']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'messages' => $messages
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching conversation: ' . $e->getMessage()
    ]);
}
