<?php
session_start();
require_once __DIR__ . '/../config/config.php';

function redirect_to_login(array $params) {
    header('Location: ../login.php?' . http_build_query($params));
    exit;
}

function redirect_to_posts() {
    header('Location: ../main.php');
    exit;
}

function redirect_to_admin() {
    header('Location: ../admin_dashboard.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    redirect_to_login([
        'error' => 'Please fill in all fields',
        'email' => $email,
    ]);
}

// Updated query to include status
$stmt = $conn->prepare('SELECT id, email, full_name, role, password, status FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Check user status first
    $status = $user['status'] ?? 'active'; // Default to active if status is null
    
    if ($status === 'banned') {
        redirect_to_login([
            'error' => 'Your account has been banned. Please contact administrator.',
            'email' => $email,
        ]);
    }
    
    if ($status === 'inactive') {
        redirect_to_login([
            'error' => 'Your account is inactive. Please contact administrator to activate your account.',
            'email' => $email,
        ]);
    }
    
    // Only proceed if user is active
    if ($status !== 'active') {
        redirect_to_login([
            'error' => 'Account access denied. Please contact administrator.',
            'email' => $email,
        ]);
    }
    
    // Verify password using secure password verification
    if (password_verify($password, $user['password'])) {
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_status'] = $user['status'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            redirect_to_admin();
        } else {
            redirect_to_posts();
        }
        
    } else {
        redirect_to_login([
            'error' => 'Invalid email or password',
            'email' => $email,
        ]);
    }
} else {
    redirect_to_login([
        'error' => 'Invalid email or password',
        'email' => $email,
    ]);
}

$stmt->close();
$conn->close();
?>