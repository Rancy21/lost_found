<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    header("Location: login.php?error=Please log in to access post management");
    exit();
}

$user_role = $_SESSION["user_role"];

if ($user_role !== "admin") {
    header("Location: main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Management - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style/moderation.css" />
    <style>
        /* Search and Filter Bar */
        .filter-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-container {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .clear-btn {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .clear-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }

        .results-info {
            background: #f3f4f6;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        /* Reason Modal Styles */
        .reason-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }

        .reason-modal.active {
            display: flex;
        }

        .reason-modal-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .reason-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .reason-modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .reason-textarea {
            width: 100%;
            min-height: 120px;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            resize: vertical;
            margin-bottom: 1.5rem;
        }

        .reason-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .reason-modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .reason-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .reason-btn-cancel {
            background: #f3f4f6;
            color: #6b7280;
        }

        .reason-btn-cancel:hover {
            background: #e5e7eb;
        }

        .reason-btn-confirm {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .reason-btn-confirm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .reason-btn-confirm.reject {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .reason-btn-confirm.reject:hover {
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        @media (max-width: 768px) {
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1 class="page-title">📊 Post Management</h1>
            <div style="display: flex; gap: 1rem;">
                <a href="post_moderation.php" class="back-btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    📋 Moderation
                </a>
                <a href="users.php" class="back-btn" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    👥 Users
                </a>
                <a href="admin_dashboard.php" class="back-btn">
                    ← Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number" id="total-count">-</div>
                <div class="stat-label">Total Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="active-count">-</div>
                <div class="stat-label">Active Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="rejected-count">-</div>
                <div class="stat-label">Rejected Posts</div>
            </div>
        </div>

        <!-- Message Block -->
        <div id="message-block" class="message-block" style="display: none;">
            <div class="message-content">
                <span class="message-icon" id="message-icon"></span>
                <span class="message-text" id="message-text"></span>
                <button class="message-close" onclick="hideMessage()">&times;</button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-container">
                <span class="search-icon">🔍</span>
                <input type="text" id="search-input" class="search-input" placeholder="Search by description or location...">
            </div>
            
            <select id="type-filter" class="filter-select">
                <option value="all">All Types</option>
                <option value="lost">Lost Items</option>
                <option value="found">Found Items</option>
            </select>
            
            <select id="status-filter" class="filter-select">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="rejected">Rejected</option>
            </select>
            
            <button class="clear-btn" onclick="clearFilters()">Clear Filters</button>
        </div>

        <!-- Results Info -->
        <div id="results-info" class="results-info" style="display: none;"></div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Posts Management</h2>
                <button class="refresh-btn" onclick="loadPosts()">🔄 Refresh</button>
            </div>

            <!-- Loading State -->
            <div id="loading" class="loading">
                <div class="loading-spinner"></div>
                <p>Loading posts...</p>
            </div>

            <!-- Posts Table -->
            <table class="posts-table" id="posts-table" style="display: none;">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date Posted</th>
                        <th>Posted By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="posts-tbody">
                </tbody>
            </table>

            <!-- Empty State -->
            <div id="empty-state" class="empty-state" style="display: none;">
                <div class="empty-icon">📭</div>
                <h3>No Posts Found</h3>
                <p>No posts match your current filters.</p>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Post Details</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-content" id="modal-content">
                <!-- Post details will be loaded here -->
            </div>
            <div class="modal-actions">
                <button class="action-btn btn-pending" onclick="setPendingCurrentPost()">⏳ Set to Pending</button>
                <button class="action-btn btn-delete" onclick="showReasonModal('delete')">🗑 Delete Post</button>
            </div>
        </div>
    </div>

    <!-- Reason Modal -->
    <div class="reason-modal" id="reason-modal">
        <div class="reason-modal-content">
            <div class="reason-modal-header">
                <h3 class="reason-modal-title" id="reason-modal-title">Provide Reason</h3>
                <button class="close-btn" onclick="closeReasonModal()">&times;</button>
            </div>
            <textarea 
                id="reason-textarea" 
                class="reason-textarea" 
                placeholder="Please provide a reason for this action..."
                maxlength="500"
            ></textarea>
            <div class="reason-modal-actions">
                <button class="reason-btn reason-btn-cancel" onclick="closeReasonModal()">Cancel</button>
                <button class="reason-btn reason-btn-confirm" id="reason-confirm-btn" onclick="confirmReasonAction()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let currentPosts = [];
        let currentPost = null;
        let currentAction = null; // 'delete'
        let currentFilters = { type: 'all', status: 'all', search: '' };

        // Load posts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadPosts();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search input with debounce
            let searchTimeout;
            document.getElementById('search-input').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentFilters.search = this.value;
                    loadPosts();
                }, 500);
            });

            // Filter selects
            document.getElementById('type-filter').addEventListener('change', function() {
                currentFilters.type = this.value;
                loadPosts();
            });

            document.getElementById('status-filter').addEventListener('change', function() {
                currentFilters.status = this.value;
                loadPosts();
            });
        }

        async function loadPosts() {
            showLoading();
            hideMessage();

            try {
                const params = new URLSearchParams(currentFilters);
                const response = await fetch(`php_actions/admin_actions/filter_posts_admin.php?${params}`, {
                    method: 'GET',
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.status === 'success') {
                    currentPosts = data.posts;
                    displayPosts();
                    updateStats();
                    updateResultsInfo(data);
                } else {
                    showMessage('Error loading posts: ' + data.message, true);
                }
            } catch (error) {
                showMessage('Network error: ' + error.message, true);
            } finally {
                hideLoading();
            }
        }

        function displayPosts() {
            const tbody = document.getElementById('posts-tbody');
            const table = document.getElementById('posts-table');
            const emptyState = document.getElementById('empty-state');

            if (currentPosts.length === 0) {
                table.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            table.style.display = 'table';
            emptyState.style.display = 'none';

            tbody.innerHTML = currentPosts.map(post => `
                <tr>
                    <td class="description-cell" title="${post.description || 'No description'}">${post.description || 'No description'}</td>
                    <td><span class="role-badge role-${post.type}">${post.type === 'lost' ? '🔍 Lost' : '🎯 Found'}</span></td>
                    <td><span class="status-badge status-${post.status}">${post.status}</span></td>
                    <td class="date-cell">${new Date(post.date_posted).toLocaleDateString()}</td>
                    <td class="user-cell">${post.user_name}</td>
                    <td>
                        <button class="review-btn" onclick="openModal(${post.id})">👁 View Details</button>
                    </td>
                </tr>
            `).join('');
        }

        function updateStats() {
            const totalCount = currentPosts.length;
            const activeCount = currentPosts.filter(post => post.status === 'active').length;
            const rejectedCount = currentPosts.filter(post => post.status === 'rejected').length;

            document.getElementById('total-count').textContent = totalCount;
            document.getElementById('active-count').textContent = activeCount;
            document.getElementById('rejected-count').textContent = rejectedCount;
        }

        function updateResultsInfo(data) {
            const resultsInfo = document.getElementById('results-info');
            const hasFilters = data.filters.type !== 'all' || data.filters.status !== 'all' || data.filters.search !== '';
            
            if (hasFilters) {
                let filterText = [];
                if (data.filters.type !== 'all') filterText.push(`Type: ${data.filters.type}`);
                if (data.filters.status !== 'all') filterText.push(`Status: ${data.filters.status}`);
                if (data.filters.search !== '') filterText.push(`Search: "${data.filters.search}"`);

                resultsInfo.textContent = `Showing ${data.count} posts filtered by: ${filterText.join(', ')}`;
                resultsInfo.style.display = 'block';
            } else {
                resultsInfo.style.display = 'none';
            }
        }

        function clearFilters() {
            currentFilters = { type: 'all', status: 'all', search: '' };
            document.getElementById('search-input').value = '';
            document.getElementById('type-filter').value = 'all';
            document.getElementById('status-filter').value = 'all';
            loadPosts();
        }

        function openModal(postId) {
            currentPost = currentPosts.find(post => post.id === postId);
            if (!currentPost) return;

            const modalContent = document.getElementById('modal-content');
            modalContent.innerHTML = `
                <div class="post-detail">
                    <span class="detail-label">Description:</span>
                    <div class="detail-value">${currentPost.description || 'No description provided'}</div>
                </div>
                <div class="post-detail">
                    <span class="detail-label">Type:</span>
                    <div class="detail-value">${currentPost.type || 'Unknown'}</div>
                </div>
                <div class="post-detail">
                    <span class="detail-label">Status:</span>
                    <div class="detail-value"><span class="status-badge status-${currentPost.status}">${currentPost.status}</span></div>
                </div>
                <div class="post-detail">
                    <span class="detail-label">Date Posted:</span>
                    <div class="detail-value">${new Date(currentPost.date_posted).toLocaleString()}</div>
                </div>
                <div class="post-detail">
                    <span class="detail-label">Location:</span>
                    <div class="detail-value">${currentPost.location_name || 'No location specified'}</div>
                </div>
                <div class="post-detail">
                    <span class="detail-label">Posted By:</span>
                    <div class="detail-value">${currentPost.user_name}</div>
                </div>
                ${currentPost.image_url ? `
                <div class="post-detail">
                    <span class="detail-label">Image:</span>
                    <img src="${currentPost.image_url}" alt="Post image" class="post-image-modal">
                </div>
                ` : '<div class="post-detail"><span class="detail-label">Image:</span><div class="detail-value">No image provided</div></div>'}
            `;

            document.getElementById('modal-overlay').classList.add('active');
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('active');
            currentPost = null;
        }

        function showReasonModal(action) {
            currentAction = action;
            const modal = document.getElementById('reason-modal');
            const title = document.getElementById('reason-modal-title');
            const confirmBtn = document.getElementById('reason-confirm-btn');
            const textarea = document.getElementById('reason-textarea');
            
            if (action === 'delete') {
                title.textContent = 'Delete Post - Provide Reason';
                confirmBtn.textContent = 'Delete Post';
                confirmBtn.className = 'reason-btn reason-btn-confirm';
                textarea.placeholder = 'Please provide a reason for deleting this post...';
            }
            
            textarea.value = '';
            modal.classList.add('active');
            textarea.focus();
        }

        function closeReasonModal() {
            document.getElementById('reason-modal').classList.remove('active');
            currentAction = null;
        }

        async function confirmReasonAction() {
            const reason = document.getElementById('reason-textarea').value.trim();
            
            if (!reason) {
                showMessage('Please provide a reason for this action.', true);
                return;
            }

            if (!currentPost || !currentAction) return;

            closeReasonModal();

            if (currentAction === 'delete') {
                await deleteCurrentPostWithReason(reason);
            }
        }

        // Close modal when clicking overlay
        document.getElementById('modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('reason-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReasonModal();
            }
        });

        async function setPendingCurrentPost() {
            if (!currentPost) return;
            
            if (!confirm(`Set this post to pending status? It will need admin review again.`)) {
                return;
            }

            try {
                const response = await fetch('php_actions/admin_actions/set_pending_with_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${currentPost.id}`
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showMessage(data.message, false);
                    closeModal();
                    loadPosts();
                } else {
                    showMessage('Error setting post to pending: ' + data.message, true);
                }
            } catch (error) {
                showMessage('Network error: ' + error.message, true);
            }
        }

        async function deleteCurrentPostWithReason(reason) {
            if (!currentPost) return;
            try {
                const response = await fetch('php_actions/admin_actions/delete_with_reason.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${currentPost.id}&reason=${encodeURIComponent(reason)}`
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showMessage('Post deleted successfully and user notified!', false);
                    closeModal();
                    loadPosts();
                } else {
                    showMessage('Error deleting post: ' + data.message, true);
                }
            } catch (error) {
                showMessage('Network error: ' + error.message, true);
            }
        }

        function showLoading() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('posts-table').style.display = 'none';
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
            
            setTimeout(() => {
                hideMessage();
            }, 5000);
        }

        function hideMessage() {
            document.getElementById('message-block').style.display = 'none';
        }
    </script>
</body>
</html>