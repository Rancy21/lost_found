<?php
session_start();
require_once __DIR__ . "/../../config/config.php";
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


try{
    $stmt = $conn->prepare('SELECT email, full_name, role, status FROM users');

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    $users = [];

    while($row = $result->fetch_assoc()) {
        $users[] = [
            'email' => $row['email'],
            'full_name' => $row['full_name'],
            'role' => $row['role'],
            'status' => $row['status']
        ];
    }

    $stmt->close();

      $conn->close();
    
    echo json_encode([
        'status' => 'success',
        'users' => $users,
        'count' => count($users)
    ]);

}catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching posts: ' . $e->getMessage()
    ]);
}