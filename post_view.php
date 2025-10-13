<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to view your posts');
    exit;
}

$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts - Lost & Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/users_posts.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        /* Header Section */
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
            text-decoration: none;
        }

        .logo {
            font-size: 2rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3c72;
            margin: 0;
        }

        .page-info {
            text-align: center;
            flex: 1;
        }

        .page-info .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 0.25rem;
        }

        .page-info .page-subtitle {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-profile {
            position: relative;
        }

        .profile-btn {
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
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .profile-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            padding: 0.5rem 0;
            min-width: 200px;
            z-index: 1000;
            display: none;
            margin-top: 0.5rem;
        }

        .dropdown-item {
            display: block;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            color: #374151;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: #f8fafc;
            color: #1e3c72;
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

        .page-header .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-header .page-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }

        /* ... rest of your existing styles remain the same ... */

        /* Action Bar */
        .action-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .stats {
            display: flex;
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3c72;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .create-post-btn {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .create-post-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.3);
        }

        /* Messages */
        .message {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: none;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .success-message {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #16a34a;
            border-left: 4px solid #16a34a;
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

        /* Posts Grid */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .post-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #e2e8f0;
        }

        .post-content {
            padding: 1.5rem;
        }

        .post-type {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .post-type.lost {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .post-type.found {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .post-description {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .post-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .meta-icon {
            font-size: 1rem;
        }

        /* Update existing status classes and add new ones */
.post-status {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
    margin-bottom: 1rem;
}

.status-active {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1d4ed8;
}

.status-resolved {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.status-pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.status-rejected {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626;
}

.status-approved {
    background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
    color: #7c3aed;
}

        .post-actions {
            display: flex;
            gap: 0.75rem;
        }

        .action-btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .delete-btn {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            color: #dc2626;
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            transform: translateY(-2px);
        }

        .resolve-btn {
            background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
            color: #16a34a;
        }

        .resolve-btn:hover {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                padding: 0 1rem;
            }

            .container {
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .action-bar {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }

            .posts-grid {
                grid-template-columns: 1fr;
            }

            .post-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <!-- Logo -->
            <a href="main.php" class="logo-section">
                <span class="logo">🔍</span>
                <h1 class="header-title">Lost & Found</h1>
            </a>

            <!-- Page Title -->
            <div class="page-info">
                <h2 class="page-title">My Posts</h2>
                <p class="page-subtitle">Manage your lost and found items</p>
            </div>

            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Notification Bell will be inserted here by JavaScript -->
                
                <!-- User Profile -->
                <div class="user-profile">
                    <button class="profile-btn" id="profileBtn">
                        <span id="userInitials"><?php echo strtoupper(substr($user_name, 0, 1)); ?></span>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="main.php" class="dropdown-item">🏠 Home</a>
                        <a href="notifications.php" class="dropdown-item">🔔 Notifications</a>
                        <a href="messages.php" class="dropdown-item">💬 Messages</a>
                        <a href="#" class="dropdown-item">⚙️ Settings</a>
                        <a href="php_actions/logout.php" class="dropdown-item">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Your Posts</h1>
            <p class="page-subtitle">Manage your lost and found items</p>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-number" id="total-posts">0</span>
                    <span class="stat-label">Total Posts</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="active-posts">0</span>
                    <span class="stat-label">Active</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="pending-posts">0</span>
                    <span class="stat-label">Pending</span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-number" id="rejected-posts">0</span>
                    <span class="stat-label">Rejected</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="resolved-posts">0</span>
                    <span class="stat-label">Resolved</span>
                </div>
            </div>
            <a href="post.html" class="create-post-btn">
                ➕ Create New Post
            </a>
        </div>

        <!-- Messages -->
        <div id="message" class="message"></div>

        <!-- Loading -->
        <div id="loading" class="loading" style="display: none">
            <div class="loading-spinner"></div>
            Loading your posts...
        </div>

        <!-- Posts Container -->
        <div id="posts-container" class="posts-grid"></div>
    </div>

    <!-- Include notification JavaScript -->
    <script src="js/notifications.js"></script>
    <script>
        const message = document.getElementById("message");
        const loading = document.getElementById("loading");
        const postsContainer = document.getElementById("posts-container");
        const profileBtn = document.getElementById("profileBtn");
        const dropdownMenu = document.getElementById("dropdownMenu");

        // Toggle dropdown menu
        profileBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function(e) {
            if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.style.display = "none";
            }
        });

        function showMessage(text, isError = false) {
            message.className = `message ${isError ? "error-message" : "success-message"}`;
            message.textContent = text;
            message.style.display = "block";
            
            setTimeout(() => {
                message.style.display = "none";
            }, 5000);
        }

        function updateStats(posts) {
    const totalPosts = posts.length;
    const activePosts = posts.filter(post => post.status === 'active').length;
    const resolvedPosts = posts.filter(post => post.status === 'resolved').length;
    const pendingPosts = posts.filter(post => post.status === 'pending').length;
    const rejectedPosts = posts.filter(post => post.status === 'rejected').length;

    document.getElementById('total-posts').textContent = totalPosts;
    document.getElementById('active-posts').textContent = activePosts;
    document.getElementById('resolved-posts').textContent = resolvedPosts;
    document.getElementById('rejected-posts').textContent = rejectedPosts;
    document.getElementById('pending-posts').textContent = pendingPosts;
    
    // You can add more stat items in the HTML if needed for pending/rejected
}

        function createPostHTML(post) {
    const statusClass = `status-${post.status}`;
    const typeClass = post.type.toLowerCase();
    
    // Determine if resolve button should be shown/enabled
    const canResolve = post.status === 'active' || post.status === 'approved';
    const resolveButtonText = post.status === 'resolved' ? 'Resolved' : 'Mark Resolved';
    
    return `
        <div class="post-card">
            <img src="${escapeHtml(post.image_url)}" alt="Post image" class="post-image" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDMwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xNTAgMTAwQzE1MCA4Ny4yNDQ5IDE0MC43NTUgNzYuNjY2NyAxMjguODg5IDc2LjY2NjdDMTE3LjAyMiA3Ni42NjY3IDEwNy43NzggODcuMjQ0OSAxMDcuNzc4IDEwMEMxMDcuNzc4IDExMi43NTUgMTE3LjAyMiAxMjMuMzMzIDEyOC44ODkgMTIzLjMzM0MxNDAuNzU1IDEyMy4zMzMgMTUwIDExMi43NTUgMTUwIDEwMFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHA+CiAgPHRleHQgeD0iMTUwIiB5PSIxNDAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzlDQTNBRiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+Tm8gSW1hZ2U8L3RleHQ+CjwvcD4KPC9zdmc+">
            
            <div class="post-content">
                <div class="post-type ${typeClass}">${escapeHtml(post.type)}</div>
                
                <div class="post-description">${escapeHtml(post.description)}</div>
                
                <div class="post-meta">
                    <div class="meta-item">
                        <span class="meta-icon">📍</span>
                        <span>${escapeHtml(post.location_name)}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">📅</span>
                        <span>${formatDate(post.date_posted)}</span>
                    </div>
                </div>
                
                <div class="post-status ${statusClass}">${escapeHtml(post.status)}</div>
                
                <div class="post-actions">
                    <button class="action-btn resolve-btn" onclick="resolvePost(${post.id})" ${!canResolve ? 'disabled' : ''}>
                        ✅ ${resolveButtonText}
                    </button>
                    <button class="action-btn delete-btn" onclick="deletePost(${post.id})">
                        🗑️ Delete
                    </button>
                </div>
            </div>
        </div>
    `;
}

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        function escapeHtml(text) {
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }

        async function loadPosts() {
            loading.style.display = "block";
            postsContainer.innerHTML = "";

            try {
                const response = await fetch("php_actions/get_posts.php");
                const data = await response.json();

                loading.style.display = "none";

                if (data.status === "success") {
                    updateStats(data.posts);
                    
                    if (data.posts.length > 0) {
                        postsContainer.innerHTML = data.posts
                            .map((post) => createPostHTML(post))
                            .join("");
                    } else {
                        postsContainer.innerHTML = `
                            <div class="empty-state">
                                <div class="empty-icon">📭</div>
                                <h3 class="empty-title">No posts yet</h3>
                                <p class="empty-description">Start by creating your first lost or found post to help your community!</p>
                                <a href="post.html" class="create-post-btn">Create Your First Post</a>
                            </div>
                        `;
                    }
                } else {
                    if (data.message.includes('not authenticated')) {
                        window.location.href = 'login.php?error=Session expired. Please log in again.';
                    } else {
                        showMessage(data.message, true);
                    }
                }
            } catch (error) {
                loading.style.display = "none";
                showMessage(`Error loading posts: ${error.message}`, true);
            }
        }

        function resolvePost(post_id) {
            if (confirm("Mark this post as resolved? This action cannot be undone.")) {
                fetch("php_actions/resolve_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `post_id=${post_id}`,
                })
                    .then((response) => response.json())
                    .then((result) => {
                        if (result.status === "success") {
                            showMessage(result.message, false);
                            loadPosts();
                        } else {
                            showMessage(result.message, true);
                        }
                    })
                    .catch((err) => {
                        showMessage(`Error: ${err.message}`, true);
                    });
            }
        }

        function deletePost(post_id) {
            if (confirm("Are you sure you want to delete this post? This action cannot be undone.")) {
                fetch("php_actions/delete_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `post_id=${post_id}`,
                })
                    .then((response) => response.json())
                    .then((result) => {
                        if (result.status === "success") {
                            showMessage(result.message, false);
                            loadPosts();
                        } else {
                            showMessage(result.message, true);
                        }
                    })
                    .catch((err) => {
                        showMessage(`Error: ${err}`, true);
                    });
            }
        }

        document.addEventListener("DOMContentLoaded", loadPosts);
    </script>
    
    <footer style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 3rem 0 1.5rem; margin-top: 3rem; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <!-- Quick Links Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <!-- Navigation Column -->
            <div>
                <h3 style="color: #1e3c72; font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Navigation</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="main.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🏠</span> Home
                    </a>
                    <a href="post_view.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>📝</span> My Posts
                    </a>
                    <a href="post.html" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>➕</span> Create Post
                    </a>
                </div>
            </div>
            
            <!-- Communication Column -->
            <div>
                <h3 style="color: #1e3c72; font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Communication</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="messages.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>💬</span> Messages
                    </a>
                    <a href="notifications.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔔</span> Notifications
                    </a>
                    <a href="profile.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>👤</span> Profile
                    </a>
                </div>
            </div>
            
            <!-- Information Column -->
            <div>
                <h3 style="color: #1e3c72; font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Information</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>ℹ️</span> About
                    </a>
                    <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔒</span> Privacy Policy
                    </a>
                    <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>📋</span> Terms of Service
                    </a>
                    <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;">
                        <span>📧</span> Contact
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Divider -->
        <div style="border-top: 1px solid #e5e7eb; margin: 2rem 0 1.5rem;"></div>
        
        <!-- Bottom Section -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <p style="color: #6b7280; font-size: 0.85rem; margin: 0;">
                © <?php echo date('Y'); ?> Lost & Found. All rights reserved.
            </p>
            <!-- <div style="display: flex; gap: 1rem;">
                <a href="#" style="color: #9ca3af; font-size: 1.2rem; transition: color 0.3s;" title="Facebook">📘</a>
                <a href="#" style="color: #9ca3af; font-size: 1.2rem; transition: color 0.3s;" title="Twitter">🐦</a>
                <a href="#" style="color: #9ca3af; font-size: 1.2rem; transition: color 0.3s;" title="Instagram">📷</a>
            </div> -->
        </div>
    </div>
</footer>

<style>
footer a:hover {
    color: #764ba2 !important;
    transform: translateX(3px);
}
</style>
</body>
</html>