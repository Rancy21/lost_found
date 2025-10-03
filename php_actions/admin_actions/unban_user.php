<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../includes/email_helper.php";
header('Content-Type: application/json; charset=UTF-8');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if(empty($_POST['user_email']) ) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User email is required']);
    exit;
}

$email = $_POST['user_email'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit;
}


$status = 'active';
try {
    // Get user's full name for personalized email
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }
    
    $fullName = $user['full_name'] ?? 'User';
    
    // Update user status
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE email = ?");
    if(!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('ss', $status, $email);
    if(!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found or already active']);
        exit;
    }
    
    $stmt->close();
    
    // Send unban notification email
    $subject = "Account Unbanned - Lost & Found";
    $emailMessage = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .info-box { background: white; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; color: #6b7280; font-size: 0.9em; margin-top: 20px; }
            .btn { display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: 600; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔓 Account Unbanned</h1>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($fullName) . ",</p>
                
                <div class='info-box'>
                    <h3 style='margin-top: 0; color: #059669;'>Good News!</h3>
                    <p>Your Lost & Found account has been unbanned by an administrator.</p>
                </div>
                
                <p>You can now log in and access all features of the Lost & Found platform again.</p>
                
                <div style='text-align: center;'>
                    <a href='" . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] ."lost_found/login.php' class='btn'>Log In Now</a>
                </div>
                
                <p>Please make sure to follow the community guidelines to maintain your account in good standing.</p>
                
                <div class='footer'>
                    <p>This is an automated message from Lost & Found</p>
                    <p>Date: " . date('Y-m-d H:i:s') . "</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($email, $subject, $emailMessage);

    $message = 'User successfully unbanned and notification email sent';
    http_response_code(200);
    echo json_encode([
        'status' => 'success', 
        'message' => $message
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error while unbanning user: ' . $e->getMessage()
    ]);
}

$conn->close();