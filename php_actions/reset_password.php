<?php
require_once __DIR__ . '/../config/config.php';

function redirect_to_reset($token, $params) {
    header('Location: ../reset_password.php?token=' . $token . '&' . http_build_query($params));
    exit;
}

function redirect_to_login($params) {
    header('Location: ../login.php?' . http_build_query($params));
    exit;
}

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
if (empty($token)) {
    redirect_to_login(['error' => 'Invalid or missing reset token']);
}

if (empty($password) || empty($confirm_password)) {
    redirect_to_reset($token, ['error' => 'Please fill in all fields']);
}

if (strlen($password) < 6) {
    redirect_to_reset($token, ['error' => 'Password must be at least 6 characters long']);
}

if ($password !== $confirm_password) {
    redirect_to_reset($token, ['error' => 'Passwords do not match']);
}

// Verify token
$stmt = $conn->prepare('
    SELECT pr.user_id, pr.expires_at, u.email 
    FROM password_resets pr 
    JOIN users u ON pr.user_id = u.id 
    WHERE pr.token = ? AND pr.used = FALSE
');
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    redirect_to_login(['error' => 'Invalid or expired reset token']);
}

$reset = $result->fetch_assoc();
$stmt->close();

// Check if token has expired
$current_time = date('Y-m-d H:i:s');
if ($current_time > $reset['expires_at']) {
    redirect_to_login(['error' => 'Reset token has expired. Please request a new one.']);
}

// Hash the new password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update user password
$stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->bind_param('si', $hashed_password, $reset['user_id']);

if ($stmt->execute()) {
    $stmt->close();
    
    // Mark token as used
    $stmt = $conn->prepare('UPDATE password_resets SET used = TRUE WHERE token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();
    
    // Send confirmation email
    require_once __DIR__ . '/../includes/email_helper.php';
    
    $subject = "Password Changed Successfully - Lost & Found";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Password Changed</h1>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>Your password has been successfully changed for your Lost & Found account.</p>
                <p>If you did not make this change, please contact us immediately.</p>
                <p>Best regards,<br>Lost & Found Team</p>
            </div>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($reset['email'], $subject, $message);
    
    redirect_to_login([
        'success' => 'Your password has been successfully reset. You can now log in with your new password.'
    ]);
} else {
    $stmt->close();
    redirect_to_reset($token, [
        'error' => 'Failed to reset password. Please try again.'
    ]);
}

$conn->close();
?>
