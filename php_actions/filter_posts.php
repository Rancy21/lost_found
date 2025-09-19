<?php

session_start();
require_once __DIR__ . "/../config/config.php";
header('Content-Type: application/json; charset=UTF-8');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

$filter = $_GET['filter'] ?? 'all';

try {
    $whereClause = "";
    $params = [];
    $types = "";

    // Add filter conditions
    if ($filter === 'lost') {
        $whereClause = "WHERE p.type = 'lost' and u.id != ? and p.status = 'active'";
    } elseif ($filter === 'found') {
        $whereClause = "WHERE p.type = 'found' and u.id != ? and p.status = 'active'";
    } elseif ($filter === 'recent') {
        $whereClause = "WHERE p.date_posted >= DATE_SUB(NOW(), INTERVAL 7 DAY) and u.id != ? and p.status = 'active'";
    }

    $sql = "SELECT p.post_id as post_id, p.date_posted as date_posted,p.description as description, p.image_url as image_url, p.location_name as location_name, p.status as status, p.type as type, u.id as user_id, u.full_name as full_name
                            from posts p join users u on p.user_id = u.id
            $whereClause 
            ORDER BY p.date_posted DESC";

    $stmt = $conn->prepare($sql);

    $stmt -> bind_param("i", $user_id);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $posts = [];

    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            'id' => $row['post_id'],
            'description' => $row['description'],
            'type' => $row['type'],
            'location_name' => $row['location_name'],
            'date_posted' => $row['date_posted'],
            'status' => $row['status'],
            'image_url' => $row['image_url'],
            'user_id' => $row['user_id'],
            'user_name' => $row['full_name'],
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'posts' => $posts,
        'count' => count($posts),
        'filter' => $filter
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error filtering posts: ' . $e->getMessage()
    ]);
}
