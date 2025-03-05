<?php
// book_appointment.php

session_start();
if ($_SESSION['role'] != 'patient') {
    header("Location: unauthorized.php");
    exit();
}

include 'config.php'; // Database connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form values
    $doctor_id = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : null;

    // Error handling: check if required fields are present
    if ($doctor_id === null || $appointment_date === null) {
        echo "<script>alert('Please select a doctor and choose an appointment date.'); window.location.href='patient_portal.php';</script>";
        exit();
    }

    // Fetch patient_id from the patients table using user_id from the session
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $patient = $stmt->fetch();
    if (!$patient) {
        echo "<script>alert('No patient found for this user.'); window.location.href='patient_portal.php';</script>";
        exit();
    }
    $patient_id = $patient['id'];

    // Check if doctor exists
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE id = ?");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch();
    if (!$doctor) {
        echo "<script>alert('Invalid doctor selected.'); window.location.href='patient_portal.php';</script>";
        exit();
    }

    // Check if an appointment already exists for the same patient and doctor on the same date
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE patient_id = ? AND doctor_id = ? AND appointment_date = ?");
    $stmt->execute([$patient_id, $doctor_id, $appointment_date]);
    $existing_appointment = $stmt->fetch();
    if ($existing_appointment) {
        echo "<script>alert('You already have an appointment booked with this doctor on the selected date.'); window.location.href='patient_portal.php';</script>";
        exit();
    }

    // Insert booking into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) 
                               VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$patient_id, $doctor_id, $appointment_date]);

        echo "<script>alert('Appointment booked successfully!'); window.location.href='patient_portal.php';</script>";
    } catch (PDOException $e) {
        // Log the full error message in case of database issues
        error_log("Error: " . $e->getMessage());
        echo "<script>alert('There was a problem booking your appointment. Please try again later.'); window.location.href='patient_portal.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='patient_portal.php';</script>";
}
?>
