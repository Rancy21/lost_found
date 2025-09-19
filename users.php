<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to view users');
    exit;
}

$user_role = $_SESSION['user_role'];

function redirect_to_posts() {
    header('Location: main.php');
    exit;
}

if($user_role !== 'admin' ){
    redirect_to_posts();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style/users_posts.css" />
    <style>
        /* Additional styles for user management */
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-admin {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .role-user {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }

        .users-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table tr:hover {
            background: #f9fafb;
        }

        .email-cell {
            color: #3b82f6;
            font-weight: 500;
        }

        .name-cell {
            font-weight: 600;
            color: #1f2937;
        }

        .message-block {
            margin-bottom: 1.5rem;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: none;
            align-items: center;
            animation: slideDown 0.3s ease;
        }

        .message-block.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .message-block.error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 4px solid #ef4444;
            color: #dc2626;
        }

        .message-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
        }

        .message-icon {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .message-text {
            flex: 1;
            font-weight: 500;
        }

        .message-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .message-close:hover {
            opacity: 1;
        }

        .status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.status-banned {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626;
}

.ban-btn {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    padding: 0.4rem 0.8rem;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-right: 0.5rem;
}

.unban-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.4rem 0.8rem;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-right: 0.5rem;
}

.ban-btn:hover, .unban-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

        .change-role-btn {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    padding: 0.4rem 0.8rem;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.change-role-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

.main-content{
    max-width: 900px;
}
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="navbar">
        <div class="nav-container">
            <a href="main.php" class="logo-section">
                <span class="logo-icon">🔍</span>
                Lost & Found
            </a>
            
            <div class="quick-actions">
                <a href="post_moderation.php" class="action-btn btn-lost">
                    📋 Post Moderation
                </a>
                <a href="admin_dashboard.php" class="action-btn btn-found">
                    ← Back to Main
                </a>
            </div>
        </div>
    </div>


    <!-- Main Content -->
    <div class="main-content">
        <!-- Feed Header -->
        <div class="feed-header">
            <h1 class="feed-title">👥 User Management</h1>
            <p class="feed-subtitle">Manage all registered users in the system</p>
        </div>

        <!-- Message Block -->
        <div id="message-block" class="message-block">
            <div class="message-content">
                <span class="message-icon" id="message-icon"></span>
                <span class="message-text" id="message-text"></span>
                <button class="message-close" onclick="hideMessage()">&times;</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="filter-tabs">
            <div class="filter-tab active">
                <strong>Total Users: </strong><span id="total-users">-</span>
            </div>
            <div class="filter-tab">
                <strong>Admins: </strong><span id="admin-count">-</span>
            </div>
            <div class="filter-tab">
                <strong>Regular Users: </strong><span id="user-count">-</span>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading" class="loading" style="display: none;">
            <div class="loading-spinner"></div>
            <p>Loading users...</p>
        </div>

        <!-- Users Table -->
        <div id="users-container" style="display: none;">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Status</th>
<th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="empty-state" style="display: none;">
            <div class="empty-icon">👥</div>
            <h3 class="empty-title">No Users Found</h3>
            <p class="empty-description">There are no registered users in the system.</p>
        </div>
    </div>

    <script>
        let allUsers = [];

        // Load users when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });

        async function loadUsers() {
            showLoading();
            hideMessage();

            try {
                const response = await fetch('php_actions/admin_actions/get_users.php', {
                    method: 'GET'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.status === 'success') {
                    allUsers = data.users;
                    displayUsers();
                    updateStats();
                } else {
                    showMessage('Error loading users: ' + data.message, true);
                }
            } catch (error) {
                showMessage('Network error: ' + error.message, true);
            } finally {
                hideLoading();
            }
        }

        function displayUsers() {
            const tbody = document.getElementById('users-tbody');
            const container = document.getElementById('users-container');
            const emptyState = document.getElementById('empty-state');

            if (allUsers.length === 0) {
                container.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            container.style.display = 'block';
            emptyState.style.display = 'none';

            tbody.innerHTML = allUsers.map(user => `
    <tr>
        <td class="name-cell">${user.full_name || 'N/A'}</td>
        <td class="email-cell">${user.email}</td>
        <td>
            <span class="role-badge role-${user.role}">
                ${user.role === 'admin' ? '👑 Admin' : '👤 User'}
            </span>
        </td>
        <td>
            <span class="status-badge status-${user.status || 'active'}">
                ${user.status === 'banned' ? '🚫 Banned' : '✅ Active'}
            </span>
        </td>
        <td>
            <button class="change-role-btn" onclick="changeUserRole('${user.email}', '${user.role}')">
                🔄 Change Role
            </button>
            ${user.status === 'banned' ? 
                `<button class="unban-btn" onclick="unbanUser('${user.email}')">🔓 Unban</button>` :
                `<button class="ban-btn" onclick="banUser('${user.email}')">🚫 Ban</button>`
            }
        </td>
    </tr>
`).join('');
        }

        function updateStats() {
    const totalUsers = allUsers.length;
    const adminCount = allUsers.filter(user => user.role === 'admin').length;
    const userCount = allUsers.filter(user => user.role === 'user').length;
    const bannedCount = allUsers.filter(user => user.status === 'banned').length;

    document.getElementById('total-users').textContent = totalUsers;
    document.getElementById('admin-count').textContent = adminCount;
    document.getElementById('user-count').textContent = userCount;
    
    // Add banned count to stats if you want
}

        function showLoading() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('users-container').style.display = 'none';
            document.getElementById('empty-state').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        function showMessage(message, isError = false) {
            const messageBlock = document.getElementById('message-block');
            const messageIcon = document.getElementById('message-icon');
            const messageText = document.getElementById('message-text');
            
            messageText.textContent = message;
            
            if (isError) {
                messageBlock.className = 'message-block error';
                messageIcon.textContent = '❌';
            } else {
                messageBlock.className = 'message-block success';
                messageIcon.textContent = '✅';
            }
            
            messageBlock.style.display = 'flex';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                hideMessage();
            }, 5000);
        }

        function hideMessage() {
            document.getElementById('message-block').style.display = 'none';
        }

        async function changeUserRole(email, currentRole) {
    const newRole = currentRole === 'admin' ? 'user' : 'admin';
    const action = newRole === 'admin' ? 'promote to Admin' : 'demote to User';
    
    if (!confirm(`Are you sure you want to ${action} for ${email}?`)) {
        return;
    }

    try {
        const response = await fetch('php_actions/admin_actions/modify_role.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_email=${encodeURIComponent(email)}&user_role=${newRole}`
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            showMessage(data.message, false);
            loadUsers(); // Refresh the user list
        } else {
            showMessage('Error changing role: ' + data.message, true);
        }
    } catch (error) {
        showMessage('Network error: ' + error.message, true);
    }
}

    async function banUser(email) {
    if (!confirm(`Are you sure you want to ban ${email}? They will not be able to log in.`)) {
        return;
    }

    try {
        const response = await fetch('php_actions/admin_actions/ban_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_email=${encodeURIComponent(email)}`
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            showMessage(data.message, false);
            loadUsers(); // Refresh the user list
        } else {
            showMessage('Error banning user: ' + data.message, true);
        }
    } catch (error) {
        showMessage('Network error: ' + error.message, true);
    }
}

async function unbanUser(email) {
    if (!confirm(`Are you sure you want to unban ${email}?`)) {
        return;
    }

    try {
        const response = await fetch('php_actions/admin_actions/unban_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_email=${encodeURIComponent(email)}`
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            showMessage(data.message, false);
            loadUsers(); // Refresh the user list
        } else {
            showMessage('Error unbanning user: ' + data.message, true);
        }
    } catch (error) {
        showMessage('Network error: ' + error.message, true);
    }
}
    </script>
</body>
</html>