<?php
// doctor_portal.php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: unauthorized.php");
    exit();
}

include 'config.php'; // Include database connection

// Fetch doctor data based on logged-in user
$doctor_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

if ($doctor === false) {
    echo "<script>alert('No doctor data found for this user. Please contact the administrator.'); window.location.href='logout.php';</script>";
    exit();
}

// Initialize variable for patient search
$searched_patient = null;

// Handle search by patient ID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_patient_id'])) {
    $search_patient_id = trim($_POST['search_patient_id']);

    // Validate Patient ID
    if (empty($search_patient_id) || !is_numeric($search_patient_id)) {
        $search_error = "Please enter a valid Patient ID.";
    } else {
        // Fetch patient details and appointments
        $stmt = $pdo->prepare("
            SELECT a.*, p.full_name AS patient_name, pr.id AS prescription_id
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.id 
            LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
            WHERE a.doctor_id = ? AND p.id = ?");
        $stmt->execute([$doctor['id'], $search_patient_id]);
        $searched_patient = $stmt->fetchAll();
        
        if (!$searched_patient) {
            $search_error = "No appointments found for the provided Patient ID.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags & Title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Portal</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CSS Styles -->
    <style>
        /* Reset and Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #ffffff;
            overflow: hidden;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px 40px;
            background-color: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            max-height: 80vh;
            animation: fadeIn 0.8s ease;
            position: relative;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
        }

        .header h1 {
            font-size: 28px;
            color: #1abc9c;
            animation: slideInLeft 0.5s ease;
            display: flex;
            align-items: center;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Doctor Profile Image */
        .doctor-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-left: 15px;
            border: 3px solid #1abc9c;
            animation: fadeIn 1s ease;
        }

        .doctor-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Logout Button */
        .logout-button {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            animation: slideInRight 0.5s ease;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .logout-button:hover {
            background-color: #c0392b;
        }

        .logout-button:active {
            transform: scale(0.98);
        }

        /* Search Form */
        .search-form {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeIn 1s ease;
        }

        .search-form label {
            font-size: 18px;
            margin-right: 10px;
            color: #ecf0f1;
            display: flex;
            align-items: center;
        }

        .search-form label i {
            margin-right: 5px;
            color: #1abc9c;
        }

        .search-form input[type="text"] {
            padding: 10px 15px;
            width: 250px;
            border-radius: 8px;
            border: 1px solid #34495e;
            font-size: 16px;
            outline: none;
            background-color: #2c3e50;
            color: #ecf0f1;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .search-form input[type="text"]:focus {
            border-color: #1abc9c;
            background-color: #34495e;
        }

        .search-form button {
            padding: 10px 20px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-left: 15px;
            transition: background-color 0.3s, transform 0.1s;
            display: flex;
            align-items: center;
        }

        .search-form button i {
            margin-right: 5px;
        }

        .search-form button:hover {
            background-color: #16a085;
        }

        .search-form button:active {
            transform: scale(0.98);
        }

        /* Error Message */
        .error-message {
            text-align: center;
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 16px;
            animation: fadeIn 1s ease;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            animation: fadeIn 1.2s ease;
        }

        table thead {
            background-color: #34495e;
        }

        table thead th {
            color: #ecf0f1;
            padding: 15px;
            font-size: 16px;
            text-align: left;
        }

        table tbody tr {
            border-bottom: 1px solid #2c3e50;
            transition: background-color 0.3s;
        }

        table tbody tr:hover {
            background-color: #2c3e50;
        }

        table tbody tr:nth-child(even) {
            background-color: #1e272e;
        }

        table tbody td {
            padding: 15px;
            font-size: 15px;
            color: #ecf0f1;
            vertical-align: middle;
        }

        a.button {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            background-color: #2ecc71;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.1s;
        }

        a.button i {
            margin-right: 5px;
        }

        a.button:hover {
            background-color: #27ae60;
        }

        a.button:active {
            transform: scale(0.98);
        }

        /* Prescription Details */
        .prescription-details, .view-prescription-details {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px 40px;
            background-color: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease;
            color: #ecf0f1;
        }

        .prescription-details h2, .view-prescription-details h2 {
            font-size: 24px;
            color: #1abc9c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .prescription-details h2 i, .view-prescription-details h2 i {
            margin-right: 10px;
        }

        .prescription-details form, .view-prescription-details form {
            display: flex;
            flex-direction: column;
        }

        .prescription-details label, .view-prescription-details label {
            margin-bottom: 8px;
            font-size: 16px;
            color: #ecf0f1;
        }

        .prescription-details textarea, .view-prescription-details textarea {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #34495e;
            font-size: 16px;
            outline: none;
            background-color: #2c3e50;
            color: #ecf0f1;
            resize: vertical;
            transition: border-color 0.3s, background-color 0.3s;
            margin-bottom: 20px;
        }

        .prescription-details textarea:focus, .view-prescription-details textarea:focus {
            border-color: #1abc9c;
            background-color: #34495e;
        }

        .prescription-details input[type="file"], .view-prescription-details input[type="file"] {
            padding: 8px 12px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            background-color: #2c3e50;
            color: #ecf0f1;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }

        .prescription-details input[type="file"]:focus, .view-prescription-details input[type="file"]:focus {
            background-color: #34495e;
        }

        .prescription-details button.submit-btn, .view-prescription-details button.back-btn {
            padding: 12px 20px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            align-self: flex-start;
        }

        .prescription-details button.submit-btn:hover, .view-prescription-details button.back-btn:hover {
            background-color: #16a085;
        }

        .prescription-details button.submit-btn:active, .view-prescription-details button.back-btn:active {
            transform: scale(0.98);
        }

        /* Prescription Image */
        .prescription-image img {
            max-width: 100%;
            height: auto;
            border: 1px solid #34495e;
            border-radius: 8px;
            margin-top: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .doctor-image {
                width: 50px;
                height: 50px;
            }

            .search-form {
                flex-direction: column;
            }

            .search-form label {
                margin-bottom: 10px;
            }

            .search-form input[type="text"] {
                width: 100%;
                margin-bottom: 15px;
            }

            .search-form button {
                width: 100%;
                margin-left: 0;
            }

            table thead th, table tbody td {
                padding: 10px;
                font-size: 14px;
            }

            a.button {
                padding: 8px 10px;
                font-size: 13px;
            }

            .prescription-details, .view-prescription-details {
                width: 90%;
                padding: 20px;
            }

            .prescription-details h2, .view-prescription-details h2 {
                font-size: 20px;
            }

            .prescription-details button.submit-btn, .view-prescription-details button.back-btn {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, Dr. <?php echo htmlspecialchars($doctor['full_name']); ?>
                <!-- Doctor Profile Image -->
                <div class="doctor-image">
                    <img src="https://www.shutterstock.com/image-photo/profile-photo-attractive-family-doc-600nw-1724693776.jpg" alt="Doctor Profile Image">
                </div>
            </h1>
            <form method="POST" action="logout.php">
                <button type="submit" class="logout-button"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>

        <!-- Search Form for Patient ID -->
        <div class="search-form">
            <form method="POST" action="doctor_portal.php">
                <label for="search_patient_id"><i class="fas fa-user-search"></i> Search by Patient ID:</label>
                <input type="text" name="search_patient_id" id="search_patient_id" placeholder="Enter Patient ID" required>
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <!-- Display Search Error -->
        <?php if (isset($search_error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($search_error); ?>
            </div>
        <?php endif; ?>

        <!-- Search Results Section -->
        <?php if ($searched_patient !== null && empty($search_error)): ?>
            <h2><i class="fas fa-results"></i> Search Results for Patient ID: <?php echo htmlspecialchars($search_patient_id); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Appointment Date</th>
                        <th>Status</th>
                        <th>Doctor Note</th>
                        <th>Prescription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($searched_patient)): ?>
                        <?php foreach ($searched_patient as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($appointment['appointment_date']))); ?></td>
                                <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                                <td>
                                    <?php if ($appointment['status'] != 'Completed'): ?>
                                        <a href="add_doctor_note.php?appointment_id=<?php echo htmlspecialchars($appointment['id']); ?>" class="button"><i class="fas fa-edit"></i> Add Note</a>
                                    <?php else: ?>
                                        Completed
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($appointment['prescription_id']): ?>
                                        <a href="doctor_portal.php?action=view_prescription&prescription_id=<?php echo htmlspecialchars($appointment['prescription_id']); ?>" class="button"><i class="fas fa-eye"></i> View Prescription</a>
                                    <?php else: ?>
                                        <?php if ($appointment['status'] != 'Completed'): ?>
                                            <a href="doctor_portal.php?action=add_prescription&appointment_id=<?php echo htmlspecialchars($appointment['id']); ?>" class="button"><i class="fas fa-plus-circle"></i> Add Prescription</a>
                                        <?php else: ?>
                                            No Prescription
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No appointments found for this patient.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Show All Appointments -->
        <h2><i class="fas fa-calendar-check"></i> Your Appointments</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Appointment Date</th>
                    <th>Status</th>
                    <th>Doctor Note</th>
                    <th>Prescription</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all appointments related to the logged-in doctor along with prescription info
                $stmt = $pdo->prepare("
                    SELECT a.*, p.full_name AS patient_name, pr.id AS prescription_id 
                    FROM appointments a
                    JOIN patients p ON a.patient_id = p.id
                    LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
                    WHERE a.doctor_id = ?");
                $stmt->execute([$doctor['id']]);
                $appointments = $stmt->fetchAll();

                if (!empty($appointments)) {
                    foreach ($appointments as $appointment) {
                        echo "<tr>
                                <td>" . htmlspecialchars($appointment['patient_name']) . "</td>
                                <td>" . htmlspecialchars(date("F j, Y, g:i a", strtotime($appointment['appointment_date']))) . "</td>
                                <td>" . htmlspecialchars($appointment['status']) . "</td>
                                <td>";
                        if ($appointment['status'] != 'Completed') {
                            echo '<a href="add_doctor_note.php?appointment_id=' . htmlspecialchars($appointment['id']) . '" class="button"><i class="fas fa-edit"></i> Add Note</a>';
                        } else {
                            echo 'Completed';
                        }
                        echo "</td>";

                        // Prescription Column
                        echo "<td>";
                        if ($appointment['prescription_id']) {
                            // Prescription exists
                            echo '<a href="doctor_portal.php?action=view_prescription&prescription_id=' . htmlspecialchars($appointment['prescription_id']) . '" class="button"><i class="fas fa-eye"></i> View Prescription</a>';
                        } else {
                            if ($appointment['status'] != 'Completed') {
                                // Allow adding prescription
                                echo '<a href="doctor_portal.php?action=add_prescription&appointment_id=' . htmlspecialchars($appointment['id']) . '" class="button"><i class="fas fa-plus-circle"></i> Add Prescription</a>';
                            } else {
                                echo 'No Prescription';
                            }
                        }
                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No appointments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

<?php
// Handle add prescription action
$action = $_GET['action'] ?? null;

if ($action == 'add_prescription') {
    // Handle add prescription
    // Check if appointment_id is provided and valid
    $appointment_id = $_GET['appointment_id'] ?? null;
    if (!$appointment_id || !is_numeric($appointment_id)) {
        echo "<script>alert('Invalid appointment ID.'); window.location.href='doctor_portal.php';</script>";
        exit();
    }

    // Fetch the appointment to ensure it belongs to the logged-in doctor
    $stmt = $pdo->prepare("
        SELECT a.*, p.full_name AS patient_name, p.id AS patient_id
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE a.id = ? AND a.doctor_id = ?");
    $stmt->execute([$appointment_id, $doctor['id']]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        echo "<script>alert('Appointment not found or you do not have permission to access it.'); window.location.href='doctor_portal.php';</script>";
        exit();
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Process form submission
        $typed_prescription = trim($_POST['typed_prescription'] ?? '');
        $image_file = $_FILES['image_file'] ?? null;
        $errors = [];
        $prescription_image = null;

        // Handle image upload
        if ($image_file && $image_file['error'] != UPLOAD_ERR_NO_FILE) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($image_file['name'], PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_types)) {
                $errors[] = "Invalid image type. Only JPG, JPEG, PNG, and GIF are allowed.";
            } else {
                $target_dir = 'uploads/prescriptions/';
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $new_file_name = uniqid() . '.' . $file_ext;
                $target_file = $target_dir . $new_file_name;

                if (!move_uploaded_file($image_file['tmp_name'], $target_file)) {
                    $errors[] = "Failed to upload image.";
                } else {
                    $prescription_image = $target_file;
                }
            }
        }

        if (empty($typed_prescription) && !$prescription_image) {
            $errors[] = "Please provide a typed prescription or upload an image.";
        }

        if (empty($errors)) {
            // Insert into prescriptions table
            $stmt = $pdo->prepare("
                INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, typed_prescription, prescription_image)
                VALUES (?, ?, ?, ?, ?)
            ");
            $patient_id = $appointment['patient_id'];
            $stmt->execute([$appointment_id, $patient_id, $doctor['id'], $typed_prescription, $prescription_image]);

            // Optionally update appointment status
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?");
            $stmt->execute([$appointment_id]);

            echo "<script>alert('Prescription successfully submitted.'); window.location.href = 'doctor_portal.php';</script>";
            exit();
        }
    }

    // Display the form to add a prescription
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <!-- Meta Tags & Title -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Prescription</title>

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">

        <!-- Font Awesome for Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- CSS Styles -->
        <style>
            /* Reset and Base Styles */
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            html, body {
                height: 100%;
                font-family: 'Roboto', sans-serif;
                background-color: #121212;
                color: #ffffff;
                overflow: hidden;
            }

            /* Container */
            .prescription-container {
                max-width: 700px;
                margin: 50px auto;
                padding: 30px 40px;
                background-color: #1e1e1e;
                border-radius: 12px;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
                animation: fadeIn 0.8s ease;
                color: #ecf0f1;
                position: relative;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Header */
            .prescription-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
            }

            .prescription-header h2 {
                font-size: 24px;
                color: #1abc9c;
                display: flex;
                align-items: center;
                animation: slideInLeft 0.5s ease;
            }

            .prescription-header h2 i {
                margin-right: 10px;
            }

            @keyframes slideInLeft {
                from { opacity: 0; transform: translateX(-20px); }
                to { opacity: 1; transform: translateX(0); }
            }

            .back-button {
                padding: 8px 16px;
                background-color: #3498db;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s, transform 0.1s;
                display: flex;
                align-items: center;
                animation: slideInRight 0.5s ease;
            }

            .back-button i {
                margin-right: 5px;
            }

            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(20px); }
                to { opacity: 1; transform: translateX(0); }
            }

            .back-button:hover {
                background-color: #2980b9;
            }

            .back-button:active {
                transform: scale(0.98);
            }

            /* Error Messages */
            .errors p {
                color: #e74c3c;
                font-size: 16px;
                margin-bottom: 15px;
                background-color: #2c1f1f;
                padding: 10px;
                border-radius: 6px;
            }

            /* Form Elements */
            form {
                display: flex;
                flex-direction: column;
            }

            label {
                margin-bottom: 8px;
                font-size: 16px;
                color: #ecf0f1;
            }

            textarea {
                padding: 10px 15px;
                border-radius: 8px;
                border: 1px solid #34495e;
                font-size: 16px;
                outline: none;
                background-color: #2c3e50;
                color: #ecf0f1;
                resize: vertical;
                transition: border-color 0.3s, background-color 0.3s;
                margin-bottom: 20px;
            }

            textarea:focus {
                border-color: #1abc9c;
                background-color: #34495e;
            }

            input[type="file"] {
                padding: 8px 12px;
                font-size: 16px;
                border: none;
                border-radius: 8px;
                background-color: #2c3e50;
                color: #ecf0f1;
                transition: background-color 0.3s;
                margin-bottom: 20px;
            }

            input[type="file"]:focus {
                background-color: #34495e;
            }

            button.submit-btn {
                padding: 12px 20px;
                background-color: #1abc9c;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s, transform 0.1s;
                align-self: flex-start;
                display: flex;
                align-items: center;
            }

            button.submit-btn i {
                margin-right: 5px;
            }

            button.submit-btn:hover {
                background-color: #16a085;
            }

            button.submit-btn:active {
                transform: scale(0.98);
            }

            /* Prescription Image */
            .prescription-image img {
                max-width: 100%;
                height: auto;
                border: 1px solid #34495e;
                border-radius: 8px;
                margin-top: 20px;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .prescription-container {
                    width: 90%;
                    padding: 20px;
                }

                .prescription-header h2 {
                    font-size: 20px;
                }

                .prescription-header h2 i {
                    margin-right: 5px;
                }

                .back-button {
                    padding: 6px 12px;
                    font-size: 12px;
                }

                .prescription-details, .view-prescription-details {
                    width: 90%;
                    padding: 20px;
                }

                .prescription-details h2, .view-prescription-details h2 {
                    font-size: 20px;
                }

                .prescription-details button.submit-btn, .view-prescription-details button.back-btn {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class="prescription-container">
            <div class="prescription-header">
                <h2><i class="fas fa-file-prescription"></i> Add Prescription for <?php echo htmlspecialchars($appointment['patient_name']); ?></h2>
                <form method="GET" action="doctor_portal.php">
                    <button type="submit" class="back-button"><i class="fas fa-arrow-left"></i> Back</button>
                </form>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <label for="typed_prescription"><i class="fas fa-pen-alt"></i> Typed Prescription:</label>
                <textarea name="typed_prescription" id="typed_prescription" rows="8" placeholder="Enter typed prescription..."><?php echo htmlspecialchars($typed_prescription ?? ''); ?></textarea>

                <label for="image_file"><i class="fas fa-image"></i> Upload Prescription Image:</label>
                <input type="file" name="image_file" id="image_file" accept=".jpg, .jpeg, .png, .gif">

                <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit Prescription</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

if ($action == 'view_prescription') {
    // Handle view prescription
    $prescription_id = $_GET['prescription_id'] ?? null;
    if (!$prescription_id || !is_numeric($prescription_id)) {
        echo "<script>alert('Invalid prescription ID.'); window.location.href='doctor_portal.php';</script>";
        exit();
    }

    // Fetch the prescription
    $stmt = $pdo->prepare("
        SELECT pr.*, p.full_name AS patient_name
        FROM prescriptions pr
        JOIN appointments a ON pr.appointment_id = a.id
        JOIN patients p ON a.patient_id = p.id
        WHERE pr.id = ? AND a.doctor_id = ?");
    $stmt->execute([$prescription_id, $doctor['id']]);
    $prescription = $stmt->fetch();

    if (!$prescription) {
        echo "<script>alert('Prescription not found or you do not have permission to access it.'); window.location.href='doctor_portal.php';</script>";
        exit();
    }

    // Display the prescription
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <!-- Meta Tags & Title -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>View Prescription</title>

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">

        <!-- Font Awesome for Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- CSS Styles -->
        <style>
            /* Reset and Base Styles */
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            html, body {
                height: 100%;
                font-family: 'Roboto', sans-serif;
                background-color: #121212;
                color: #ffffff;
                overflow: hidden;
            }

            /* Container */
            .prescription-view-container {
                max-width: 800px;
                margin: 50px auto;
                padding: 30px 40px;
                background-color: #1e1e1e;
                border-radius: 12px;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
                animation: fadeIn 0.8s ease;
                color: #ecf0f1;
                position: relative;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Header */
            .prescription-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
            }

            .prescription-header h2 {
                font-size: 24px;
                color: #1abc9c;
                display: flex;
                align-items: center;
                animation: slideInLeft 0.5s ease;
            }

            .prescription-header h2 i {
                margin-right: 10px;
            }

            @keyframes slideInLeft {
                from { opacity: 0; transform: translateX(-20px); }
                to { opacity: 1; transform: translateX(0); }
            }

            .back-button {
                padding: 8px 16px;
                background-color: #3498db;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s, transform 0.1s;
                display: flex;
                align-items: center;
                animation: slideInRight 0.5s ease;
            }

            .back-button i {
                margin-right: 5px;
            }

            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(20px); }
                to { opacity: 1; transform: translateX(0); }
            }

            .back-button:hover {
                background-color: #2980b9;
            }

            .back-button:active {
                transform: scale(0.98);
            }

            /* Prescription Details */
            .prescription-details, .view-prescription-details {
                margin-top: 20px;
            }

            .prescription-details h3, .view-prescription-details h3 {
                font-size: 20px;
                color: #1abc9c;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
            }

            .prescription-details h3 i, .view-prescription-details h3 i {
                margin-right: 5px;
            }

            .prescription-details p, .view-prescription-details p {
                font-size: 16px;
                color: #ecf0f1;
                margin-bottom: 20px;
                background-color: #2c3e50;
                padding: 15px;
                border-radius: 8px;
                line-height: 1.5;
            }

            /* Prescription Image */
            .prescription-image img {
                max-width: 100%;
                height: auto;
                border: 1px solid #34495e;
                border-radius: 8px;
                margin-top: 20px;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .prescription-view-container {
                    width: 90%;
                    padding: 20px;
                }

                .prescription-header h2 {
                    font-size: 20px;
                }

                .prescription-header h2 i {
                    margin-right: 5px;
                }

                .back-button {
                    padding: 6px 12px;
                    font-size: 12px;
                }

                .prescription-details h3, .view-prescription-details h3 {
                    font-size: 18px;
                }

                .prescription-details p, .view-prescription-details p {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class="prescription-view-container">
            <div class="prescription-header">
                <h2><i class="fas fa-eye"></i> Prescription for <?php echo htmlspecialchars($prescription['patient_name']); ?></h2>
                <form method="GET" action="doctor_portal.php">
                    <button type="submit" class="back-button"><i class="fas fa-arrow-left"></i> Back</button>
                </form>
            </div>

            <?php if ($prescription['typed_prescription']): ?>
                <div class="prescription-details">
                    <h3><i class="fas fa-pen-alt"></i> Typed Prescription:</h3>
                    <p><?php echo nl2br(htmlspecialchars($prescription['typed_prescription'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($prescription['prescription_image']): ?>
                <div class="prescription-image">
                    <h3><i class="fas fa-image"></i> Prescription Image:</h3>
                    <img src="<?php echo htmlspecialchars($prescription['prescription_image']); ?>" alt="Prescription Image">
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
</body>
</html>