<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit;
}

require_once __DIR__ . "/../../config/config.php";

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$last_check_timestamp = isset($_GET['last_check']) ? $_GET['last_check'] : '1970-01-01 00:00:00';

try {
    // 1. Check for new messages since the last check
    $sql_new = "SELECT 1 FROM messages WHERE receiver_id = ? AND timestamp > ? LIMIT 1";
    $stmt_new = $conn->prepare($sql_new);
    if (!$stmt_new) {
        throw new Exception("Prepare failed (new): " . $conn->error);
    }
    $stmt_new->bind_param("is", $user_id, $last_check_timestamp);
    if (!$stmt_new->execute()) {
        throw new Exception("Execute failed (new): " . $stmt_new->error);
    }
    $stmt_new->store_result();
    $has_new_messages = $stmt_new->num_rows > 0;
    $stmt_new->close();

    // 2. Get the total unread count
    $unread_count = 0;
    $sql_count = "SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0";
    $stmt_count = $conn->prepare($sql_count);
    if (!$stmt_count) {
        throw new Exception("Prepare failed (count): " . $conn->error);
    }
    $stmt_count->bind_param("i", $user_id);
    if (!$stmt_count->execute()) {
        throw new Exception("Execute failed (count): " . $stmt_count->error);
    }
    $stmt_count->bind_result($unread_count);
    $stmt_count->fetch();
    $stmt_count->close();

    // Get current server time to send back to the client for the next poll
    $current_server_time = date("Y-m-d H:i:s");

    $conn->close();

    echo json_encode([
        "status" => "success",
        "has_new_messages" => $has_new_messages,
        "unread_count" => (int)$unread_count,
        "timestamp" => $current_server_time
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}