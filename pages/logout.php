<?php
session_start();

// Check if user is actually logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Store user info for logging (optional)
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Unknown';

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear any remember me cookies (if you have them)
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Optional: Log the logout activity
if ($user_id) {
    // You can add database logging here
    // Example: logUserActivity($user_id, 'logout', date('Y-m-d H:i:s'));
}

// Optional: Set a logout success message
session_start(); // Start new session for flash message
$_SESSION['logout_message'] = 'आप सफलतापूर्वक logout हो गए हैं।';

// Redirect to login page
header("Location: login.php");
exit();
?>