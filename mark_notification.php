<?php
// mark_notification.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Include database connection

// Mark all notifications as read for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$user_id]);

header("Location: " . $_SERVER['HTTP_REFERER']); // Redirect back to the previous page
exit();
