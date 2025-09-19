<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to access admin dashboard');
    exit;
}

$user_role = $_SESSION['user_role'];

if ($user_role !== 'admin') {
    header('Location: main.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            font-size: 2rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3c72;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f8fafc;
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .user-details h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
        }

        .user-details p {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .logout-btn {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        .logout-btn:hover {
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Quick Actions */
        .quick-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .actions-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
        }
        .action-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .action-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .action-description {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        /* Loading */
        .loading {
            text-align: center;
            padding: 3rem;
            color: white;
            font-size: 1.1rem;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Message */
        .message {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: none;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                padding: 0 1rem;
            }

            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo">🔍</div>
                <h1 class="header-title">Admin Dashboard</h1>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user_name); ?></h3>
                        <p><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                </div>
                <div class="nav-buttons">
                    <!-- <a href="main.php" class="nav-btn">
                        🏠 Main Site
                    </a> -->
                    <a href="php_actions/logout.php" class="nav-btn logout-btn">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Admin Dashboard</h1>
            <p class="page-subtitle">Monitor and manage your Lost & Found platform</p>
        </div>

        <!-- Error Message -->
        <div id="message" class="message"></div>

        <!-- Loading -->
        <div id="loading" class="loading" style="display: none;">
            <div class="loading-spinner"></div>
            Loading dashboard data...
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid" id="stats-grid" style="display: none;">
            <div class="stat-card">
                <span class="stat-icon">👥</span>
                <span class="stat-number" id="total-users">-</span>
                <span class="stat-label">Total Users</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">📝</span>
                <span class="stat-number" id="total-posts">-</span>
                <span class="stat-label">Total Posts</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">✅</span>
                <span class="stat-number" id="resolved-posts">-</span>
                <span class="stat-label">Resolved Posts</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">⏳</span>
                <span class="stat-number" id="pending-posts">-</span>
                <span class="stat-label">Pending Review</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">🔍</span>
                <span class="stat-number" id="lost-posts">-</span>
                <span class="stat-label">Lost Items</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">🎯</span>
                <span class="stat-number" id="found-posts">-</span>
                <span class="stat-label">Found Items</span>
            </div>
        </div>

        <!-- Quick Actions -->
<div class="quick-actions" id="quick-actions" style="display: none;">
    <h2 class="actions-title">Quick Actions</h2>
    <div class="actions-grid">
        <div class="action-card">
            <span class="action-icon">📋</span>
            <h3 class="action-title">Post Moderation</h3>
            <p class="action-description">Review and moderate pending posts from users</p>
            <a href="post_moderation.php" class="action-btn">
                🔍 Review Posts
            </a>
        </div>
        
        <div class="action-card">
            <span class="action-icon">📊</span>
            <h3 class="action-title">Post Management</h3>
            <p class="action-description">Manage active and rejected posts with advanced filtering</p>
            <a href="post_management.php" class="action-btn">
                ⚙️ Manage Posts
            </a>
        </div>
        
        <div class="action-card">
            <span class="action-icon">👥</span>
            <h3 class="action-title">User Management</h3>
            <p class="action-description">Manage users, roles, and account status</p>
            <a href="users.php" class="action-btn">
                👤 Manage Users
            </a>
        </div>
    </div>
</div>
    </div>

    <script>
        async function loadDashboardData() {
            const loading = document.getElementById('loading');
            const statsGrid = document.getElementById('stats-grid');
            const quickActions = document.getElementById('quick-actions');
            const message = document.getElementById('message');

            loading.style.display = 'block';
            statsGrid.style.display = 'none';
            quickActions.style.display = 'none';
            message.style.display = 'none';

            try {
                // Load users data
                const usersResponse = await fetch('php_actions/admin_actions/get_users.php');
                const usersData = await usersResponse.json();

                // Load posts data
                const postsResponse = await fetch('php_actions/admin_actions/get_all_posts.php');
                const postsData = await postsResponse.json();

                if (usersData.status === 'success' && postsData.status === 'success') {
                    // Calculate metrics
                    const totalUsers = usersData.users.length;
                    const totalPosts = postsData.posts.length;
                    const resolvedPosts = postsData.posts.filter(post => post.status === 'resolved').length;
                    const pendingPosts = postsData.posts.filter(post => post.status === 'pending').length;
                    const lostPosts = postsData.posts.filter(post => post.type === 'lost').length;
                    const foundPosts = postsData.posts.filter(post => post.type === 'found').length;

                    // Update UI
                    document.getElementById('total-users').textContent = totalUsers;
                    document.getElementById('total-posts').textContent = totalPosts;
                    document.getElementById('resolved-posts').textContent = resolvedPosts;
                    document.getElementById('pending-posts').textContent = pendingPosts;
                    document.getElementById('lost-posts').textContent = lostPosts;
                    document.getElementById('found-posts').textContent = foundPosts;

                    // Show content
                    loading.style.display = 'none';
                    statsGrid.style.display = 'grid';
                    quickActions.style.display = 'block';
                } else {
                    throw new Error('Failed to load dashboard data');
                }
            } catch (error) {
                loading.style.display = 'none';
                message.textContent = 'Error loading dashboard: ' + error.message;
                message.style.display = 'block';
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>
</body>
</html>