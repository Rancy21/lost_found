<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}


$user_role = $_SESSION['user_role'];

if ($user_role !== 'admin') {
    header('Location: main.php');
    exit;
} else {

    header('Location: admin_dashboard.php');
    exit;

}
