<?php
// dashboard.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect users based on their role
switch ($_SESSION['role']) {
    case 'hospital':
        header("Location: hospital_portal.php");
        break;
    case 'doctor':
        header("Location: doctor_portal.php");
        break;
    case 'patient':
        header("Location: patient_portal.php");
        break;
    case 'lab':
        header("Location: lab_portal.php");
        break;
    case 'pharmacy':
        header("Location: pharmacy_portal.php");
        break;
    case 'billing':
        header("Location: billing_portal.php");
        break;
    default:
        echo "Unauthorized access.";
        session_destroy();
        exit();
}
?>
