<?php

session_start();
require_once __DIR__ . "/../../config/config.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check if notifications table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Notifications table does not exist']);
        exit;
    }

    $metrics = [];

    // Get total notifications count
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $metrics['total'] = (int)$result->fetch_assoc()['total'];
    $stmt->close();

    // Get unread notifications count
    $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $metrics['unread'] = (int)$result->fetch_assoc()['unread'];
    $stmt->close();

    // Get approved notifications count
    $stmt = $conn->prepare("SELECT COUNT(*) as approved FROM notifications WHERE user_id = ? AND type = 'approved'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $metrics['approved'] = (int)$result->fetch_assoc()['approved'];
    $stmt->close();

    // Get rejected notifications count
    $stmt = $conn->prepare("SELECT COUNT(*) as rejected FROM notifications WHERE user_id = ? AND type = 'rejected'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $metrics['rejected'] = (int)$result->fetch_assoc()['rejected'];
    $stmt->close();

    // Get recent notifications count (last 24 hours)
    $stmt = $conn->prepare("SELECT COUNT(*) as recent FROM notifications WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $metrics['recent'] = (int)$result->fetch_assoc()['recent'];
    $stmt->close();

    // Get additional metrics
    $stmt = $conn->prepare("SELECT COUNT(*) as deleted FROM notifications WHERE user_id = ? AND type = 'deleted'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $metrics['deleted'] = (int)$result->fetch_assoc()['deleted'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM notifications WHERE user_id = ? AND type = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $metrics['pending'] = (int)$result->fetch_assoc()['pending'];
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'metrics' => $metrics
    ]);

} catch (Exception $e) {
    error_log("Exception in get_metrics.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
