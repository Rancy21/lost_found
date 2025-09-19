<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to test notifications');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Notification UI - Lost & Found</title>
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
            padding: 2rem;
        }

        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .test-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .test-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .test-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        .test-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
        }

        .test-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }

        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .mock-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .mock-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3c72;
        }

        .user-profile {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .status-display {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1 class="test-title">🔔 Notification System Test</h1>
            <p class="test-subtitle">Test the notification UI components and functionality</p>
        </div>

        <!-- Mock Header with Notification Bell -->
        <div class="test-section">
            <h3 class="section-title">Notification Bell Display</h3>
            <div class="mock-header">
                <div class="mock-logo">🔍 Lost & Found</div>
                <!-- Notification bell will be inserted here -->
                <div class="user-profile">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Test Controls -->
        <div class="test-section">
            <h3 class="section-title">Test Controls</h3>
            <button class="test-btn" onclick="testRefresh()">
                🔄 Refresh Notifications
            </button>
            <button class="test-btn" onclick="testSuccessToast()">
                ✅ Show Success Toast
            </button>
            <button class="test-btn" onclick="testErrorToast()">
                ❌ Show Error Toast
            </button>
            <button class="test-btn" onclick="testInfoToast()">
                ℹ️ Show Info Toast
            </button>
            <button class="test-btn" onclick="testToggleDropdown()">
                📋 Toggle Dropdown
            </button>
            <button class="test-btn" onclick="window.open('notifications.php', '_blank')">
                📄 Open Full Notifications Page
            </button>
        </div>

        <!-- Status Display -->
        <div class="test-section">
            <h3 class="section-title">Status</h3>
            <div id="status-display" class="status-display">
                Initializing notification system...
            </div>
        </div>

        <!-- Navigation -->
        <div class="test-section">
            <h3 class="section-title">Navigation</h3>
            <a href="main.php" class="test-btn">🏠 Back to Home</a>
            <a href="post_view.php" class="test-btn">📝 My Posts</a>
            <a href="notifications.php" class="test-btn">🔔 Full Notifications</a>
        </div>
    </div>

    <!-- Include notification JavaScript -->
    <script src="assets/js/notifications.js"></script>
    <script>
        // Wait for notification manager to be ready
        function waitForNotificationManager(callback, maxAttempts = 10) {
            let attempts = 0;
            const checkInterval = setInterval(() => {
                attempts++;
                if (window.notificationManager) {
                    clearInterval(checkInterval);
                    callback();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    updateStatus('❌ Notification manager failed to initialize after ' + maxAttempts + ' attempts');
                }
            }, 100);
        }

        // Test functions
        function testRefresh() {
            waitForNotificationManager(() => {
                window.notificationManager.refresh();
                updateStatus('✅ Refresh triggered');
            });
        }

        function testSuccessToast() {
            waitForNotificationManager(() => {
                window.notificationManager.showToast('Test success notification! 🎉', 'success');
                updateStatus('✅ Success toast triggered');
            });
        }

        function testErrorToast() {
            waitForNotificationManager(() => {
                window.notificationManager.showToast('Test error notification! ⚠️', 'error');
                updateStatus('✅ Error toast triggered');
            });
        }

        function testInfoToast() {
            waitForNotificationManager(() => {
                window.notificationManager.showToast('Test info notification! 📢', 'info');
                updateStatus('✅ Info toast triggered');
            });
        }

        function testToggleDropdown() {
            waitForNotificationManager(() => {
                window.notificationManager.toggleDropdown();
                updateStatus('✅ Dropdown toggle triggered');
            });
        }

        function updateStatus(message) {
            const statusDisplay = document.getElementById('status-display');
            const timestamp = new Date().toLocaleTimeString();
            statusDisplay.innerHTML += `\n[${timestamp}] ${message}`;
            statusDisplay.scrollTop = statusDisplay.scrollHeight;
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            updateStatus('🔄 DOM loaded, waiting for notification manager...');
            
            // Check if notification manager is ready
            waitForNotificationManager(() => {
                updateStatus('✅ Notification manager initialized successfully');
                updateStatus('🔔 Notification bell should be visible in the header above');
                
                // Test loading unread count
                setTimeout(() => {
                    window.notificationManager.loadUnreadCount();
                    updateStatus('📊 Loaded unread count');
                }, 1000);
            });
        });

        // Override console.log to show in status display
        const originalLog = console.log;
        console.log = function(...args) {
            originalLog.apply(console, args);
            updateStatus('Console: ' + args.join(' '));
        };

        // Override console.error to show in status display
        const originalError = console.error;
        console.error = function(...args) {
            originalError.apply(console, args);
            updateStatus('❌ Error: ' + args.join(' '));
        };

        // Override console.warn to show in status display
        const originalWarn = console.warn;
        console.warn = function(...args) {
            originalWarn.apply(console, args);
            updateStatus('⚠️ Warning: ' + args.join(' '));
        };
    </script>
</body>
</html>