<?php
session_start();
require_once __DIR__ . '/../config/config.php';

function redirect_with(array $params): void {
    header('Location: ../profile.php?' . http_build_query($params));
    exit;
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    redirect_with(['error' => 'You must be logged in.']);
}

$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['user_email'] ?? '';

if (!$user_id) {
    redirect_with(['error' => 'Invalid session.']);
}

$fullname = trim($_POST['fullname'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($fullname === '') {
    redirect_with(['error' => 'Full name is required.']);
}

// If changing password, validate inputs
$changing_password = ($current_password !== '' || $new_password !== '' || $confirm_password !== '');
if ($changing_password) {
    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        redirect_with(['error' => 'To change password, fill all password fields.']);
    }
    if (strlen($new_password) < 6) {
        redirect_with(['error' => 'New password must be at least 6 characters.']);
    }
    if ($new_password !== $confirm_password) {
        redirect_with(['error' => 'New passwords do not match.']);
    }
}

// Fetch current user data (including password hash)
$stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
if (!$stmt) {
    redirect_with(['error' => 'Prepare failed: ' . $conn->error]);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    redirect_with(['error' => 'User not found.']);
}
$row = $result->fetch_assoc();
$stmt->close();

// If changing password, verify current password
if ($changing_password) {
    if (!password_verify($current_password, $row['password'])) {
        redirect_with(['error' => 'Current password is incorrect.']);
    }
}

// Build update query dynamically
if ($changing_password) {
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET full_name = ?, password = ? WHERE id = ?');
    if (!$stmt) {
        redirect_with(['error' => 'Prepare failed: ' . $conn->error]);
    }
    $stmt->bind_param('ssi', $fullname, $new_hash, $user_id);
} else {
    $stmt = $conn->prepare('UPDATE users SET full_name = ? WHERE id = ?');
    if (!$stmt) {
        redirect_with(['error' => 'Prepare failed: ' . $conn->error]);
    }
    $stmt->bind_param('si', $fullname, $user_id);
}

if ($stmt->execute()) {
    $stmt->close();
    // Update session name to reflect changes
    $_SESSION['user_name'] = $fullname;
    redirect_with(['success' => 'Profile updated successfully.']);
} else {
    $err = $stmt->error;
    $stmt->close();
    redirect_with(['error' => 'Update failed: ' . $err]);
}

$conn->close();
