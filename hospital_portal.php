<?php
// hospital_portal.php

session_start();
if ($_SESSION['role'] != 'hospital') {
    header("Location: unauthorized.php");
    exit();
}

include 'config.php';

$hospital_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM hospitals WHERE user_id = ?");
$stmt->execute([$hospital_id]);
$hospital = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE hospital_id = ?");
$stmt->execute([$hospital_id]);
$doctors = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM patients WHERE hospital_id = ?");
$stmt->execute([$hospital_id]);
$patients = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Portal</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($hospital['name']); ?></h1>

    <h2>Manage Doctors</h2>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Specialty</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($doctors as $doctor): ?>
            <tr>
                <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                <td><?php echo htmlspecialchars($doctor['department']); ?></td>
                <td><?php echo htmlspecialchars($doctor['specialty']); ?></td>
                <td><a href="edit_doctor.php?id=<?php echo htmlspecialchars($doctor['id']); ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="add_doctor.php">Add New Doctor</a>

    <h2>Manage Patients</h2>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Date of Birth</th>
            <th>Medical History</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($patients as $patient): ?>
            <tr>
                <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                <td><?php echo htmlspecialchars($patient['date_of_birth']); ?></td>
                <td><?php echo htmlspecialchars($patient['medical_history']); ?></td>
                <td><a href="edit_patient.php?id=<?php echo htmlspecialchars($patient['id']); ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="add_patient.php">Add New Patient</a>

    <h2>Billing and Payments</h2>
    <p><a href="billing_portal.php">Go to Billing Portal</a></p>

    <a href="logout.php">Logout</a>
</body>
</html>
