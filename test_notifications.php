<?php

// File: /home/larryck/Web Projects/lost_found/test_notifications.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>"; // For better formatting in browser

try {
    require_once __DIR__ . "/includes/notification_helpers.php";
    echo "✅ notification_helpers.php loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading notification_helpers.php: " . $e->getMessage() . "\n";
    exit;
}

echo "=== Testing Notification System ===\n\n";

// Test database connection first
try {
    global $conn;
    if (!$conn) {
        echo "❌ Database connection not available\n";
        exit;
    }
    echo "✅ Database connection available\n";
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "\n";
    exit;
}

// Test creating a notification
echo "\nTesting notification creation...\n";

// First, let's get actual IDs from the database
echo "Getting actual user and post IDs from database...\n";

try {
    // Get a real user ID
    $user_result = $conn->query("SELECT id, full_name FROM users LIMIT 1");
    if ($user_result && $user_row = $user_result->fetch_assoc()) {
        $test_user_id = $user_row['id'];
        echo "✅ Found user ID: {$test_user_id} (Name: {$user_row['full_name']})\n";
    } else {
        echo "❌ No users found in database\n";
        exit;
    }

    // Get a real post ID
    $post_result = $conn->query("SELECT post_id, description FROM posts LIMIT 1");
    if ($post_result && $post_row = $post_result->fetch_assoc()) {
        $test_post_id = $post_row['post_id'];
        echo "✅ Found post ID: {$test_post_id}\n";
    } else {
        echo "❌ No posts found in database\n";
        exit;
    }

    // Get an admin user ID
    $admin_result = $conn->query("SELECT id, full_name FROM users WHERE role = 'admin' LIMIT 1");
    if ($admin_result && $admin_row = $admin_result->fetch_assoc()) {
        $test_admin_id = $admin_row['id'];
        echo "✅ Found admin ID: {$test_admin_id} (Name: {$admin_row['full_name']})\n";
    } else {
        // If no admin found, use any user
        $test_admin_id = $test_user_id;
        echo "⚠️ No admin found, using regular user as admin for test\n";
    }

} catch (Exception $e) {
    echo "❌ Error getting test data: " . $e->getMessage() . "\n";
    exit;
}

echo "\n--- Testing Helper Functions ---\n";

// Test getPostTitle function
try {
    $post_title = getPostTitle($test_post_id);
    echo "✅ getPostTitle() works: '{$post_title}'\n";
} catch (Exception $e) {
    echo "❌ getPostTitle() error: " . $e->getMessage() . "\n";
}

// Test getNotificationTemplate function
try {
    $template = getNotificationTemplate('approved', $post_title);
    echo "✅ getNotificationTemplate() works\n";
    echo "   Title: {$template['title']}\n";
    echo "   Message: {$template['message']}\n";
} catch (Exception $e) {
    echo "❌ getNotificationTemplate() error: " . $e->getMessage() . "\n";
}

// Test userExists function
try {
    $exists = userExists($test_user_id);
    echo "✅ userExists() works: " . ($exists ? "User exists" : "User not found") . "\n";
} catch (Exception $e) {
    echo "❌ userExists() error: " . $e->getMessage() . "\n";
}

echo "\n--- Testing Notification Creation ---\n";

// Test approval notification
try {
    $post_title = getPostTitle($test_post_id);
    $template = getNotificationTemplate('approved', $post_title);

    if (createNotification($test_user_id, $test_post_id, 'approved', $template['title'], $template['message'], null, $test_admin_id)) {
        echo "✅ Approval notification created successfully\n";
    } else {
        echo "❌ Failed to create approval notification\n";
    }
} catch (Exception $e) {
    echo "❌ Error creating approval notification: " . $e->getMessage() . "\n";
}

// Test rejection notification with reason
try {
    $template = getNotificationTemplate('rejected', $post_title, 'Content violates community guidelines');

    if (createNotification($test_user_id, $test_post_id, 'rejected', $template['title'], $template['message'], 'Content violates community guidelines', $test_admin_id)) {
        echo "✅ Rejection notification created successfully\n";
    } else {
        echo "❌ Failed to create rejection notification\n";
    }
} catch (Exception $e) {
    echo "❌ Error creating rejection notification: " . $e->getMessage() . "\n";
}

// Test getting notifications
echo "\n--- Testing Notification Retrieval ---\n";
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $test_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $notification_count = $row['count'];
    $stmt->close();

    echo "✅ User now has {$notification_count} notifications\n";

    // Show recent notifications
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param("i", $test_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "\nRecent notifications:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['type']}: {$row['title']}\n";
        echo "  Message: {$row['message']}\n";
        echo "  Created: {$row['created_at']}\n\n";
    }
    $stmt->close();

} catch (Exception $e) {
    echo "❌ Error retrieving notifications: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Check your notifications table to see all created notifications.\n";

echo "</pre>";
