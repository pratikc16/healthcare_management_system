<?php
// send_notification.php

function sendNotification($pdo, $user_id, $message, $type) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $message, $type]);
}
?>
