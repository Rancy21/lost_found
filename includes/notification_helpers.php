<?php

require_once __DIR__ . "/../config/config.php";

/**
 * Create a new notification
 */
function createNotification($user_id, $post_id, $type, $title, $message, $reason = null, $admin_id = null)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, post_id, type, title, message, reason, admin_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iissssi", $user_id, $post_id, $type, $title, $message, $reason, $admin_id);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

/**
 * Get notification message template based on type
 */
function getNotificationTemplate($type, $post_title = '', $reason = '')
{
    $templates = [
        'approved' => [
            'title' => 'Post Approved! 🎉',
            'message' => "Great news! Your post '{$post_title}' has been approved and is now visible to the community."
        ],
        'rejected' => [
            'title' => 'Post Rejected ❌',
            'message' => "Your post '{$post_title}' has been rejected." . ($reason ? " Reason: {$reason}" : "")
        ],
        'deleted' => [
            'title' => 'Post Deleted 🗑️',
            'message' => "Your post '{$post_title}' has been deleted." . ($reason ? " Reason: {$reason}" : "")
        ],
        'pending' => [
            'title' => 'Post Under Review ⏳',
            'message' => "Your post '{$post_title}' is now under review. We'll notify you once it's processed."
        ]
    ];

    return $templates[$type] ?? [
        'title' => 'Post Status Update',
        'message' => "Your post '{$post_title}' status has been updated."
    ];
}

/**
 * Get post owner ID
 */
function getPostOwner($post_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['user_id'];
    }

    $stmt->close();
    return null;
}

/**
 * Get post title/description for notification
 * Fixed to use correct column name
 */
function getPostTitle($post_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT description FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        // Return first 50 characters of description as title
        return substr($row['description'], 0, 50) . (strlen($row['description']) > 50 ? '...' : '');
    }

    $stmt->close();
    return 'Unknown Post';
}

/**
 * Check if user exists
 */
function userExists($user_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $exists = $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

/**
 * Get user notifications
 */
function getUserNotifications($user_id, $limit = 10)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT n.*, p.description as post_description 
        FROM notifications n 
        LEFT JOIN posts p ON n.post_id = p.id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC 
        LIMIT ?
    ");

    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    $stmt->close();
    return $notifications;
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notification_id, $user_id)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}
