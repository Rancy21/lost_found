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

if(empty($_POST['user_email']) || empty($_POST['user_role'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User email and role are required']);
    exit;
}

$email = $_POST['user_email'];
$role = $_POST['user_role'];


if (!in_array($role, ['admin', 'user'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit;
}


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
    
    // Update user role
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE email = ?");
    if(!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('ss', $role, $email);
    if(!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found or role unchanged']);
        exit;
    }
    
    $stmt->close();
    
    // Send role change notification email
    $roleDisplay = ucfirst($role);
    $subject = "Account Role Changed - Lost & Found";
    
    if ($role === 'admin') {
        $emailMessage = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; color: #6b7280; font-size: 0.9em; margin-top: 20px; }
                .badge { display: inline-block; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; padding: 8px 16px; border-radius: 20px; font-weight: 700; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>👑 Role Promoted to Admin</h1>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($fullName) . ",</p>
                    
                    <div class='info-box'>
                        <h3 style='margin-top: 0; color: #f59e0b;'>Congratulations!</h3>
                        <p>Your Lost & Found account has been promoted to <span class='badge'>👑 ADMIN</span></p>
                    </div>
                    
                    <p><strong>As an admin, you now have access to:</strong></p>
                    <ul>
                        <li>User Management - Ban/Unban users and modify roles</li>
                        <li>Post Moderation - Review and manage all posts</li>
                        <li>Admin Dashboard - Access to administrative tools</li>
                        <li>Extended Permissions - Full platform control</li>
                    </ul>
                    
                    <p>Please use these privileges responsibly to maintain a safe and helpful community.</p>
                    
                    <div class='footer'>
                        <p>This is an automated message from Lost & Found</p>
                        <p>Date: " . date('Y-m-d H:i:s') . "</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    } else {
        $emailMessage = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; color: #6b7280; font-size: 0.9em; margin-top: 20px; }
                .badge { display: inline-block; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; padding: 8px 16px; border-radius: 20px; font-weight: 700; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔄 Role Changed to User</h1>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($fullName) . ",</p>
                    
                    <div class='info-box'>
                        <h3 style='margin-top: 0; color: #3b82f6;'>Role Update</h3>
                        <p>Your Lost & Found account role has been changed to <span class='badge'>👤 USER</span></p>
                    </div>
                    
                    <p>Your account now has standard user permissions. You can:</p>
                    <ul>
                        <li>Post lost and found items</li>
                        <li>Search and browse posts</li>
                        <li>Manage your own posts</li>
                        <li>Communicate with other users</li>
                    </ul>
                    
                    <p>If you have any questions about this change, please contact an administrator.</p>
                    
                    <div class='footer'>
                        <p>This is an automated message from Lost & Found</p>
                        <p>Date: " . date('Y-m-d H:i:s') . "</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    sendEmail($email, $subject, $emailMessage);

    $message = 'User role modified successfully and notification email sent';
    http_response_code(200);
    echo json_encode([
        'status' => 'success', 
        'message' => $message
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error while modifying user role: ' . $e->getMessage()
    ]);
}

$conn->close();