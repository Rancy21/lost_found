<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to view your profile');
    exit;
}

$user_email = $_SESSION['user_email'] ?? '';
$user_name = $_SESSION['user_name'] ?? '';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #111827;
        }

        /* Top Nav */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo-section {
            text-decoration: none;
            color: #1e3c72;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }
        .logo-icon { font-size: 1.5rem; }
        .nav-actions { display: flex; gap: .5rem; }
        .action-btn {
            padding: 0.6rem 1rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        .btn-back {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .btn-logout {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        /* Main */
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background: rgba(255,255,255,0.98);
            border-radius: 16px;
            box-shadow: 0 10px 32px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .title { font-size: 1.4rem; font-weight: 700; color: #1f2937; }
        .subtitle { color: #6b7280; font-weight: 500; }
        .card-body { padding: 1.5rem; }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
        }

        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: 600; margin-bottom: .4rem; color: #374151; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: border-color .2s ease;
        }
        input:focus { outline: none; border-color: #667eea; }
        input[disabled] { background: #f9fafb; color: #6b7280; }

        .hint { color: #6b7280; font-size: .9rem; }

        .actions { display: flex; gap: .75rem; margin-top: .5rem; }
        .btn {
            padding: .8rem 1.2rem;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; }
        .btn-secondary { background: #e5e7eb; color: #111827; }

        .alert { border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1rem; font-weight: 600; display: flex; gap: .5rem; align-items: center; }
        .alert-success { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border-left: 4px solid #10b981; }
        .alert-error { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); color: #dc2626; border-left: 4px solid #ef4444; }

        .section-title { font-weight: 700; color: #1f2937; margin: .25rem 0 1rem; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-container">
            <a href="main.php" class="logo-section">
                <span class="logo-icon">🔍</span>
                Lost & Found
            </a>
            <div class="nav-actions">
                <a class="action-btn btn-back" href="main.php">← Back</a>
                <a class="action-btn btn-logout" href="php_actions/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="title">My Profile</div>
                    <div class="subtitle">Manage your account information</div>
                </div>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="php_actions/update_profile.php" novalidate>
                    <div class="grid">
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user_name); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" disabled />
                            <div class="hint">Email cannot be changed</div>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1rem 0;" />
                    <div class="section-title">Change Password</div>
                    <div class="grid">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Enter current password" />
                        </div>
                        <div></div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" />
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" />
                        </div>
                    </div>
                    <div class="hint">Leave password fields empty if you do not want to change it. To change password, you must provide your current password.</div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="main.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
