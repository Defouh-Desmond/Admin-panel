<?php
session_start();
require_once '../classes/connection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Update user status to offline
    $stmt = $mysqli->prepare("UPDATE users SET status = 'offline' WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Clear all session data
$_SESSION = [];
session_destroy();

// Optional: prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Redirect to login page
header("Location: login.php");
exit;
