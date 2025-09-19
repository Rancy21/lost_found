<?php

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

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


$status = 'pending';


try {
    $stmt = $conn -> prepare('UPDATE posts set status = ? where post_id = ?');
    if(!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt -> bind_param('si', $status, $post_id);
    if(!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Post not found or already deleted']);
        exit;
    }
    
    $stmt->close();

$message = 'Post successfully set to pending';
    http_response_code(200);
    echo json_encode([
        'status' => 'success', 
        'message' => $message
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error while setting post to pending: ' . $e->getMessage()
    ]);
}

$conn -> close();
?>