<?php
session_start();
require_once __DIR__ . "/../config/config.php";
header('Content-Type: application/json');

$sql = "SELECT DISTINCT location_name FROM posts WHERE location_name IS NOT NULL AND location_name != '' ORDER BY location_name";
$result = $conn->query($sql);

$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row['location_name'];
}

echo json_encode(['status' => 'success', 'locations' => $locations]);