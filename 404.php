<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <link rel="stylesheet" href="dashboard.css"> <!-- Include CSS file -->
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for does not exist.</p>
    <p><a href="index.php">Go to Home</a></p>
</body>
</html>
<?php
// error_handler.php

function customError($errno, $errstr, $errfile, $errline) {
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] Error: $errstr in $errfile on line $errline\n";
    error_log($errorMessage, 3, "errors.log"); // Log the error to a file named errors.log
}

set_error_handler("customError");
// Include this at the top of your main PHP files
include 'error_handler.php';
?>
