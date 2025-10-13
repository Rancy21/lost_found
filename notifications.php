<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to view notifications');
    exit;
}

$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Lost & Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3c72;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        /* Main Content */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .notifications-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .notifications-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .notifications-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        /* Metrics Section */
        .metrics-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .metric-card.unread {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        .metric-card.approved {
            border-color: #10b981;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .metric-card.rejected {
            border-color: #ef4444;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }

        .metric-card.recent {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .metric-icon {
            font-size: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.8);
            flex-shrink: 0;
        }

        .metric-content {
            flex: 1;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
            line-height: 1;
        }

        .metric-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Filter Bar */
        .filter-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .mark-all-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: auto;
        }

        .mark-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        /* Notifications List */
        .notifications-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
        }

        .notification-item {
            padding: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            gap: 1rem;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: #f9fafb;
        }

        .notification-item.unread {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid #3b82f6;
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 10px;
            height: 10px;
            background: #3b82f6;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .notification-icon.approved {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .notification-icon.rejected {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }

        .notification-icon.deleted {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .notification-icon.pending {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .notification-message {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }

        .notification-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .notification-time {
            color: #9ca3af;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .notification-type {
            background: #f3f4f6;
            color: #6b7280;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
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

        .empty-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        /* Pagination */
        .pagination {
            padding: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            border-top: 1px solid #f3f4f6;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .pagination-btn:hover:not(.disabled) {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .pagination-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .metrics-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .metric-card {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .metric-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }

            .metric-value {
                font-size: 1.5rem;
            }

            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .mark-all-btn {
                margin-left: 0;
            }

            .notification-item {
                padding: 1rem;
            }

            .notification-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .metrics-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1 class="page-title">
                🔔 Notifications
            </h1>
            <a href="main.php" class="back-btn">
                ← Back to Home
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="notifications-header">
            <h2 class="notifications-title">Your Notifications</h2>
            <p class="notifications-subtitle">Stay updated with your post status and community activity</p>
        </div>

        <!-- Metrics Section -->
        <div class="metrics-container">
            <div class="metric-card">
                <div class="metric-icon">📊</div>
                <div class="metric-content">
                    <div class="metric-value" id="total-count">-</div>
                    <div class="metric-label">Total Notifications</div>
                </div>
            </div>
            <div class="metric-card unread">
                <div class="metric-icon">🔔</div>
                <div class="metric-content">
                    <div class="metric-value" id="unread-count">-</div>
                    <div class="metric-label">Unread</div>
                </div>
            </div>
            <!-- <div class="metric-card approved">
                <div class="metric-icon">✅</div>
                <div class="metric-content">
                    <div class="metric-value" id="approved-count">-</div>
                    <div class="metric-label">Approved</div>
                </div>
            </div> -->
            <!-- <div class="metric-card rejected">
                <div class="metric-icon">❌</div>
                <div class="metric-content">
                    <div class="metric-value" id="rejected-count">-</div>
                    <div class="metric-label">Rejected</div>
                </div>
            </div> -->
            <div class="metric-card recent">
                <div class="metric-icon">⏰</div>
                <div class="metric-content">
                    <div class="metric-value" id="recent-count">-</div>
                    <div class="metric-label">Last 24 Hours</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <select id="type-filter" class="filter-select">
                <option value="all">All Types</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="deleted">Deleted</option>
                <option value="pending">Pending</option>
            </select>
            
            <select id="status-filter" class="filter-select">
                <option value="all">All Status</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
            </select>
            
            <button class="mark-all-btn" id="mark-all-btn">
                ✓ Mark All as Read
            </button>
        </div>

        <!-- Notifications Container -->
        <div class="notifications-container">
            <!-- Loading State -->
            <div id="loading" class="loading">
                <div class="loading-spinner"></div>
                <p>Loading notifications...</p>
            </div>

            <!-- Notifications List -->
            <div id="notifications-list" class="notifications-list" style="display: none;">
                <!-- Notifications will be loaded here -->
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="empty-state" style="display: none;">
                <div class="empty-icon">📭</div>
                <h3 class="empty-title">No Notifications</h3>
                <p class="empty-subtitle">You're all caught up! New notifications will appear here.</p>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="pagination" style="display: none;">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        class NotificationsPage {
            constructor() {
                this.notifications = [];
                this.currentPage = 1;
                this.totalPages = 1;
                this.filters = {
                    type: 'all',
                    status: 'all'
                };
                this.init();
            }

            init() {
                this.setupEventListeners();
                this.loadMetrics();
                this.loadNotifications();
            }

            setupEventListeners() {
                // Filter change events
                document.getElementById('type-filter').addEventListener('change', (e) => {
                    this.filters.type = e.target.value;
                    this.currentPage = 1;
                    this.loadNotifications();
                });

                document.getElementById('status-filter').addEventListener('change', (e) => {
                    this.filters.status = e.target.value;
                    this.currentPage = 1;
                    this.loadNotifications();
                });

                // Mark all as read
                document.getElementById('mark-all-btn').addEventListener('click', () => {
                    this.markAllAsRead();
                });
            }

            async loadMetrics() {
                try {
                    const response = await fetch('php_actions/notifications/get_metrics.php');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        this.displayMetrics(data.metrics);
                    } else {
                        console.error('Failed to load metrics:', data.message);
                    }
                } catch (error) {
                    console.error('Error loading metrics:', error);
                }
            }

            displayMetrics(metrics) {
                document.getElementById('total-count').textContent = metrics.total || 0;
                document.getElementById('unread-count').textContent = metrics.unread || 0;
                // document.getElementById('approved-count').textContent = metrics.approved || 0;
                // document.getElementById('rejected-count').textContent = metrics.rejected || 0;
                document.getElementById('recent-count').textContent = metrics.recent || 0;
            }
            
            async loadNotifications() {
                this.showLoading();
            
                try {
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        type: this.filters.type,
                        status: this.filters.status
                    });
            
                    console.log('Loading notifications with params:', params.toString());
            
                    const response = await fetch(`php_actions/notifications/get_notifications.php?${params}`);
                    
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
            
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
            
                    const text = await response.text();
                    console.log('Raw response:', text);
            
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
            
                    console.log('Parsed data:', data);
            
                    if (data.status === 'success') {
                        this.notifications = data.notifications;
                        this.totalPages = data.total_pages;
                        this.displayNotifications();
                        this.displayPagination();
                    } else {
                        this.showError(data.message || 'Failed to load notifications');
                    }
                } catch (error) {
                    console.error('Error loading notifications:', error);
                    this.showError('Network error occurred: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            }

            displayNotifications() {
                const notificationsList = document.getElementById('notifications-list');
                const emptyState = document.getElementById('empty-state');

                if (this.notifications.length === 0) {
                    notificationsList.style.display = 'none';
                    emptyState.style.display = 'block';
                    return;
                }

                emptyState.style.display = 'none';
                notificationsList.style.display = 'block';

                notificationsList.innerHTML = this.notifications.map(notification => `
                    <div class="notification-item ${notification.is_read ? '' : 'unread'}" 
                         data-notification-id="${notification.id}"
                         onclick="notificationsPage.markAsRead(${notification.id})">
                        <div class="notification-icon ${notification.type}">
                            ${this.getNotificationIcon(notification.type)}
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                            <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                            <div class="notification-meta">
                                <span class="notification-time">${this.formatTime(notification.created_at)}</span>
                                <span class="notification-type">${notification.type}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            displayPagination() {
                const pagination = document.getElementById('pagination');
                
                if (this.totalPages <= 1) {
                    pagination.style.display = 'none';
                    return;
                }

                pagination.style.display = 'flex';

                let paginationHTML = '';

                // Previous button
                paginationHTML += `
                    <button class="pagination-btn ${this.currentPage === 1 ? 'disabled' : ''}" 
                            onclick="notificationsPage.goToPage(${this.currentPage - 1})"
                            ${this.currentPage === 1 ? 'disabled' : ''}>
                        ← Previous
                    </button>
                `;

                // Page numbers
                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(this.totalPages, this.currentPage + 2);

                if (startPage > 1) {
                    paginationHTML += `<button class="pagination-btn" onclick="notificationsPage.goToPage(1)">1</button>`;
                    if (startPage > 2) {
                        paginationHTML += `<span class="pagination-btn disabled">...</span>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHTML += `
                        <button class="pagination-btn ${i === this.currentPage ? 'active' : ''}" 
                                onclick="notificationsPage.goToPage(${i})">
                            ${i}
                        </button>
                    `;
                }

                if (endPage < this.totalPages) {
                    if (endPage < this.totalPages - 1) {
                        paginationHTML += `<span class="pagination-btn disabled">...</span>`;
                    }
                    paginationHTML += `<button class="pagination-btn" onclick="notificationsPage.goToPage(${this.totalPages})">${this.totalPages}</button>`;
                }

                // Next button
                paginationHTML += `
                    <button class="pagination-btn ${this.currentPage === this.totalPages ? 'disabled' : ''}" 
                            onclick="notificationsPage.goToPage(${this.currentPage + 1})"
                            ${this.currentPage === this.totalPages ? 'disabled' : ''}>
                        Next →
                    </button>
                `;

                pagination.innerHTML = paginationHTML;
            }

            goToPage(page) {
                if (page < 1 || page > this.totalPages || page === this.currentPage) return;
                
                this.currentPage = page;
                this.loadNotifications();
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            async markAsRead(notificationId) {
                try {
                    const response = await fetch('php_actions/notifications/mark_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            notification_id: notificationId
                        })
                    });

                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        // Update UI
                        const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                        if (notificationItem) {
                            notificationItem.classList.remove('unread');
                        }
                        
                        // Update notification in array
                        const notification = this.notifications.find(n => n.id == notificationId);
                        if (notification) {
                            notification.is_read = true;
                        }
                    }
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            }

            async markAllAsRead() {
                try {
                    const response = await fetch('php_actions/notifications/mark_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            mark_all: true
                        })
                    });

                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        // Update UI
                        const unreadItems = document.querySelectorAll('.notification-item.unread');
                        unreadItems.forEach(item => {
                            item.classList.remove('unread');
                        });
                        
                        // Update notifications in array
                        this.notifications.forEach(notification => {
                            notification.is_read = true;
                        });
                        
                        this.showToast('All notifications marked as read', 'success');
                    }
                } catch (error) {
                    console.error('Error marking all notifications as read:', error);
                    this.showToast('Failed to mark notifications as read', 'error');
                }
            }

            getNotificationIcon(type) {
                const icons = {
                    'approved': '✅',
                    'rejected': '❌',
                    'deleted': '🗑️',
                    'pending': '⏳'
                };
                return icons[type] || '📢';
            }

            formatTime(timestamp) {
                const date = new Date(timestamp);
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                if (diffInSeconds < 60) {
                    return 'Just now';
                } else if (diffInSeconds < 3600) {
                    const minutes = Math.floor(diffInSeconds / 60);
                    return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
                } else if (diffInSeconds < 86400) {
                    const hours = Math.floor(diffInSeconds / 3600);
                    return `${hours} hour${hours > 1 ? 's' : ''} ago`;
                } else if (diffInSeconds < 604800) {
                    const days = Math.floor(diffInSeconds / 86400);
                    return `${days} day${days > 1 ? 's' : ''} ago`;
                } else {
                    return date.toLocaleDateString();
                }
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            showLoading() {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('notifications-list').style.display = 'none';
                document.getElementById('empty-state').style.display = 'none';
            }

            hideLoading() {
                document.getElementById('loading').style.display = 'none';
            }

            showError(message) {
                const notificationsList = document.getElementById('notifications-list');
                const emptyState = document.getElementById('empty-state');
                
                notificationsList.style.display = 'none';
                emptyState.style.display = 'block';
                
                emptyState.innerHTML = `
                    <div class="empty-icon">⚠️</div>
                    <h3 class="empty-title">Error Loading Notifications</h3>
                    <p class="empty-subtitle">${this.escapeHtml(message)}</p>
                `;
            }

            showToast(message, type = 'info') {
                // Remove existing toast
                const existingToast = document.querySelector('.notification-toast');
                if (existingToast) {
                    existingToast.remove();
                }

                // Create new toast
                const toast = document.createElement('div');
                toast.className = `notification-toast ${type}`;
                toast.innerHTML = `
                    <span class="toast-icon">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
                    <span class="toast-message">${this.escapeHtml(message)}</span>
                `;

                // Add toast styles if not already added
                if (!document.querySelector('#toast-styles')) {
                    const style = document.createElement('style');
                    style.id = 'toast-styles';
                    style.textContent = `
                        .notification-toast {
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: white;
                            border-radius: 12px;
                            padding: 1rem 1.5rem;
                            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                            border-left: 4px solid #3b82f6;
                            z-index: 10000;
                            display: flex;
                            align-items: center;
                            gap: 0.75rem;
                            animation: slideInToast 0.3s ease;
                            max-width: 350px;
                        }

                        .notification-toast.success {
                            border-left-color: #10b981;
                        }

                        .notification-toast.error {
                            border-left-color: #ef4444;
                        }

                        .toast-message {
                            font-size: 0.9rem;
                            color: #374151;
                            font-weight: 500;
                        }

                        @keyframes slideInToast {
                            from {
                                transform: translateX(100%);
                                opacity: 0;
                            }
                            to {
                                transform: translateX(0);
                                opacity: 1;
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }

                document.body.appendChild(toast);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    toast.style.animation = 'slideInToast 0.3s ease reverse';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }, 3000);
            }
        }

        // Initialize notifications page
        let notificationsPage;
        document.addEventListener('DOMContentLoaded', () => {
            notificationsPage = new NotificationsPage();
        });
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