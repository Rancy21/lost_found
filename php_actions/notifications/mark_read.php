<?php


session_start();
require_once __DIR__ . "/../../config/config.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$notification_id = $input['notification_id'] ?? null;
$mark_all = $input['mark_all'] ?? false;
$user_id = $_SESSION['user_id'];

if ($mark_all) {
    // Mark all notifications as read for the user
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        echo json_encode([
            'status' => 'success',
            'message' => "Marked $affected_rows notifications as read"
        ]);
    } else {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark notifications as read']);
    }
} else {
    // Mark specific notification as read
    if (!$notification_id) {
        echo json_encode(['status' => 'error', 'message' => 'Notification ID required']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
    } else {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark notification as read']);
    }
}
