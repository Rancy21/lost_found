<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $targetDir = "../uploads/";
    // make sure uploads dir exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $fileName;

    // basic validation
    $allowedTypes = ['jpg','jpeg','png','gif'];
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            echo json_encode([
                "status" => "success",
                "url" => "uploads/" . $fileName
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Upload failed"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid file type"]);
    }
}
?>
