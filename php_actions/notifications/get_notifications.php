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

// Get parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? min(50, max(5, intval($_GET['limit']))) : 10;
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$offset = ($page - 1) * $limit;

// Debug: Log the parameters
error_log("Notifications Debug - User ID: $user_id, Page: $page, Limit: $limit, Type: $type_filter, Status: $status_filter");

// Build WHERE clause
$where_conditions = ["user_id = ?"];
$params = [$user_id];
$param_types = "i";

if ($type_filter !== 'all') {
    $where_conditions[] = "type = ?";
    $params[] = $type_filter;
    $param_types .= "s";
}

if ($status_filter === 'read') {
    $where_conditions[] = "is_read = TRUE";
} elseif ($status_filter === 'unread') {
    $where_conditions[] = "is_read = FALSE";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

try {
    // Debug: Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Notifications table does not exist']);
        exit;
    }

    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM notifications $where_clause";
    error_log("Count SQL: $count_sql");
    error_log("Params: " . print_r($params, true));

    $count_stmt = $conn->prepare($count_sql);
    if (!$count_stmt) {
        error_log("Count prepare error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare count query: ' . $conn->error]);
        exit;
    }

    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_count = $count_result->fetch_assoc()['total'];
    $count_stmt->close();

    error_log("Total count: $total_count");

    // Calculate total pages
    $total_pages = ceil($total_count / $limit);

    // Get notifications
    $sql = "SELECT notification_id, type, title, message, reason, is_read, created_at 
            FROM notifications 
            $where_clause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?";

    error_log("Main SQL: $sql");

    // Add limit and offset parameters
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Main prepare error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare notifications query: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    error_log("Query executed. Rows returned: " . $result->num_rows);

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['notification_id'],
            'type' => $row['type'],
            'title' => $row['title'],
            'message' => $row['message'],
            'reason' => $row['reason'],
            'is_read' => (bool)$row['is_read'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'notifications' => $notifications,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_count' => $total_count,
            'per_page' => $limit
        ],
        'total_pages' => $total_pages // For backward compatibility
    ]);

} catch (Exception $e) {
    error_log("Exception in get_notifications.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
