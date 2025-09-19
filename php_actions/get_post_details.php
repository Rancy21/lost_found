<?php

session_start();
require_once __DIR__.'/../config/config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if (!isset($_GET['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No post ID provided']);
    exit;
}

$post_id = $_GET['post_id'];

$stmt = $conn->prepare("SELECT title, type, date_posted, description, location_name, image_url FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'title' => $post['title'],
        'type' => $post['type'],
        'date_posted' => $post['date_posted'],
        'description' => $post['description'],
        'location_name' => $post['location_name'],
        'image_url' => $post['image_url']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Post not found']);
}

$stmt->close();
$conn->close();
