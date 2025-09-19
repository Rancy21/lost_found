<?php

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_role'] !== 'user') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit;
}

require_once __DIR__ . "/../../config/config.php";
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "GET method required"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT 
    p.post_id,
    p.title as subject,
    u.id as other_user_id,
    u.full_name as other_user_name,
    MAX(m.timestamp) as last_message_time,
    (SELECT content FROM messages 
     WHERE post_id = p.post_id 
     AND ((sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?))
     ORDER BY timestamp DESC LIMIT 1) as last_message
    FROM messages m
    JOIN users u ON u.id = m.receiver_id
    JOIN posts p ON p.post_id = m.post_id
    WHERE m.sender_id = ?
    GROUP BY p.post_id, u.id
    ORDER BY last_message_time DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = [
            'post_id' => $row['post_id'],
            'subject' => $row['subject'],
            'other_user_id' => $row['other_user_id'],
            'other_user_name' => $row['other_user_name'],
            'last_message_time' => $row['last_message_time'],
            'last_message' => $row['last_message'],
            'unread_count' => 0 // Not applicable for sent view
        ];
    }

    echo json_encode([
        "status" => "success",
        "conversations" => $conversations
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch sent messages: " . $e->getMessage()
    ]);
}
