<?php

session_start();
if (!isset($_SESSION["logged_in"]) || $_SESSION["user_role"] !== "admin") {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

require_once __DIR__ . "/../../config/config.php";
header("Content-Type: application/json; charset=UTF-8");

$type = $_GET["type"] ?? "all";
$status = $_GET["status"] ?? "all";
$search = $_GET["search"] ?? "";

try {
    $whereConditions = ["p.status IN ('active', 'rejected')"];
    $params = [];
    $types = "";

    // Add type filter
    if ($type === "lost") {
        $whereConditions[] = "p.type = ?";
        $params[] = "lost";
        $types .= "s";
    } elseif ($type === "found") {
        $whereConditions[] = "p.type = ?";
        $params[] = "found";
        $types .= "s";
    }

    // Add status filter
    if ($status === "active") {
        $whereConditions[] = "p.status = ?";
        $params[] = "active";
        $types .= "s";
    } elseif ($status === "rejected") {
        $whereConditions[] = "p.status = ?";
        $params[] = "rejected";
        $types .= "s";
    }

    // Add search filter
    if (!empty($search)) {
        $whereConditions[] = "(p.description LIKE ? OR p.location_name LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }

    $whereClause = "WHERE " . implode(" AND ", $whereConditions);

    $sql = "SELECT p.post_id as post_id, p.date_posted as date_posted, p.description as description, p.image_url as image_url, p.location_name as location_name, p.status as status, p.type as type, u.id as user_id, u.full_name as full_name
            FROM posts p 
            JOIN users u ON p.user_id = u.id
            $whereClause 
            ORDER BY p.date_posted DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $posts = [];

    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            "id" => $row["post_id"],
            "description" => $row["description"],
            "type" => $row["type"],
            "location_name" => $row["location_name"],
            "date_posted" => $row["date_posted"],
            "status" => $row["status"],
            "image_url" => $row["image_url"],
            "user_id" => $row["user_id"],
            "user_name" => $row["full_name"],
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        "status" => "success",
        "posts" => $posts,
        "count" => count($posts),
        "filters" => [
            "type" => $type,
            "status" => $status,
            "search" => $search,
        ],
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error filtering posts: " . $e->getMessage(),
    ]);
}
