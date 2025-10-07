# 🔍 Lost & Found Community Platform

A comprehensive web-based platform designed to help communities reunite lost items with their owners. Built with PHP, MySQL, and modern JavaScript, this application provides a complete solution for managing lost and found items with real-time messaging, notifications, and administrative oversight.

## ✨ Key Features

### 🏠 **User Features**
- **Account Management**: Secure user registration and login with encrypted passwords
- **Password Recovery**: Forgot password functionality with email-based reset links
- **Profile Management**: View your profile and update full name and password (email cannot be changed)
- **Post Creation**: Create detailed posts for lost or found items with images and location data
- **Search & Filter**: Advanced search functionality with type and status filters
- **Real-time Messaging**: Direct communication between users about specific items
- **Notifications**: Automated notifications for post status updates (approved, rejected, etc.)
- **Post Management**: View, edit, resolve, or delete your own posts
- **Responsive Design**: Fully responsive interface that works on desktop and mobile

### 👑 **Admin Features**
- **Admin Dashboard**: Comprehensive overview with statistics and metrics
- **Post Moderation**: Review and moderate pending posts with approval/rejection workflow
- **User Management**: Manage user accounts, roles, and status (active/banned)
  - Ban users with reason modal and automated email notification
  - Unban users with email confirmation
  - Promote/demote users with role change notifications
- **Email Notification System**: Automated email notifications for all user management actions
- **Notification System**: Send automated notifications to users about post status changes
- **Advanced Analytics**: Track platform usage, post resolution rates, and user activity

### 💬 **Communication System**
- **Internal Messaging**: Built-in messaging system for user-to-user communication
- **Conversation Threading**: Organized message threads grouped by posts
- **Read/Unread Status**: Message read tracking with unread counters
- **Contact Integration**: Easy contact buttons on posts for direct communication

### 🔔 **Notification System**
- **In-App Notifications**: Live notification bell with dropdown preview
- **Comprehensive Dashboard**: Full notifications page with filtering and pagination
- **Metrics Display**: Visual metrics showing notification statistics
- **Email-style Organization**: Inbox/sent message organization
- **Email Notifications**: SMTP-based email system with HTML templates
  - Password reset emails with secure time-limited tokens
  - Account ban notifications with detailed reasons
  - Account unban confirmations with login links
  - Role change notifications (admin promotion/demotion)

## 🛠 Technology Stack

- **Backend**: PHP 8.0+ with MySQL/MariaDB
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Authentication**: Secure password hashing with PHP's password_hash()
- **Email System**: PHPMailer with SMTP support (Gmail, etc.)
- **Database**: MySQL with prepared statements for security
- **File Uploads**: Image upload support with validation
- **Responsive Design**: CSS Grid and Flexbox for modern layouts

## 📋 Prerequisites

- Web server (Apache/Nginx) with PHP 8.0+
- MySQL 8.0+ or MariaDB 10.3+
- PHP extensions: mysqli, gd, fileinfo
- Composer (for PHPMailer installation)
- SMTP server access (e.g., Gmail, SendGrid) for email features
- Modern web browser

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Rancy21/lost_found.git
   cd lost_found
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up the database**
   - Create a MySQL database for the application
   - Import the database schema (create tables for users, posts, messages, notifications, password_resets)
   - Update database configuration in `config/config.php`

4. **Configure the application**
   ```php
   // config/config.php
   $servername = "localhost";
   $username = "your_db_username";
   $password = "your_db_password";
   $dbname = "lost_found_db";
   ```

5. **Configure email settings**
   ```php
   // includes/email_helper.php
   define('SMTP_ENABLED', true);
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your_email@gmail.com');
   define('SMTP_PASSWORD', 'your_app_password');
   define('SMTP_ENCRYPTION', 'tls');
   define('FROM_EMAIL', 'noreply@lostandfound.com');
   define('FROM_NAME', 'Lost & Found');
   ```

6. **Set up file permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 *.php
   ```

7. **Create admin account**
   - Register a regular user account
   - Manually update the user's role to 'admin' in the database
   - Or use the provided SQL commands

## 📊 Database Schema

### Core Tables
- **users**: User accounts with roles and status (admin/user, active/banned)
- **posts**: Lost and found item posts with images and locations
- **messages**: Internal messaging system
- **notifications**: User notification system
- **password_resets**: Secure password reset tokens with expiration

### Key Features
- Prepared statements for SQL injection protection
- Password encryption using PHP's password_hash()
- Foreign key relationships for data integrity
- Indexed columns for optimal performance

## 🎯 Usage Guide

### For Regular Users
1. **Register/Login**: Create an account or sign in
2. **Browse Items**: View all lost and found posts on the main feed
3. **Post Items**: Create posts for items you've lost or found
4. **Contact Others**: Use the messaging system to communicate about items
5. **Track Status**: Monitor your post status and receive notifications
6. **Manage Profile**: Update your name and password from your Profile page (email is read-only)

### For Administrators
1. **Access Admin Panel**: Login and navigate to the admin dashboard
2. **Moderate Posts**: Review pending posts for approval/rejection
3. **Manage Users**: Handle user accounts, roles, and bans
4. **Monitor Activity**: Track platform metrics and user engagement
5. **Send Notifications**: Automated notification system keeps users informed

## 🔐 Security Features

- **Password Security**: Bcrypt hashing for all passwords
- **Password Reset Security**: Time-limited tokens (1 hour expiration) with secure random generation
- **SQL Injection Protection**: Prepared statements throughout
- **XSS Prevention**: Input sanitization and output escaping
- **Session Management**: Secure session handling
- **Role-Based Access**: Admin/user role separation with middleware protection
- **Immutable Email**: Users cannot change their registered email address
- **File Upload Security**: Image validation and secure storage
- **Email Privacy**: Password reset doesn't reveal if email exists (security best practice)

## 📱 Responsive Design

The platform is fully responsive and optimized for:
- **Desktop**: Full-featured interface with all functionality
- **Tablet**: Touch-optimized layouts with adapted navigation
- **Mobile**: Simplified interface with essential features accessible

## 🚧 Future Enhancements

- [x] Email notification integration
- [x] Password reset functionality
- [x] User ban/unban system with email notifications
- [ ] Advanced search with AI-powered matching
- [ ] Mobile app development
- [ ] Integration with social media platforms
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] API for third-party integrations
- [ ] Two-factor authentication (2FA)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 👥 Support

For support, bug reports, or feature requests:
- Open an issue on GitHub
- Contact the development team
- Check the documentation for common solutions

## 🙏 Acknowledgments

- Built with modern web development best practices
- Inspired by community-driven platforms
- Designed with accessibility and user experience in mind

---

**Made with ❤️ for communities worldwide**
