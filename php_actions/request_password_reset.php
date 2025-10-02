<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/email_helper.php';

function redirect_to_forgot($params) {
    header('Location: ../forgot_password.php?' . http_build_query($params));
    exit;
}

$email = trim($_POST['email'] ?? '');

// Validate email
if (empty($email)) {
    redirect_to_forgot([
        'error' => 'Please enter your email address',
        'email' => $email
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_to_forgot([
        'error' => 'Invalid email address',
        'email' => $email
    ]);
}

// Check if user exists
$stmt = $conn->prepare('SELECT id, full_name, email FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Don't reveal if email exists or not (security best practice)
    redirect_to_forgot([
        'success' => 'If that email is registered, you will receive a password reset link shortly.'
    ]);
}

$user = $result->fetch_assoc();
$stmt->close();

// Generate secure random token
$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

// Store token in database
$stmt = $conn->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
$stmt->bind_param('iss', $user['id'], $token, $expires_at);

if ($stmt->execute()) {
    $stmt->close();
    
    // Send email with reset link
    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/../reset_password.php?token=" . $token;
    
    $subject = "Password Reset Request - Lost & Found";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Password Reset Request</h1>
            </div>
            <div class='content'>
                <p>Hello " . htmlspecialchars($user['full_name']) . ",</p>
                <p>We received a request to reset your password for your Lost & Found account.</p>
                <p>Click the button below to reset your password:</p>
                <p style='text-align: center;'>
                    <a href='" . $reset_link . "' class='button'>Reset Password</a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>" . $reset_link . "</p>
                <p><strong>This link will expire in 1 hour.</strong></p>
                <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
                <p>Best regards,<br>Lost & Found Team</p>
            </div>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    if (sendEmail($email, $subject, $message)) {
        redirect_to_forgot([
            'success' => 'Password reset link has been sent to your email address. Please check your inbox.'
        ]);
    } else {
        redirect_to_forgot([
            'error' => 'Failed to send email. Please try again later or contact support.'
        ]);
    }
} else {
    $stmt->close();
    redirect_to_forgot([
        'error' => 'An error occurred. Please try again later.',
        'email' => $email
    ]);
}

$conn->close();
?>
