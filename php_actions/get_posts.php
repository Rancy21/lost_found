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

try {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? and status in ('active', 'pending', 'resolved', 'rejected') ORDER BY date_posted DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    
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
            'image_url' => $row['image_url']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'posts' => $posts,
        'count' => count($posts),
        'user_email' => $_SESSION['user_email']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching posts: ' . $e->getMessage()
    ]);
}
?>