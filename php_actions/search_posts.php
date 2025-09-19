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

$search = $_GET['search'] ?? '';

try {
    if (empty($search)) {
        echo json_encode([
            'status' => 'success',
            'posts' => [],
            'count' => 0,
            'search' => $search
        ]);
        exit;
    }

    $searchParam = "%$search%";

    $sql = "SELECT p.post_id as post_id, p.date_posted as date_posted, p.description as description, p.image_url as image_url, p.location_name as location_name, p.status as status, p.type as type, u.id as user_id, u.full_name as full_name
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        WHERE u.id != ? AND (p.description LIKE ? OR p.location_name LIKE ?) and p.status = 'active'
        ORDER BY p.date_posted DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iss", $user_id, $searchParam, $searchParam);

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
        'search' => $search
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error searching posts: ' . $e->getMessage()
    ]);
}
