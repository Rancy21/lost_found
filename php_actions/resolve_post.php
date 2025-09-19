<?php

require_once __DIR__ . "/../config/config.php";
header('Content-Type: application/json; charset=UTF-8');

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
$post_id = $_POST['post_id'];

$status = 'resolved';
$stmt = $conn -> prepare('UPDATE posts set status = ? where post_id = ?');
$stmt -> bind_param('si', $status, $post_id);
if($stmt -> execute()) {
    $stmt -> close();
    echo json_encode(array('status'=> 'success','message'=> 'Post updated successfully'));
}else{
    echo json_encode(array('status'=> 'error','message'=> 'Error updating post:' . $stmt -> error));
    $stmt -> close();
}
$conn -> close();
}else{
        http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

?>