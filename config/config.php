<?php

// Database credentials
$servername = "127.0.0.1";
$username = "root";
$password = "L@rr21003";
$dbname = "lost_found";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset for proper encoding
$conn->set_charset('utf8mb4');
