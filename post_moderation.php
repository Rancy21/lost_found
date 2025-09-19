<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to view your posts');
    exit;
}

$user_role = $_SESSION['user_role'];

function redirect_to_posts()
{
    header('Location: ../main.php');
    exit;
}

if ($user_role !== 'admin') {
    redirect_to_posts();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Moderation - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style/moderation.css" />
    <style>
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1 class="page-title">📋 Post Moderation</h1>
               <div style="display: flex; gap: 1rem;">
        <a href="users.php" class="back-btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            👥 User Management
        </a>
        <a href="admin_dashboard.php" class="back-btn">
            ← Back to Main
        </a>
    </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number" id="pending-count">-</div>
                <div class="stat-label">Pending Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-count">-</div>
                <div class="stat-label">Total Posts</div>
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

        <!-- Error Message -->
        <div id="error-message" class="error-message"></div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Pending Posts for Review</h2>
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
                <h3>No Pending Posts</h3>
                <p>All posts have been reviewed!</p>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Review Post</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-content" id="modal-content">
                <!-- Post details will be loaded here -->
            </div>
            <div class="modal-actions">
                <button class="action-btn btn-approve" onclick="approveCurrentPost()">✓ Approve</button>
                <button class="action-btn btn-reject" onclick="showReasonModal('reject')">⚠ Reject</button>
                <button class="action-btn btn-delete" onclick="showReasonModal('delete')">🗑 Delete</button>
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
        let currentAction = null; // 'reject' or 'delete'

        // Load posts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadPosts();
        });

        async function loadPosts() {
            showLoading();
            hideError();

            try {
                const response = await fetch('php_actions/admin_actions/get_posts.php', {
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
                } else {
                    showError('Error loading posts: ' + data.message);
                }
            } catch (error) {
                showError('Network error: ' + error.message);
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
                    <td class="date-cell">${new Date(post.date_posted).toLocaleDateString()}</td>
                    <td class="user-cell">${post.user_name}</td>
                    <td>
                        <button class="review-btn" onclick="openModal(${post.id})">👁 Review</button>
                    </td>
                </tr>
            `).join('');
        }

        function updateStats() {
            document.getElementById('pending-count').textContent = currentPosts.length;
            document.getElementById('total-count').textContent = currentPosts.length;
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
            
            if (action === 'reject') {
                title.textContent = 'Reject Post - Provide Reason';
                confirmBtn.textContent = 'Reject Post';
                confirmBtn.className = 'reason-btn reason-btn-confirm reject';
                textarea.placeholder = 'Please provide a reason for rejecting this post...';
            } else if (action === 'delete') {
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
            console.log('Confirming action:', currentAction); // Debug log
            const reason = document.getElementById('reason-textarea').value.trim();
            
            if (!reason) {
                showMessage('Please provide a reason for this action.', true);
                return;
            }

            if (!currentPost || !currentAction) {
                console.error('No current post or action selected.');x
                return;
            }

            
            console.log('Current Post ID:', currentPost.id); // Debug log
            console.log('Reason provided:', reason); // Debug log

            if (currentAction === 'reject') {
                console.log('Rejecting post:', currentPost.id, 'with reason:', reason); // Debug log
                await rejectCurrentPostWithReason(reason);
            } else if (currentAction === 'delete') {
                console.log('Deleting post:', currentPost.id, 'with reason:', reason); // Debug log
                await deleteCurrentPostWithReason(reason);
            }
            closeReasonModal();
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
            
            messageBlock.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                hideMessage();
            }, 5000);
        }

        function hideMessage() {
            document.getElementById('message-block').style.display = 'none';
        }

        // Updated admin action functions with notifications
        async function approveCurrentPost() {
            if (!currentPost) return;
            try {
                const response = await fetch('php_actions/admin_actions/approve_with_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${currentPost.id}`
                });

                const data = await response.json();
                
                if (data.status === 'success') {
                    showMessage('Post approved successfully and user notified!', false);
                    closeModal();
                    loadPosts();
                } else {
                    showMessage('Error approving post: ' + data.message, true);
                }
            } catch (error) {
                showMessage('Network error: ' + error.message, true);
            }
        }

        async function rejectCurrentPostWithReason(reason) {
            if (!currentPost) return;
            
            console.log('Rejecting post:', currentPost.id, 'with reason:', reason); // Debug log
            
            try {
                const response = await fetch('php_actions/admin_actions/reject_with_reason.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${currentPost.id}&reason=${encodeURIComponent(reason)}`
                });

                console.log('Response status:', response.status); // Debug log
                
                const data = await response.json();
                console.log('Response data:', data); // Debug log
                
                if (data.status === 'success') {
                    showMessage('Post rejected successfully and user notified!', false);
                    closeModal();
                    loadPosts();
                } else {
                    showMessage('Error rejecting post: ' + data.message, true);
                }
            } catch (error) {
                console.error('Network error:', error); // Debug log
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



                const data = await response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
                
                if (data.status === 'success') {
                    showMessage(data.message, false);
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

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        function hideError() {
            document.getElementById('error-message').style.display = 'none';
        }
    </script>
</body>
</html>