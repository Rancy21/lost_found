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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lost & Found - Community Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
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
        flex-wrap: wrap;
        gap: 1rem;
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

      .search-container {
        flex: 1;
        max-width: 400px;
        position: relative;
        margin: 0 1rem;
      }

      .search-bar {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 25px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
      }

      .search-bar:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }

      .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1.2rem;
      }

      .quick-actions {
        display: flex;
        gap: 0.75rem;
      }

      .action-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .btn-lost {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: white;
      }

      .btn-found {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
      }

      .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      }

      .user-profile {
        position: relative;
      }

      .profile-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-size: 1.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .profile-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      }

      .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 0.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid #e5e7eb;
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
      }

      .dropdown-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
      }

      .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
      }

      .dropdown-item:last-child {
        border-bottom: none;
      }

      .dropdown-item:hover {
        background: #f9fafb;
        color: #1e3c72;
      }

      .dropdown-item:first-child {
        border-radius: 12px 12px 0 0;
      }

      .dropdown-item:last-child {
        border-radius: 0 0 12px 12px;
      }

      /* Main Content */
      .container {
        max-width: 800px;
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

      /* Filter Tabs */
      .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 0.5rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }

      .filter-tab {
        flex: 1;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        background: transparent;
        color: #6b7280;
      }

      .filter-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      }

      .filter-tab:hover:not(.active) {
        background: rgba(255, 255, 255, 0.5);
        color: #374151;
      }

      /* Loading State */
      .loading {
        text-align: center;
        padding: 3rem;
        color: white;
        font-size: 1.1rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        margin-bottom: 2rem;
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

      /* Feed Container */
      .feed-container {
        display: flex;
        flex-direction: column;
        gap: 2rem;
      }

      /* Post Cards */
      .post-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .post-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      }

      /* Post Header */
      .post-header {
        padding: 1.5rem 1.5rem 0;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
      }

      .post-type-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: capitalize;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }

      .post-card.lost .post-type-badge {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid #f59e0b;
      }

      .post-card.found .post-type-badge {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border: 1px solid #10b981;
      }

      .post-icon {
        font-size: 1.1rem;
      }

      .post-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.25rem;
      }

      .post-date {
        font-size: 0.8rem;
        color: #9ca3af;
        font-weight: 500;
      }

      /* Post Content */
      .post-content {
        padding: 0 1.5rem 1.5rem;
      }

      .post-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.3;
        margin: 1rem 0;
      }

      .post-description {
        color: #4b5563;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      /* Post Image */
      .post-image-container {
        margin: 0 -1.5rem 1.5rem;
        width: calc(100% + 3rem);
        height: 300px;
        overflow: hidden;
        position: relative;
        background: #f8fafc;
      }

      .post-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      /* Post Details */
      .post-details {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        margin: 0 -1.5rem;
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
      }

      .detail-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 500;
      }

      .detail-icon {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
      }

      .detail-text {
        color: #374151;
        font-weight: 600;
      }

      /* Post Actions */
      .post-actions {
        padding: 1.25rem 1.5rem;
        background: white;
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
      }

      .contact-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 0.75rem 1.25rem;
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
        flex: 1;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
      }

      .contact-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
      }

      .share-btn {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        padding: 0.75rem 1.25rem;
        border: none;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
        justify-content: center;
      }

      .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
      }

      .own-post-label {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
        flex: 1;
        border: 1px solid #f59e0b;
      }

      .btn-icon {
        font-size: 1rem;
      }

      /* Empty State */
      .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
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
        font-size: 1rem;
        margin-bottom: 2rem;
      }

      /* No Posts State */
      .no-posts {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .no-posts-content {
        max-width: 400px;
        margin: 0 auto;
      }

      .no-posts-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.6;
      }

      .no-posts h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
      }

      .no-posts p {
        color: #6b7280;
        font-size: 1rem;
        margin-bottom: 2rem;
      }

      /* Error Message */
      .error-message {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #dc2626;
        border-left: 4px solid #dc2626;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        font-weight: 500;
        display: none;
        align-items: center;
        gap: 0.75rem;
      }

      /* Debug Info */
      .debug-info {
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        font-family: monospace;
        font-size: 0.8rem;
        margin-bottom: 1rem;
        display: none;
      }

      /* Notification */
      .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        animation: slideIn 0.3s ease;
      }

      .notification.success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      }

      .notification.error {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      }

      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        .header-content {
          flex-direction: column;
          gap: 1rem;
          padding: 0 1rem;
        }

        .search-container {
          order: 3;
          max-width: 100%;
          margin: 0;
        }

        .quick-actions {
          gap: 0.5rem;
        }

        .action-btn {
          padding: 0.5rem 1rem;
          font-size: 0.8rem;
        }

        .container {
          padding: 1rem;
        }

        .page-title {
          font-size: 2rem;
        }

        .filter-tabs {
          flex-direction: column;
          gap: 0.25rem;
        }

        .post-header {
          padding: 1rem 1rem 0;
          flex-direction: column;
          align-items: flex-start;
          gap: 1rem;
        }

        .post-meta {
          align-items: flex-start;
          width: 100%;
        }

        .post-content {
          padding: 0 1rem 1rem;
        }

        .post-title {
          font-size: 1.2rem;
        }

        .post-description {
          font-size: 0.95rem;
        }

        .post-image-container {
          margin: 0 -1rem 1rem;
          width: calc(100% + 2rem);
          height: 200px;
        }

        .post-details {
          margin: 0 -1rem;
          padding: 1rem;
        }

        .post-actions {
          padding: 1rem;
          flex-direction: column;
          gap: 0.5rem;
        }

        .contact-btn,
        .share-btn {
          flex: none;
          width: 100%;
        }
      }

      @media (max-width: 480px) {
        .container {
          padding: 0.5rem;
        }

        .page-title {
          font-size: 1.8rem;
        }

        .page-subtitle {
          font-size: 1rem;
        }

        .post-header {
          padding: 1rem;
          gap: 0.75rem;
        }

        .post-title {
          font-size: 1.1rem;
        }

        .post-description {
          font-size: 0.9rem;
        }

        .post-image-container {
          height: 180px;
        }

        .detail-item {
          font-size: 0.85rem;
        }
      }
    </style>
  </head>
  <body>
    <!-- Header -->
    <header class="header">
      <div class="header-content">
        <!-- Logo -->
        <a href="#" class="logo-section">
          <span class="logo">🔍</span>
          <h1 class="header-title">Lost & Found</h1>
        </a>

        <!-- Search Bar -->
        <div class="search-container">
          <span class="search-icon">🔍</span>
          <input
            type="text"
            class="search-bar"
            placeholder="Search for lost or found items..."
            id="searchInput"
          />
        </div>

        <!-- Quick Action Buttons -->
        <div class="quick-actions">
          <a href="post.html?type=lost" class="action-btn btn-lost">
            😢 Post Lost Item
          </a>
          <a href="post.html?type=found" class="action-btn btn-found">
            😊 Post Found Item
          </a>
        </div>

        <!-- Notification Bell will be inserted here by JavaScript -->

        <!-- User Profile Dropdown -->
        <div class="user-profile">
          <button class="profile-btn" id="profileBtn">
            <span id="userInitials"><?php echo strtoupper(substr($user_name, 0, 1)); ?></span>
          </button>
          <div class="dropdown-menu" id="dropdownMenu">
            <a href="post_view.php" class="dropdown-item">📝 My Posts</a>
            <a href="messages.php" class="dropdown-item">💬 Messages</a>
            <a href="notifications.php" class="dropdown-item">🔔 Notifications</a>
            <a href="profile.php" class="dropdown-item">👤 Profile</a>
            <a href="php_actions/logout.php" class="dropdown-item">🚪 Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="container">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Community Feed</h1>
        <p class="page-subtitle">Help reunite lost items with their owners</p>
      </div>

      <!-- Debug Info (for troubleshooting) -->
      <div class="debug-info" id="debugInfo"></div>

      <!-- Error Message -->
      <div class="error-message" id="errorMessage"></div>

      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">All Items</button>
        <button class="filter-tab" data-filter="lost">Lost Items</button>
        <button class="filter-tab" data-filter="found">Found Items</button>
        <button class="filter-tab" data-filter="recent">Recent</button>
      </div>

      <!-- Loading State -->
      <div class="loading" id="loading">
        <div class="loading-spinner"></div>
        Loading posts...
      </div>

      <!-- Feed Container -->
      <div class="feed-container" id="feedContainer"></div>

      <!-- Empty State -->
      <div class="empty-state" id="emptyState" style="display: none">
        <div class="empty-icon">📭</div>
        <h3 class="empty-title">No items found</h3>
        <p class="empty-description">
          Try adjusting your search or filter criteria
        </p>
        <a href="post.html" class="action-btn btn-found">Post an Item</a>
      </div>
    </main>

    <!-- Include notification JavaScript -->
    <script src="assets/js/notifications.js"></script>
    <script>
      let allPosts = [];
      let currentFilter = "all";
      let currentSearch = "";
      let currentUser = <?php echo json_encode($_SESSION['user_id']); ?>;
    
      // Add contact functionality
      function contactUser(postId, postOwnerId, postTitle) {
          // Check if user is trying to contact themselves
          if (postOwnerId === currentUser) {
              showNotification('You cannot contact yourself about your own post', 'error');
              return;
          }
          
          // Redirect to messages page with post context
          const params = new URLSearchParams({
              action: 'contact',
              post_id: postId,
              owner_id: postOwnerId,
              post_title: postTitle
          });
          
          window.location.href = `messages.php?${params.toString()}`;
      }
      
      function showNotification(message, type) {
          const notification = document.createElement('div');
          notification.className = `notification ${type}`;
          notification.textContent = message;
          document.body.appendChild(notification);
          setTimeout(() => {
              notification.remove();
          }, 3000);
      }
      
      function sharePost(postId) {
        // Simple share functionality - you can enhance this
        const url = `${window.location.origin}/post_details.php?id=${postId}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Lost & Found Item',
                url: url
            }).catch(console.error);
        } else {
            // Fallback - copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                showNotification('Link copied to clipboard!', 'success');
            }).catch(() => {
                showNotification('Could not copy link', 'error');
            });
        }
      }
    
      // Initialize page
      document.addEventListener("DOMContentLoaded", () => {
        loadPosts();
        initializeEventListeners();
      });

      // Load posts from backend
      async function loadPosts() {
        const loading = document.getElementById("loading");
        const feedContainer = document.getElementById("feedContainer");
        const errorMessage = document.getElementById("errorMessage");
        const debugInfo = document.getElementById("debugInfo");

        loading.style.display = "block";
        feedContainer.style.display = "none";
        errorMessage.style.display = "none";

        try {
          let url;

          // Determine which endpoint to use
          if (currentSearch.trim() !== "") {
            // Use search endpoint
            const params = new URLSearchParams({
              search: currentSearch,
            });
            url = `php_actions/search_posts.php?${params}`;
          } else if (currentFilter !== "all") {
            // Use filter endpoint
            const params = new URLSearchParams({
              filter: currentFilter,
            });
            url = `php_actions/filter_posts.php?${params}`;
          } else {
            // Use basic get all posts endpoint
            url = `php_actions/get_users_post.php`;
          }

          debugInfo.textContent = `Fetching: ${url}`;
          debugInfo.style.display = "block";

          const response = await fetch(url);

          // Check if response is ok
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          // Get response text first to debug
          const responseText = await response.text();
          debugInfo.textContent += `\nResponse: ${responseText.substring(
            0,
            200
          )}...`;

          // Try to parse JSON
          let data;
          try {
            data = JSON.parse(responseText);
          } catch (parseError) {
            throw new Error(
              `JSON Parse Error: ${parseError.message}\nResponse: ${responseText}`
            );
          }

          loading.style.display = "none";
          debugInfo.style.display = "none"; // Hide debug info on success

          if (data.status === "success") {
            allPosts = data.posts;
            console.log("Loaded posts:", allPosts);
            displayPosts(allPosts);
            feedContainer.style.display = "flex";
          } else {
            if (data.message && data.message.includes("not authenticated")) {
              window.location.href =
                "login.php?error=Please log in to view posts";
            } else {
              showError(data.message || "Unknown error occurred");
            }
          }
        } catch (error) {
          loading.style.display = "none";
          showError(`Error loading posts: ${error.message}`);
          console.error("Full error:", error);
        }
      }

      // Update the displayPosts function to include contact buttons
      function displayPosts(posts) {
          const container = document.getElementById('feedContainer');
          const loading = document.getElementById('loading');
          
          loading.style.display = 'none';
          
          if (posts.length === 0) {
              container.innerHTML = `
                  <div class="no-posts">
                      <div class="no-posts-content">
                          <span class="no-posts-icon">📭</span>
                          <h3>No posts found</h3>
                          <p>Be the first to post a lost or found item!</p>
                      </div>
                  </div>
              `;
              return;
          }
          
          container.innerHTML = posts.map(post => `
              <article class="post-card ${post.type}">
                  <div class="post-header">
                      <div class="post-type-badge ${post.type}">
                          <span class="post-icon">${post.type === 'lost' ? '😢' : '😊'}</span>
                          <span>${post.type.charAt(0).toUpperCase() + post.type.slice(1)}</span>
                      </div>
                      <div class="post-meta">
                          <span class="post-date">${formatDate(post.date_posted)}</span>
                      </div>
                  </div>
                  
                  <div class="post-content">
                      <h2 class="post-title">${escapeHtml(post.title)}</h2>
                      <p class="post-description">${escapeHtml(post.description)}</p>
                      
                      ${post.image_url ? `
                          <div class="post-image-container">
                              <img src="${post.image_url}" alt="${escapeHtml(post.title)}" class="post-image">
                          </div>
                      ` : ''}
                      
                      <div class="post-details">
                          <div class="detail-item">
                              <span class="detail-icon">📍</span>
                              <span class="detail-text">${escapeHtml(post.location_name || 'Location not specified')}</span>
                          </div>
                          <div class="detail-item">
                              <span class="detail-icon">👤</span>
                              <span class="detail-text">Posted by ${escapeHtml(post.user_name)}</span>
                          </div>
                      </div>
                  </div>
                  
                  <div class="post-actions">
                      ${post.user_id !== currentUser ? `
                          <button class="contact-btn" onclick="contactUser(${post.id}, ${post.user_id}, '${escapeHtml(post.title).replace(/'/g, "'")}')">
                              <span class="btn-icon">💬</span>
                              Contact
                          </button>
                      ` : `
                          <span class="own-post-label">Your post</span>
                      `}
                      <button class="share-btn" onclick="sharePost(${post.id})">
                          <span class="btn-icon">📤</span>
                          Share
                      </button>
                  </div>
              </article>
          `).join('');
      }

      // Initialize event listeners
      function initializeEventListeners() {
        // Profile dropdown
        const profileBtn = document.getElementById("profileBtn");
        const dropdownMenu = document.getElementById("dropdownMenu");

        profileBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          dropdownMenu.classList.toggle("active");
        });

        document.addEventListener("click", () => {
          dropdownMenu.classList.remove("active");
        });

        // Filter tabs
        const filterTabs = document.querySelectorAll(".filter-tab");
        filterTabs.forEach((tab) => {
          tab.addEventListener("click", () => {
            filterTabs.forEach((t) => t.classList.remove("active"));
            tab.classList.add("active");

            currentFilter = tab.dataset.filter;
            loadPosts();
          });
        });

        // Search functionality
        const searchInput = document.getElementById("searchInput");
        let searchTimeout;

        searchInput.addEventListener("input", (e) => {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            currentSearch = e.target.value.trim();
            loadPosts();
          }, 500); // Debounce search
        });
      }

      // Utility functions
      function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
      }

      function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays === 1) {
          return "Yesterday";
        } else if (diffDays < 7) {
          return `${diffDays} days ago`;
        } else {
          return date.toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
          });
        }
      }

      function showError(message) {
        const errorMessage = document.getElementById("errorMessage");
        errorMessage.innerHTML = `⚠️ ${message}`;
        errorMessage.style.display = "flex";

        setTimeout(() => {
          errorMessage.style.display = "none";
        }, 10000); // Show error longer for debugging
      }
    </script>
  </body>
</html>