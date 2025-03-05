<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("Location: unauthorized.php");
    exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->execute([$user_id]);
$patient = $stmt->fetch();

if ($patient === false) {
    echo "<script>alert('No patient data found. Please contact the administrator.'); window.location.href='logout.php';</script>";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM doctors");
$stmt->execute();
$doctors = $stmt->fetchAll();

if (empty($doctors)) {
    echo "<script>alert('No doctors available at the moment.');</script>";
}

$stmt = $pdo->prepare("
    SELECT a.*, d.full_name AS doctor_name, d.department, p.medical_history,
           pr.id AS prescription_id
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN patients p ON a.patient_id = p.id
    LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
    WHERE a.patient_id = ?
");
$stmt->execute([$patient['id']]);
$appointments = $stmt->fetchAll();

$action = $_GET['action'] ?? null;
$prescription = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Portal</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            margin: 0;
            padding: 0;
            color: #ecf0f1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(135deg, #2c3e50 25%, #34495e 100%);
        }
        .container {
            max-width: 1200px;
            width: 90%;
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
            display: flex;
            align-items: center;
            animation: slideInLeft 0.5s ease;
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        /* Patient Profile Image */
        .patient-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-left: 15px;
            border: 3px solid #1abc9c;
            animation: fadeIn 1s ease;
        }
        .patient-image img {
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
            display: flex;
            align-items: center;
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
        /* Your Information */
        .patient-info, .appointment-section {
            margin-bottom: 50px;
        }
        .patient-info h2, .appointment-section h2 {
            font-size: 28px;
            color: #1abc9c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .patient-info h2 i, .appointment-section h2 i {
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        thead {
            background-color: #34495e;
        }
        thead th {
            color: #ecf0f1;
            padding: 15px;
            font-size: 16px;
            text-align: left;
        }
        tbody tr {
            border-bottom: 1px solid #2c3e50;
            transition: background-color 0.3s;
        }
        tbody tr:hover {
            background-color: #2c3e50;
        }
        tbody tr:nth-child(even) {
            background-color: #1e272e;
        }
        tbody td {
            padding: 15px;
            font-size: 15px;
            color: #ecf0f1;
            vertical-align: middle;
        }
        .appointment-section form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 20px;
        }
        .appointment-section form label {
            font-size: 16px;
            margin-right: 10px;
            color: #bdc3c7;
            display: flex;
            align-items: center;
            flex: 1 1 100%;
            margin-bottom: 10px;
        }
        .appointment-section form label i {
            margin-right: 5px;
            color: #1abc9c;
        }
        .appointment-section form select, 
        .appointment-section form input {
            padding: 12px;
            margin-right: 15px;
            border-radius: 8px;
            border: 1px solid #bdc3c7;
            font-size: 16px;
            outline: none;
            flex: 1 1 45%;
            margin-bottom: 15px;
            transition: border-color 0.3s;
            background-color: #2c3e50;
            color: #ecf0f1;
        }
        .appointment-section form select:focus, 
        .appointment-section form input:focus {
            border-color: #3498db;
            background-color: #34495e;
        }
        .appointment-section form button {
            padding: 14px 20px;
            background-color: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            flex: 1 1 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .appointment-section form button i {
            margin-right: 5px;
        }
        .appointment-section form button:hover {
            background-color: #27ae60;
        }
        .appointment-section form button:active {
            transform: scale(0.98);
        }
        a.button {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            background-color: #3498db;
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
            background-color: #2980b9;
        }
        a.button:active {
            transform: scale(0.98);
        }
        /* Logout Link */
        .logout-link {
            text-align: right;
            margin-top: 30px;
        }
        .logout-link a {
            color: #e74c3c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .logout-link a i {
            margin-right: 5px;
        }
        .logout-link a:hover {
            color: #c0392b;
            text-decoration: underline;
        }
        /* Prescription Container */
        .prescription-container {
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
        .prescription-container h2 {
            font-size: 24px;
            color: #1abc9c;
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .prescription-container h2 i {
            margin-right: 10px;
        }
        .prescription-container p {
            font-size: 16px;
            color: #ecf0f1;
            background-color: #2c3e50;
            padding: 15px;
            border-radius: 8px;
            line-height: 1.6;
        }
        .prescription-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #bdc3c7;
            border-radius: 8px;
            margin-top: 20px;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px 20px;
            }
            h1, h2 {
                font-size: 24px;
            }
            table thead th, table tbody td {
                padding: 10px;
                font-size: 14px;
            }
            .appointment-section form label {
                font-size: 14px;
            }
            .appointment-section form select, 
            .appointment-section form input {
                flex: 1 1 100%;
                margin-right: 0;
            }
            .appointment-section form button {
                flex: 1 1 100%;
            }
            .patient-image {
                width: 50px;
                height: 50px;
            }
            .prescription-container {
                width: 90%;
                padding: 20px;
            }
            .prescription-container h2 {
                font-size: 20px;
            }
            .prescription-container button.button {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<?php
if ($action == 'view_prescription') {
    $prescription_id = $_GET['prescription_id'] ?? null;
    if (!$prescription_id || !is_numeric($prescription_id)) {
        echo "<script>alert('Invalid prescription ID.'); window.location.href='patient_portal.php';</script>";
        exit();
    }

    $stmt = $pdo->prepare("
        SELECT pr.*, a.appointment_date, d.full_name AS doctor_name
        FROM prescriptions pr
        JOIN appointments a ON pr.appointment_id = a.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE pr.id = ? AND a.patient_id = ?
    ");
    $stmt->execute([$prescription_id, $patient['id']]);
    $prescription = $stmt->fetch();

    if (!$prescription) {
        echo "<script>alert('Prescription not found or you do not have permission to access it.'); window.location.href='patient_portal.php';</script>";
        exit();
    }
    ?>
    <div class="prescription-container">
        <h2><i class="fas fa-eye"></i> Prescription from Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?></h2>
        <p><strong>Appointment Date:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($prescription['appointment_date']))); ?></p>

        <?php if ($prescription['typed_prescription']): ?>
            <h3><i class="fas fa-pen-alt"></i> Typed Prescription:</h3>
            <p><?php echo nl2br(htmlspecialchars($prescription['typed_prescription'])); ?></p>
        <?php endif; ?>

        <?php if ($prescription['prescription_image']): ?>
            <h3><i class="fas fa-image"></i> Prescription Image:</h3>
            <img src="<?php echo htmlspecialchars($prescription['prescription_image']); ?>" alt="Prescription Image">
        <?php endif; ?>

        <p style="text-align: center; margin-top: 30px;">
            <a href="patient_portal.php" class="button"><i class="fas fa-arrow-left"></i> Back to Portal</a>
        </p>
    </div>
    <?php
    exit();
}
?>
    <div class="container">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($patient['full_name']); ?>
                <!-- Patient Profile Image -->
                <?php if (!empty($patient['image_path']) && file_exists($patient['image_path'])): ?>
                    <div class="patient-image">
                        <img src="<?php echo htmlspecialchars($patient['image_path']); ?>" alt="Patient Profile Image">
                    </div>
                <?php else: ?>
                    <div class="patient-image">
                        <img src="https://www.shutterstock.com/image-vector/male-avatar-icon-260nw-1937073961.jpg" alt="Default Patient Image">
                    </div>
                <?php endif; ?>
            </h1>
            <form method="POST" action="logout.php">
                <button type="submit" class="logout-button"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>

        <!-- Your Information -->
        <div class="patient-info">
            <h2><i class="fas fa-user"></i> Your Information</h2>
            <table>
                <tbody>
                    <tr>
                        <th>Name</th>
                        <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td><?php echo htmlspecialchars($patient['date_of_birth']); ?></td>
                    </tr>
                    <tr>
                        <th>City</th>
                        <td><?php echo htmlspecialchars($patient['city']); ?></td>
                    </tr>
                    <tr>
                        <th>Phone Number</th>
                        <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Book an Appointment -->
        <div class="appointment-section">
            <h2><i class="fas fa-calendar-plus"></i> Book an Appointment</h2>
            <form method="POST" action="book_appointment.php">
                <label for="doctor_id"><i class="fas fa-user-md"></i> Select Doctor:</label>
                <select name="doctor_id" id="doctor_id" required>
                    <option value="">-- Select Doctor --</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo htmlspecialchars($doctor['id']); ?>">
                            <?php echo htmlspecialchars($doctor['full_name'] . " - " . $doctor['department']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="appointment_date"><i class="fas fa-calendar-alt"></i> Select Date:</label>
                <input type="datetime-local" name="appointment_date" id="appointment_date" required>

                <button type="submit" name="book_appointment"><i class="fas fa-book"></i> Book Appointment</button>
            </form>
        </div>

        <!-- Your Appointments -->
        <div class="appointment-section">
            <h2><i class="fas fa-calendar-check"></i> Your Appointments</h2>
            <table>
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Medical History</th>
                        <th>Prescription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['department']); ?></td>
                                <td><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($appointment['appointment_date']))); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($appointment['status'])); ?></td>
                                <td>
                                    <?php if (!empty($appointment['medical_history'])): ?>
                                        <a href="view_medical_history.php?patient_id=<?php echo htmlspecialchars($patient['id']); ?>" class="button"><i class="fas fa-file-medical-alt"></i> View</a>
                                    <?php else: ?>
                                        No history available
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($appointment['prescription_id']): ?>
                                        <a href="patient_portal.php?action=view_prescription&prescription_id=<?php echo htmlspecialchars($appointment['prescription_id']); ?>" class="button"><i class="fas fa-eye"></i> View</a>
                                    <?php else: ?>
                                        No prescription available
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Logout Link -->
        <div class="logout-link">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</body>
</html>
