<?php

require_once __DIR__ . "/../config/config.php";
header('Content-Type: application/json; charset=UTF-8');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if(!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Post ID is required']);
    exit;
}

$post_id = intval($_POST['post_id']);
if($post_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid post ID']);
    exit;
}

try {
    $stmt = $conn->prepare('SELECT image_url FROM posts WHERE post_id = ?');
    if(!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $post_id);
    if(!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    
    if(!$post) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        exit;
    }
    
    $image_url = $post['image_url'];
    $stmt->close();
    
    $stmt = $conn->prepare('DELETE FROM posts WHERE post_id = ?');
    if(!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $post_id);
    if(!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Post not found or already deleted']);
        exit;
    }
    
    $stmt->close();
    
    $image_deleted = false;
    if(!empty($image_url)) {
        $image_path = __DIR__ . '/../' . $image_url;
        
        if(file_exists($image_path)) {
            if(unlink($image_path)) {
                $image_deleted = true;
            }
        }
    }
    
    $message = 'Post deleted successfully';
    if($image_deleted) {
        $message .= ' and associated image removed';
    } elseif(!empty($image_url)) {
        $message .= ' (image file not found or could not be removed)';
    }
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success', 
        'message' => $message,
        'image_deleted' => $image_deleted
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error deleting post: ' . $e->getMessage()
    ]);
}

$conn->close();
?>