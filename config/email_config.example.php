<?php
/**
 * Email Configuration Template
 * 
 * IMPORTANT: Copy this file to 'email_config.php' and update with your credentials.
 * DO NOT commit email_config.php to version control!
 * 
 * Instructions:
 * 1. Copy this file: cp email_config.example.php email_config.php
 * 2. Edit email_config.php with your SMTP credentials
 * 3. The .gitignore file will keep your credentials safe
 */

// SMTP Configuration
define('SMTP_ENABLED', true); // Set to true to use SMTP, false to use PHP mail()
define('SMTP_HOST', 'smtp.gmail.com'); // Your SMTP host (e.g., smtp.gmail.com, smtp.sendgrid.net)
define('SMTP_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_USERNAME', 'your_email@gmail.com'); // Your email address
define('SMTP_PASSWORD', 'your_app_password_here'); // Your email password or app password
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// Email Sender Information
define('FROM_EMAIL', 'noreply@lostandfound.com'); // From email address
define('FROM_NAME', 'Lost & Found'); // From name

/**
 * For Gmail users:
 * - Enable 2-Step Verification
 * - Generate an App Password: https://myaccount.google.com/apppasswords
 * - Use the App Password in SMTP_PASSWORD
 * 
 * For other SMTP providers:
 * - Check your provider's documentation for SMTP settings
 * - Common providers: SendGrid, Mailgun, Amazon SES, etc.
 */
?>
