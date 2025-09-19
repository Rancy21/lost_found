<?php
require_once __DIR__ . '/../config/config.php';

function redirect_to_signup(array $params): void {
    header('Location: ../signup.php?' . http_build_query($params));
    exit;
}

function redirect_to_login(array $params): void {
    header('Location: ../login.php?' . http_build_query($params));
    exit;
}

$name = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$pswd = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// Basic validation
if ($name === '' || $email === '' || $pswd === '' || $confirm === '') {
    redirect_to_signup([
        'error' => 'Please fill in all fields.',
        'fullname' => $name,
        'email' => $email,
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_to_signup([
        'error' => 'Invalid email address.',
        'fullname' => $name,
        'email' => $email,
    ]);
}

if ($pswd !== $confirm) {
    redirect_to_signup([
        'error' => 'Passwords do not match.',
        'fullname' => $name,
        'email' => $email,
    ]);
}

if (strlen($pswd) < 6) {
    redirect_to_signup([
        'error' => 'Password must be at least 6 characters long.',
        'fullname' => $name,
        'email' => $email,
    ]);
}

$passwordHash = password_hash($pswd, PASSWORD_DEFAULT);
$role = 'user';

// Insert user into DB
$stmt = $conn->prepare('INSERT INTO users (email, full_name, password, role) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    redirect_to_signup([
        'error' => 'Prepare failed: ' . $conn->error,
        'fullname' => $name,
        'email' => $email,
    ]);
}


$stmt->bind_param('ssss', $email, $name, $pswd, $role);

if ($stmt->execute()) {
    $stmt->close();
    redirect_to_login([
        'success' => 'Account created successfully. Please log in.',
        'email' => $email,
    ]);
} else {
    $err = $stmt->error;
    $stmt->close();
    redirect_to_signup([
        'error' => 'Signup failed: ' . $err,
        'fullname' => $name,
        'email' => $email,
    ]);
}

$conn -> close();
?>