<?php

session_start();
require_once __DIR__ . "/../../config/config.php";

// Add debugging (remove this in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$limit = 5; // Show only 5 recent notifications in dropdown

try {
    // Check if notifications table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Notifications table does not exist']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT notification_id, type, title, message, is_read, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");

    if (!$stmt) {
        error_log("Dropdown prepare error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare query: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['notification_id'],
            'type' => $row['type'],
            'title' => $row['title'],
            'message' => $row['message'],
            'is_read' => (bool)$row['is_read'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    error_log("Exception in get_notifications_dropdown.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
