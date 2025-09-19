<?php
session_start();

// heck if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please log in to view your messages');
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
    <title>Messages - Lost & Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style/messages.css">
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <span class="logo">🔍</span>
            <h1 class="header-title">Lost & Found Messages</h1>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($user_name); ?></span>
        </div>
    </header>

    <div class="main-content">
        <aside class="sidebar">
            <!-- <button class="compose-btn">+ Compose</button> -->
            <ul class="folder-list">
                <li class="folder-item active">Inbox <span id="unread-count"></span></li>
                <li class="folder-item">Sent</li>
                <!-- <li class="folder-item">Drafts</li> -->
            </ul>
        </aside>

        <div class="message-list" id="messageList">
            <!-- Message items will be populated here -->
        </div>

        <div class="message-content" id="messageContent">
            <!-- Selected message content will be displayed here -->
        </div>
    </div>

    <script>
    let currentUser = <?php echo json_encode($_SESSION['user_id']); ?>;
    let conversations = [];
    let currentView = 'inbox';
    let lastCheckTimestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');
    
    document.addEventListener('DOMContentLoaded', function() {
        // Check for contact parameters in URL
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        const postId = urlParams.get('post_id');
        const ownerId = urlParams.get('owner_id');
        const postTitle = urlParams.get('post_title');
        
        if (action === 'contact' && postId && ownerId && postTitle) {
            handleContactRedirect(postId, ownerId, postTitle);
        } else {
            loadInbox();
        }
        
        setInterval(pollForUpdates, 5000);
    
        document.querySelector('.compose-btn').addEventListener('click', showComposeForm);

        // Add event listeners for Sent folder
        document.querySelectorAll('.folder-item').forEach(item => {
            item.addEventListener('click', (e) => {
                document.querySelector('.folder-item.active').classList.remove('active');
                e.currentTarget.classList.add('active');

                const folder = e.currentTarget.textContent.trim().split(' ')[0];
                if (folder === 'Inbox') {
                    loadInbox();
                } else if (folder === 'Sent') {
                    loadSentMessages();
                }
                // Clear the message content when switching folders
                document.getElementById('messageContent').innerHTML = '';
            });
        });
    });

    function pollForUpdates() {
        fetch(`php_actions/messages/check_updates.php?last_check=${encodeURIComponent(lastCheckTimestamp)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    lastCheckTimestamp = data.timestamp; // Update timestamp for the next poll

                    // Update unread count in the sidebar
                    const unreadCountElement = document.getElementById('unread-count');
                    if (data.unread_count > 0) {
                        unreadCountElement.textContent = data.unread_count;
                        unreadCountElement.style.display = 'inline';
                    } else {
                        unreadCountElement.style.display = 'none';
                    }

                    // If there are new messages and we are in the inbox view, refresh the list
                    if (data.has_new_messages && currentView === 'inbox') {
                        loadInbox();
                    }
                }
            })
            .catch(error => console.error('Error polling for updates:', error));
    }
    
    function loadInbox() {
        currentView = 'inbox';
        fetch('php_actions/messages/get_messages_inbox.php')
            .then(response => response.json())
            .then(data => {
                console.log("Inbox data:", data);
                if (data.status === 'success') {
                    conversations = data.conversations;
                    displayConversationList();
                    updateUnreadCount();
                } else {
                    console.error('Error loading conversations:', data.message);
                    showNotification('Error loading inbox', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading inbox', 'error');
            });
    }

    function loadSentMessages() {
        currentView = 'sent';
        fetch('php_actions/messages/get_messages_sent.php')
            .then(response => response.json())
            .then(data => {
                console.log("Sent messages data:", data);
                if (data.status === 'success') {
                    conversations = data.conversations;
                    displayConversationList();
                } else {
                    console.error('Error loading sent conversations:', data.message);
                    showNotification('Error loading sent messages', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading sent messages', 'error');
            });
    }

    function displayConversationList() {
        const messageList = document.getElementById('messageList');
        messageList.innerHTML = '';
    
        if (conversations.length === 0) {
            messageList.innerHTML = `
                <div class="no-messages">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <p style="margin-top: 1rem;">No messages found</p>
                    </div>
                </div>`;
            return;
        }
    
        conversations.forEach(conversation => {
            const conversationItem = document.createElement('div');
            conversationItem.className = 'message-item';
            if (currentView === 'inbox' && conversation.unread_count > 0) {
                conversationItem.classList.add('unread');
            }
            conversationItem.innerHTML = `
                <div class="message-sender">${conversation.other_user_name}</div>
                <div class="message-subject">${conversation.subject}</div>
                <div class="message-preview">${conversation.last_message}</div>
                <div class="message-timestamp">${formatDate(conversation.last_message_time)}</div>
                ${currentView === 'inbox' && conversation.unread_count > 0 ? `<div class="unread-count">${conversation.unread_count}</div>` : ''}
            `;
            conversationItem.addEventListener('click', () => {
                console.log('Conversation item clicked:', conversation.post_id, conversation.other_user_id);
                if (currentView === 'inbox' && conversation.unread_count > 0) {
                    markConversationAsRead(conversation.post_id, conversation.other_user_id);
                    // Optimistically update UI
                    conversation.unread_count = 0;
                    displayConversationList();
                }
                loadConversation(conversation.post_id, conversation.other_user_id);
            });
            messageList.appendChild(conversationItem);
        });
    }


function loadConversation(postId, otherUserId) {
    console.log('Loading conversation:', postId, otherUserId);
    fetch(`php_actions/messages/get_conversations.php?post_id=${postId}&other_user_id=${otherUserId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Conversation data:', data);
            if (data.status === 'success') {
                console.log('Displaying conversation for postId:', postId, 'otherUserId:', otherUserId);
                displayConversation(data.messages, postId, otherUserId);
            } else {
                console.error('Error loading conversation:', data.message);
                showNotification('Error loading conversation', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching conversation:', error);
            showNotification('Error loading conversation', 'error');
        });
}

// Replace the existing displayConversation function with this enhanced version

function displayConversation(messages, postId, otherUserId) {
    console.log('Displaying conversation:', messages, postId, otherUserId);
    const messageContent = document.getElementById('messageContent');
    
    // Fetch user info and post details
    Promise.all([
        fetch(`php_actions/get_user_info.php?user_id=${otherUserId}`)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            }),
        fetch(`php_actions/get_post_details.php?post_id=${postId}`)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
    ]).then(([userInfo, postDetails]) => {
        console.log('User info:', userInfo);
        console.log('Post details:', postDetails);
        
        if (!userInfo || !postDetails) {
            throw new Error('Failed to fetch user info or post details');
        }

        messageContent.innerHTML = `
            <div class="conversation-header">
                <div class="conversation-user-info">
                    <div class="user-avatar">${userInfo.name.charAt(0).toUpperCase()}</div>
                    <div class="user-details">
                        <h2>${userInfo.name}</h2>
                        <span>${userInfo.email}</span>
                    </div>
                </div>
                <div class="conversation-post-info" id="post-info-dropdown" data-type="${postDetails.type}">
                    <div class="post-info-header">
                        <div>
                            <h3>${escapeHtml(postDetails.title)}</h3>
                            <span>${postDetails.type.charAt(0).toUpperCase() + postDetails.type.slice(1)} Item • ${formatDate(postDetails.date_posted)}</span>
                        </div>
                        <span class="toggle-arrow">▼</span>
                    </div>
                    <div class="post-info-details">
                        <p><strong>Description:</strong> <span>${escapeHtml(postDetails.description || 'No description provided')}</span></p>
                        <p><strong>Location:</strong> <span>${escapeHtml(postDetails.location_name || 'Location not specified')}</span></p>
                        ${postDetails.image_url ? `<p><strong>Image:</strong></p><img src="${postDetails.image_url}" alt="Post image" class="post-info-image">` : '<p><strong>Image:</strong> <span>No image provided</span></p>'}
                    </div>
                </div>
            </div>
            <div class="conversation-messages">
                ${messages.map(message => `
                    <div class="conversation-message ${message.sender_id === currentUser ? 'sent' : 'received'}">
                        <div class="message-meta">
                            <span>${message.sender_id === currentUser ? 'You' : message.sender_name}</span>
                            <span>${formatDate(message.timestamp)}</span>
                        </div>
                        <div class="message-body">${escapeHtml(message.content)}</div>
                    </div>
                `).join('')}
            </div>
            <div class="reply-form">
                <textarea class="reply-input" placeholder="Type your reply..."></textarea>
                <button class="reply-btn" onclick="sendReply(${postId}, ${otherUserId})">Reply</button>
            </div>
        `;

        // Add event listener for the enhanced dropdown
        const postInfoDropdown = document.getElementById('post-info-dropdown');
        if (postInfoDropdown) {
            postInfoDropdown.addEventListener('click', () => {
                const details = postInfoDropdown.querySelector('.post-info-details');
                const isOpen = postInfoDropdown.classList.contains('open');
                
                postInfoDropdown.classList.toggle('open');
                
                if (!isOpen) {
                    details.style.display = 'block';
                    // Trigger reflow to ensure animation works
                    details.offsetHeight;
                } else {
                    details.style.display = 'none';
                }
            });
        }

        // Scroll to bottom of messages
        const messagesContainer = document.querySelector('.conversation-messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

    }).catch(error => {
        console.error('Error fetching conversation details:', error);
        showNotification('Error loading conversation details', 'error');
        messageContent.innerHTML = '<p>Error loading conversation. Please try again.</p>';
    });
}

function sendReply(postId, otherUserId) {
    const content = document.querySelector('.reply-input').value.trim();
    if (content) {
        sendMessage(otherUserId, content, postId);
    }
}

function sendMessage(recipientId, content, postId) {
    fetch('php_actions/messages/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `receiver_id=${recipientId}&message=${encodeURIComponent(content)}&post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('Message sent successfully', 'success');
            loadConversation(postId, recipientId);
        } else {
            showNotification('Error sending message: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while sending the message', 'error');
    });
}
        
        function showComposeForm() {
            const messageContent = document.getElementById('messageContent');
            messageContent.innerHTML = `
                <div class="message-header">
                    <h2 class="message-title">New Message</h2>
                </div>
                <div class="compose-form">
                    <input type="text" id="recipient" placeholder="Recipient" required>
                    <input type="text" id="subject" placeholder="Subject" required>
                    <textarea id="content" placeholder="Type your message..." required></textarea>
                    <button onclick="sendNewMessage()">Send</button>
                </div>
            `;
        }
        
        function sendNewMessage() {
            const recipient = document.getElementById('recipient').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const content = document.getElementById('content').value.trim();
            if (recipient && subject && content) {
                // You'll need to implement a way to get the recipient's user ID from their name or email
                // For now, we'll assume you have a function getRecipientId() that does this
                // const recipientId = getRecipientId(recipient);
                // sendMessage(recipientId, content, subject);
                showNotification('Compose functionality is not yet implemented.', 'error');
            }
        }
        
        function viewConversation(otherUserId) {
            fetch(`php_actions/messages/get_conversations.php?other_user_id=${otherUserId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayConversation(data.messages);
                    } else {
                        console.error('Error loading conversation:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        function updateUnreadCount() {
            fetch('php_actions/messages/get_unread_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const unreadCount = data.count;
                        document.getElementById('unread-count').textContent = unreadCount;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        function markMessageAsRead(messageId) {
            fetch('php_actions/messages/mark_message_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message_id=${messageId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateUnreadCount();
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function markConversationAsRead(postId, otherUserId) {
            fetch('php_actions/messages/set_conversation_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&other_user_id=${otherUserId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Conversation marked as read.');
                    updateUnreadCount(); // Update the total unread count in the sidebar
                } else {
                    console.error('Failed to mark conversation as read:', data.message);
                }
            })
            .catch(error => console.error('Error marking conversation as read:', error));
         }
        
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString('en-US', options);
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
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Event listeners for folder items
         document.querySelectorAll('.folder-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.folder-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            if (this.textContent.includes('Inbox')) {
                loadInbox();
            } else if (this.textContent === 'Sent') {
                loadSentMessages();
            }
        });
    });
    
    function handleContactRedirect(postId, ownerId, postTitle) {
        // First check if there's an existing conversation
        fetch(`php_actions/messages/check_existing_conversation.php?post_id=${postId}&other_user_id=${ownerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.has_conversation) {
                    // Load existing conversation
                    loadConversation(postId, ownerId);
                    // Update sidebar to show inbox as active
                    document.querySelector('.folder-item.active').classList.remove('active');
                    document.querySelector('.folder-item').classList.add('active');
                    loadInbox();
                } else {
                    // Show compose form for new conversation
                    showComposeFormForPost(postId, ownerId, postTitle);
                }
            })
            .catch(error => {
                console.error('Error checking existing conversation:', error);
                // Fallback to compose form
                showComposeFormForPost(postId, ownerId, postTitle);
            });
    }

    function showComposeFormForPost(postId, ownerId, postTitle) {
        // Fetch post owner info and post details
        Promise.all([
            fetch(`php_actions/get_user_info.php?user_id=${ownerId}`),
            fetch(`php_actions/get_post_details.php?post_id=${postId}`)
        ]).then(([userResponse, postResponse]) => {
            return Promise.all([userResponse.json(), postResponse.json()]);
        }).then(([userInfo, postDetails]) => {
            const messageContent = document.getElementById('messageContent');
            messageContent.innerHTML = `
                <div class="message-header">
                    <h2 class="message-title">Contact About: ${escapeHtml(postTitle)}</h2>
                </div>
                <div class="post-context-info">
                    <div class="post-context-header">
                        <h3>Post Details</h3>
                    </div>
                    <div class="post-context-content">
                        <p><strong>Title:</strong> ${escapeHtml(postDetails.title)}</p>
                        <p><strong>Type:</strong> ${postDetails.type.charAt(0).toUpperCase() + postDetails.type.slice(1)} Item</p>
                        <p><strong>Posted by:</strong> ${escapeHtml(userInfo.name)}</p>
                        <p><strong>Date:</strong> ${formatDate(postDetails.date_posted)}</p>
                        ${postDetails.image_url ? `<img src="${postDetails.image_url}" alt="Post image" class="post-context-image">` : ''}
                    </div>
                </div>
                <div class="compose-form">
                    <div class="form-group">
                        <label for="recipient">To:</label>
                        <input type="text" id="recipient" value="${escapeHtml(userInfo.name)}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" id="subject" value="Re: ${escapeHtml(postTitle)}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="content">Message:</label>
                        <textarea id="content" placeholder="Hi! I saw your post about '${escapeHtml(postTitle)}'. I'd like to get in touch..." required></textarea>
                    </div>
                    <div class="form-actions">
                        <button class="btn-primary" onclick="sendPostMessage(${postId}, ${ownerId})">Send Message</button>
                        <button class="btn-secondary" onclick="loadInbox()">Cancel</button>
                    </div>
                </div>
            `;
        }).catch(error => {
            console.error('Error loading post context:', error);
            showNotification('Error loading post information', 'error');
        });
    }

    function sendPostMessage(postId, recipientId) {
        const content = document.getElementById('content').value.trim();
        if (!content) {
            showNotification('Please enter a message', 'error');
            return;
        }
        
        fetch('php_actions/messages/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `receiver_id=${recipientId}&message=${encodeURIComponent(content)}&post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Message sent successfully!', 'success');
                // Load the new conversation
                setTimeout(() => {
                    loadConversation(postId, recipientId);
                    loadInbox(); // Refresh inbox
                }, 1000);
            } else {
                showNotification('Error sending message: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while sending the message', 'error');
        });
    }
     </script>
</body>
</html>
