<?php 
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

$appointment_id = $_GET['appointment_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_prescription'])) {
    $appointment_id = $_POST['appointment_id'];
    $prescription = trim($_POST['prescription']);

    if (empty($prescription)) {
        $error_message = "Prescription cannot be empty.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO prescriptions (appointment_id, typed_prescription)
            VALUES (?, ?)
        ");
        $stmt->execute([$appointment_id, $prescription]);

        $stmt = $pdo->prepare("
            UPDATE appointments
            SET prescription_submitted = 1
            WHERE id = ? AND doctor_id = ?
        ");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);

        $stmt = $pdo->prepare("SELECT doctor_note_submitted FROM appointments WHERE id = ?");
        $stmt->execute([$appointment_id]);
        $appointment_status = $stmt->fetch();

        if ($appointment_status['doctor_note_submitted']) {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
            $stmt->execute([$appointment_id]);
        }

        header("Location: doctor_portal.php");
        exit();
    }
}

if (!$appointment_id || !is_numeric($appointment_id)) {
    echo "Invalid appointment ID.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.*, p.full_name AS patient_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.id = ? AND a.doctor_id = ?
");
$stmt->execute([$appointment_id, $_SESSION['user_id']]);
$appointment = $stmt->fetch();

if (!$appointment) {
    echo "Appointment not found or you do not have permission to access it.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Prescription</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- CSS Styles -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-image: linear-gradient(135deg, #f0f4f8 25%, #dfe9f3 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
            animation: slideDown 0.8s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        form textarea {
            width: 100%;
            height: 200px;
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccd1d9;
            font-size: 16px;
            outline: none;
            resize: vertical;
            transition: border-color 0.3s;
        }
        form textarea:focus {
            border-color: #3498db;
        }
        form button {
            padding: 14px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            width: 100%;
        }
        form button:hover {
            background-color: #2980b9;
        }
        form button:active {
            transform: scale(0.98);
        }
        .error-message {
            color: #e74c3c;
            background-color: #fceae9;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        .back-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            h1 {
                font-size: 24px;
            }
            form button {
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Prescription for <?php echo htmlspecialchars($appointment['patient_name']); ?></h1>
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="submit_prescription.php?appointment_id=<?php echo htmlspecialchars($appointment['id']); ?>">
            <textarea name="prescription" required placeholder="Enter prescription here..."></textarea>
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['id']); ?>">
            <button type="submit" name="submit_prescription">Submit Prescription</button>
        </form>
        <div class="back-link">
            <a href="doctor_portal.php">Back to Doctor Portal</a>
        </div>
    </div>
</body>
</html>
