<?php
$error_message = $_GET['error'] ?? '';
$success_message = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Lost & Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Forgot Password</h1>
            <p>Enter your email address and we'll send you a link to reset your password</p>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="php_actions/request_password_reset.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>" placeholder="Enter your registered email" required>
            </div>
            
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        
        <div class="switch-form">
            <p>Remember your password? <a href="login.php">Sign In</a></p>
        </div>
    </div>
</body>
</html>
