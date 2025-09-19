
<?php
session_start();
require_once __DIR__ . "/config/config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    die('Please log in first');
}

echo "<h2>Detailed Notification System Debug</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test:</h3>";
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
    exit;
}

// Test 2: Check notifications table
echo "<h3>2. Notifications Table Check:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($result && $result->num_rows > 0) {
    echo "✅ Notifications table exists<br>";

    // Show table structure
    $structure = $conn->query("DESCRIBE notifications");
    echo "<table border='1' style='margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Notifications table does not exist<br>";
    exit;
}

// Test 3: Check posts table
echo "<h3>3. Posts Table Check:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'posts'");
if ($result && $result->num_rows > 0) {
    echo "✅ Posts table exists<br>";

    // Show table structure
    $structure = $conn->query("DESCRIBE posts");
    echo "<table border='1' style='margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Posts table does not exist<br>";
}

// Test 4: Test the exact query from get_notifications.php
echo "<h3>4. Testing get_notifications.php Query:</h3>";
$user_id = $_SESSION['user_id'];
$page = 1;
$limit = 10;
$offset = 0;

try {
    // Count query
    $count_sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
    echo "Count Query: " . $count_sql . "<br>";

    $count_stmt = $conn->prepare($count_sql);
    if (!$count_stmt) {
        echo "❌ Failed to prepare count query: " . $conn->error . "<br>";
    } else {
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_count = $count_result->fetch_assoc()['total'];
        echo "✅ Count query successful. Total notifications: " . $total_count . "<br>";
        $count_stmt->close();
    }

    // Main query
    $sql = "SELECT notification_id, type, title, message, reason, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
    echo "Main Query: " . $sql . "<br>";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "❌ Failed to prepare main query: " . $conn->error . "<br>";
    } else {
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "✅ Main query successful. Rows returned: " . $result->num_rows . "<br>";

        if ($result->num_rows > 0) {
            echo "<h4>Sample notifications:</h4>";
            echo "<table border='1' style='margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Type</th><th>Title</th><th>Message</th><th>Is Read</th><th>Created At</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['notification_id']}</td>";
                echo "<td>{$row['type']}</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['message']) . "</td>";
                echo "<td>" . ($row['is_read'] ? 'Yes' : 'No') . "</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        $stmt->close();
    }

} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "<br>";
}

// Test 5: Test the actual API endpoint
echo "<h3>5. Testing API Endpoint:</h3>";
echo "<button onclick='testAPI()'>Test get_notifications.php API</button>";
echo "<div id='api-result' style='margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 5px;'></div>";

echo "<script>
async function testAPI() {
    const resultDiv = document.getElementById('api-result');
    resultDiv.innerHTML = 'Testing...';
    
    try {
        const response = await fetch('php_actions/notifications/get_notifications.php?page=1&type=all&status=all');
        const data = await response.text();
        
        resultDiv.innerHTML = '<h4>API Response:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>';
    } catch (error) {
        resultDiv.innerHTML = '<h4>API Error:</h4><pre>' + error.message + '</pre>';
    }
}
</script>";

// Test 6: Check if notification_helpers.php is working
echo "<h3>6. Testing Notification Helpers:</h3>";
if (file_exists(__DIR__ . "/includes/notification_helpers.php")) {
    echo "✅ notification_helpers.php file exists<br>";

    require_once __DIR__ . "/includes/notification_helpers.php";

    // Test getPostTitle function if there are posts
    $post_check = $conn->query("SELECT post_id FROM posts LIMIT 1");
    if ($post_check && $post_check->num_rows > 0) {
        $post_row = $post_check->fetch_assoc();
        $test_post_id = $post_row['post_id'];

        echo "Testing getPostTitle with post_id: " . $test_post_id . "<br>";
        $title = getPostTitle($test_post_id);
        echo "Result: " . htmlspecialchars($title) . "<br>";
    } else {
        echo "No posts found to test getPostTitle<br>";
    }
} else {
    echo "❌ notification_helpers.php file not found<br>";
}

echo "<h3>7. Browser Console Check:</h3>";
echo "<p>Open your browser's developer console (F12) and check for any JavaScript errors when loading the notifications page.</p>";
?>