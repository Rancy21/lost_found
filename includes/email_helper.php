<?php
/**
 * Email Helper Functions
 * 
 * This file contains functions for sending emails.
 * Email credentials are stored in config/email_config.php (not version controlled)
 */

// Load email configuration from secure config file
$email_config_path = __DIR__ . '/../config/email_config.php';

if (!file_exists($email_config_path)) {
    error_log('Email configuration file not found. Please copy config/email_config.example.php to config/email_config.php and configure your SMTP settings.');
    die('Email configuration missing. Please contact the administrator.');
}

require_once $email_config_path;

/**
 * Send email using PHP mail() function
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @return bool True on success, false on failure
 */
function sendEmailWithPHPMailer($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">" . "\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Send email using SMTP (requires PHPMailer library)
 * 
 * Install PHPMailer via Composer: composer require phpmailer/phpmailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @return bool True on success, false on failure
 */
function sendEmailWithSMTP($to, $subject, $message) {
    // Check if PHPMailer is installed
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        error_log('PHPMailer not found. Install it with: composer require phpmailer/phpmailer');
        return false;
    }
    
    require __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Main email sending function
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @return bool True on success, false on failure
 */
function sendEmail($to, $subject, $message) {
    if (SMTP_ENABLED) {
        return sendEmailWithSMTP($to, $subject, $message);
    } else {
        return sendEmailWithPHPMailer($to, $subject, $message);
    }
}

/**
 * Validate email address
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Send test email to verify configuration
 * 
 * @param string $to Test recipient email
 * @return bool True on success, false on failure
 */
function sendTestEmail($to) {
    $subject = "Test Email - Lost & Found";
    $message = "
    <html>
    <body>
        <h2>Email Configuration Test</h2>
        <p>If you're seeing this, your email configuration is working correctly!</p>
        <p>Sent at: " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $message);
}
