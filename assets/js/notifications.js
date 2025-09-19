class NotificationManager {
  constructor() {
    this.unreadCount = 0;
    this.notifications = [];
    this.isDropdownOpen = false;
    this.pollInterval = null;
    this.init();
  }

  init() {
    this.createNotificationBell();
    this.loadUnreadCount();
    this.startPolling();
    this.setupEventListeners();
  }

  createNotificationBell() {
    // Check if notification bell already exists
    if (document.getElementById("notification-bell")) return;

    // Find the user profile section to add notification bell
    let userProfile = document.querySelector(".user-profile");

    // If no user-profile found, try to find the mock-header for testing
    if (!userProfile) {
      const mockHeader = document.querySelector(".mock-header");
      if (mockHeader) {
        // Create a temporary container to act as user profile
        userProfile = mockHeader.querySelector(".user-profile");
      }
    }

    if (!userProfile) {
      console.warn("No user profile element found to insert notification bell");
      return;
    }

    // Create notification bell HTML
    const notificationBell = document.createElement("div");
    notificationBell.className = "notification-bell";
    notificationBell.id = "notification-bell";
    notificationBell.innerHTML = `
            <button class="notification-btn" id="notification-btn">
                <span class="bell-icon">🔔</span>
                <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
            </button>
            <div class="notification-dropdown" id="notification-dropdown">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <button class="mark-all-read-btn" id="mark-all-read-btn">Mark all read</button>
                </div>
                <div class="notification-list" id="notification-list">
                    <div class="notification-loading">Loading notifications...</div>
                </div>
                <div class="notification-footer">
                    <a href="notifications.php" class="view-all-link">View all notifications</a>
                </div>
            </div>
        `;

    // Insert before user profile
    userProfile.parentNode.insertBefore(notificationBell, userProfile);

    // Add CSS styles
    this.addNotificationStyles();
  }

  addNotificationStyles() {
    const style = document.createElement("style");
    style.textContent = `
            .notification-bell {
                position: relative;
                margin-right: 1rem;
            }

            .notification-btn {
                width: 45px;
                height: 45px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }

            .notification-btn:hover {
                transform: scale(1.05);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            }

            .notification-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #ef4444;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                font-size: 0.75rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid white;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }

            .notification-dropdown {
                position: absolute;
                top: 100%;
                right: 0;
                margin-top: 0.5rem;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                border: 1px solid #e5e7eb;
                width: 350px;
                max-height: 400px;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: all 0.3s ease;
                z-index: 1000;
            }

            .notification-dropdown.active {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .notification-header {
                padding: 1rem;
                border-bottom: 1px solid #f3f4f6;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .notification-header h3 {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 600;
                color: #374151;
            }

            .mark-all-read-btn {
                background: none;
                border: none;
                color: #667eea;
                font-size: 0.85rem;
                font-weight: 500;
                cursor: pointer;
                padding: 0.25rem 0.5rem;
                border-radius: 6px;
                transition: all 0.2s ease;
            }

            .mark-all-read-btn:hover {
                background: #f3f4f6;
                color: #4f46e5;
            }

            .notification-list {
                max-height: 250px;
                overflow-y: auto;
            }

            .notification-item {
                padding: 1rem;
                border-bottom: 1px solid #f3f4f6;
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
            }

            .notification-item:last-child {
                border-bottom: none;
            }

            .notification-item:hover {
                background: #f9fafb;
            }

            .notification-item.unread {
                background: #eff6ff;
                border-left: 3px solid #3b82f6;
            }

            .notification-item.unread::before {
                content: '';
                position: absolute;
                top: 1rem;
                right: 1rem;
                width: 8px;
                height: 8px;
                background: #3b82f6;
                border-radius: 50%;
            }

            .notification-title {
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.25rem;
                font-size: 0.9rem;
            }

            .notification-message {
                color: #6b7280;
                font-size: 0.85rem;
                line-height: 1.4;
                margin-bottom: 0.5rem;
            }

            .notification-time {
                color: #9ca3af;
                font-size: 0.75rem;
            }

            .notification-loading {
                padding: 2rem;
                text-align: center;
                color: #6b7280;
                font-size: 0.9rem;
            }

            .notification-empty {
                padding: 2rem;
                text-align: center;
                color: #6b7280;
                font-size: 0.9rem;
            }

            .notification-footer {
                padding: 0.75rem 1rem;
                border-top: 1px solid #f3f4f6;
                text-align: center;
            }

            .view-all-link {
                color: #667eea;
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 500;
                transition: color 0.2s ease;
            }

            .view-all-link:hover {
                color: #4f46e5;
            }

            /* Mobile responsive */
            @media (max-width: 768px) {
                .notification-dropdown {
                    width: 300px;
                    right: -50px;
                }
            }

            @media (max-width: 480px) {
                .notification-dropdown {
                    width: 280px;
                    right: -100px;
                }
            }
        `;
    document.head.appendChild(style);
  }

  setupEventListeners() {
    const notificationBtn = document.getElementById("notification-btn");
    const notificationDropdown = document.getElementById(
      "notification-dropdown",
    );
    const markAllReadBtn = document.getElementById("mark-all-read-btn");

    if (notificationBtn) {
      notificationBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggleDropdown();
      });
    }

    if (markAllReadBtn) {
      markAllReadBtn.addEventListener("click", () => {
        this.markAllAsRead();
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".notification-bell")) {
        this.closeDropdown();
      }
    });

    // Handle notification item clicks
    document.addEventListener("click", (e) => {
      if (e.target.closest(".notification-item")) {
        const notificationItem = e.target.closest(".notification-item");
        const notificationId = notificationItem.dataset.notificationId;
        if (notificationId) {
          this.markAsRead(notificationId);
        }
      }
    });
  }

  async loadUnreadCount() {
    try {
      console.log('Loading unread count...');
      const response = await fetch(
        "php_actions/notifications/get_unread_count.php",
      );
      console.log('Response status:', response.status);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('Unread count data:', data);

      if (data.status === "success") {
        this.updateUnreadCount(data.count);
      } else {
        console.error('API error:', data.message || 'Unknown error');
      }
    } catch (error) {
      console.error("Error loading unread count:", error);
    }
  }

  async loadNotifications() {
    try {
      console.log('Loading notifications...');
      const response = await fetch(
        "php_actions/notifications/get_notifications_dropdown.php",
      );
      console.log('Response status:', response.status);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('Notifications data:', data);

      if (data.status === "success") {
        this.notifications = data.notifications;
        this.displayNotifications();
      } else {
        console.error('API error:', data.message || 'Unknown error');
        this.displayError();
      }
    } catch (error) {
      console.error("Error loading notifications:", error);
      this.displayError();
    }
  }

  updateUnreadCount(count) {
    this.unreadCount = count;
    const badge = document.getElementById("notification-badge");

    if (badge) {
      if (count > 0) {
        badge.textContent = count > 99 ? "99+" : count;
        badge.style.display = "flex";
      } else {
        badge.style.display = "none";
      }
    }
  }

  displayNotifications() {
    const notificationList = document.getElementById("notification-list");

    if (!notificationList) return;

    if (this.notifications.length === 0) {
      notificationList.innerHTML = `
                <div class="notification-empty">
                    <span style="font-size: 2rem; margin-bottom: 0.5rem; display: block;">📭</span>
                    No notifications yet
                </div>
            `;
      return;
    }

    notificationList.innerHTML = this.notifications
      .map(
        (notification) => `
            <div class="notification-item ${notification.is_read ? "" : "unread"}" 
                 data-notification-id="${notification.id}">
                <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                <div class="notification-time">${this.formatTime(notification.created_at)}</div>
            </div>
        `,
      )
      .join("");
  }

  displayError() {
    const notificationList = document.getElementById("notification-list");
    if (notificationList) {
      notificationList.innerHTML = `
                    <div class="notification-empty">
                        <span style="font-size: 2rem; margin-bottom: 0.5rem; display: block;">⚠️</span>
                        Failed to load notifications
                    </div>
                `;
    }
  }

  toggleDropdown() {
    const dropdown = document.getElementById("notification-dropdown");

    if (this.isDropdownOpen) {
      this.closeDropdown();
    } else {
      this.openDropdown();
    }
  }

  openDropdown() {
    const dropdown = document.getElementById("notification-dropdown");
    if (dropdown) {
      dropdown.classList.add("active");
      this.isDropdownOpen = true;
      this.loadNotifications();
    }
  }

  closeDropdown() {
    const dropdown = document.getElementById("notification-dropdown");
    if (dropdown) {
      dropdown.classList.remove("active");
      this.isDropdownOpen = false;
    }
  }

  async markAsRead(notificationId) {
    try {
      const response = await fetch("php_actions/notifications/mark_read.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          notification_id: notificationId,
        }),
      });

      const data = await response.json();

      if (data.status === "success") {
        // Update UI
        const notificationItem = document.querySelector(
          `[data-notification-id="${notificationId}"]`,
        );
        if (notificationItem) {
          notificationItem.classList.remove("unread");
        }

        // Update unread count
        this.loadUnreadCount();
      }
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  }

  async markAllAsRead() {
    try {
      const response = await fetch("php_actions/notifications/mark_read.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          mark_all: true,
        }),
      });

      const data = await response.json();

      if (data.status === "success") {
        // Update UI
        const unreadItems = document.querySelectorAll(
          ".notification-item.unread",
        );
        unreadItems.forEach((item) => {
          item.classList.remove("unread");
        });

        // Update unread count
        this.updateUnreadCount(0);

        // Show success message
        this.showToast("All notifications marked as read", "success");
      }
    } catch (error) {
      console.error("Error marking all notifications as read:", error);
      this.showToast("Failed to mark notifications as read", "error");
    }
  }

  startPolling() {
    // Poll for new notifications every 30 seconds
    this.pollInterval = setInterval(() => {
      this.loadUnreadCount();
    }, 30000);
  }

  stopPolling() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
      this.pollInterval = null;
    }
  }

  showToast(message, type = "info") {
    // Remove existing toast
    const existingToast = document.querySelector(".notification-toast");
    if (existingToast) {
      existingToast.remove();
    }

    // Create new toast
    const toast = document.createElement("div");
    toast.className = `notification-toast ${type}`;
    toast.innerHTML = `
                <span class="toast-icon">${type === "success" ? "✅" : type === "error" ? "❌" : "ℹ️"}</span>
                <span class="toast-message">${this.escapeHtml(message)}</span>
            `;

    // Add toast styles
    const toastStyles = `
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

    // Add styles if not already added
    if (!document.querySelector("#toast-styles")) {
      const style = document.createElement("style");
      style.id = "toast-styles";
      style.textContent = toastStyles;
      document.head.appendChild(style);
    }

    document.body.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
      toast.style.animation = "slideInToast 0.3s ease reverse";
      setTimeout(() => {
        if (toast.parentNode) {
          toast.remove();
        }
      }, 300);
    }, 3000);
  }

  formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) {
      return "Just now";
    } else if (diffInSeconds < 3600) {
      const minutes = Math.floor(diffInSeconds / 60);
      return `${minutes} minute${minutes > 1 ? "s" : ""} ago`;
    } else if (diffInSeconds < 86400) {
      const hours = Math.floor(diffInSeconds / 3600);
      return `${hours} hour${hours > 1 ? "s" : ""} ago`;
    } else if (diffInSeconds < 604800) {
      const days = Math.floor(diffInSeconds / 86400);
      return `${days} day${days > 1 ? "s" : ""} ago`;
    } else {
      return date.toLocaleDateString();
    }
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Public method to manually refresh notifications
  refresh() {
    this.loadUnreadCount();
    if (this.isDropdownOpen) {
      this.loadNotifications();
    }
  }

  // Cleanup method
  destroy() {
    this.stopPolling();
    const notificationBell = document.getElementById("notification-bell");
    if (notificationBell) {
      notificationBell.remove();
    }
  }
}

// Initialize notification manager when DOM is loaded
let notificationManager;
document.addEventListener("DOMContentLoaded", () => {
  notificationManager = new NotificationManager();
  // Make it globally accessible
  window.notificationManager = notificationManager;
});

// Export for use in other scripts
window.NotificationManager = NotificationManager;
