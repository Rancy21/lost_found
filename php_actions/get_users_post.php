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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$status = "active";

try {
    // Simple query to get all posts
    $stmt = $conn->prepare("SELECT p.post_id as post_id, p.date_posted as date_posted,p.description as description,p.title as title, p.image_url as image_url, p.location_name as location_name, p.status as status, p.type as type, u.id as user_id, u.full_name as full_name
                            from posts p join users u on p.user_id = u.id
                            where p.status = ? and  u.id != ?
                            order by p.date_posted desc");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt ->bind_param("si", $status, $user_id);

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
            'title' => $row['title']
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'posts' => $posts,
        'count' => count($posts)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching posts: ' . $e->getMessage()
    ]);
}
