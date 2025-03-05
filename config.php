<?php
// config.php

$host = 'localhost';
$dbname = 'healthcare_system';
$user = 'root';
$pass = 'pRATIK@16?.'; // Replace with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
