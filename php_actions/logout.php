<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: ../login.php?success=You have been logged out successfully');
exit;
?>