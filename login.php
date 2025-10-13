<?php
$error_message = $_GET['error'];
$success_message = $_GET['success'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Lost & Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Sign in to help reunite lost items with their owners</p>
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
        
        <form method="POST" action="php_actions/login_user.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <div style="text-align: right; margin-top: 0.5rem;">
                    <a href="forgot_password.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn">Sign In</button>
        </form>
        
        <div class="switch-form">
            <p>New to Lost & Found? <a href="signup.php">Create Account</a></p>
        </div>
    </div>
    
    <footer style="margin-top: 2rem; padding: 1.5rem 0; text-align: center; width: 100%;">
        <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.85rem; margin: 0;">© <?php echo date('Y'); ?> Lost & Found. All rights reserved.</p>
    </footer>
</body>
</html>