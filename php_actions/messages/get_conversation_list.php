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

try {

    $stmt = $conn->prepare(
        "SELECT 
    CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS other_user_id,
    MAX(m.timestamp) AS last_message_time,
    SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count,
    u.full_name as other_user_name
    FROM messages m
    JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY other_user_id
    ORDER BY last_message_time DESC
    "
    );
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    $messages = [];

    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'other_user_id' => $row['other_user_id'],
            'last_message_time' => $row['last_message_time'],
            'unread_count' => $row['unread_count'],
            'other_user_name' => $row['other_user_name'],
        ];
    }
    $stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'messages' => $messages,
        'count' => count($messages)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching messages: ' . $e->getMessage()
    ]);
}
