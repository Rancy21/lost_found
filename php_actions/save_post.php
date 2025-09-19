<?php

// File: /home/larryck/Web Projects/lost_found/php_actions/save_post.php

session_start();
require_once __DIR__ . "/../config/config.php";
header('Content-Type: application/json; charset=UTF-8');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or content type']);
    exit;
}

$rawJson = file_get_contents('php://input');
$data = json_decode($rawJson, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'JSON decode error: ' . json_last_error_msg()]);
    exit;
}

// Get data from request
$title = $data['title'] ?? null;
$type = $data['type'] ?? null;
$description = $data['description'] ?? null;
$imageUrl = $data['image'] ?? null;
$lat = $data['lat'] ?? null;
$lng = $data['lng'] ?? null;
$location = $data['location'] ?? null;

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Validate required fields
if (empty($title) || empty($type) || empty($description) || empty($imageUrl) || empty($location)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Validate post type
if (!in_array($type, ['lost', 'found'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid post type']);
    exit;
}

try {
    $stmt = $conn->prepare('INSERT INTO posts(user_id, title, type, description, image_url, latitude, longitude, location_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("issssdds", $user_id, $title, $type, $description, $imageUrl, $lat, $lng, $location);

    if ($stmt->execute()) {
        $post_id = $conn->insert_id;
        $stmt->close();
        $conn->close();

        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Post created successfully!",
            "post_id" => $post_id,
            "user_email" => $_SESSION['user_email']
        ]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Post creation failed: " . $e->getMessage()
    ]);
}

$conn->close();
