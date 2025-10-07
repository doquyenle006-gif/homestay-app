<?php
// Include necessary files
include '../config/database.php';
include '../includes/functions.php';

// Start session
startSession();

// Store user info for message
$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';

// Destroy all session data
session_destroy();

// Set success message
session_start();
$_SESSION['success_message'] = "Đăng xuất thành công! Tạm biệt " . $user_name . "!";

// Redirect to login page
redirect('login.php');
?>