<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}

$appointment_id = $_GET['appointment_id'] ?? null;

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
<html>
<head>
    <title>Add Doctor's Note</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
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
        }
        form textarea {
            width: 100%;
            height: 200px;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ccd1d9;
            font-size: 16px;
            margin-bottom: 25px;
            resize: vertical;
            transition: border-color 0.3s;
        }
        form textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        form button {
            padding: 15px 25px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            display: block;
            width: 100%;
        }
        form button:hover {
            background-color: #2980b9;
        }
        form button:active {
            transform: scale(0.98);
        }
        .back-link {
            margin-top: 30px;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Doctor's Note for <?php echo htmlspecialchars($appointment['patient_name']); ?></h1>
        <form method="POST" action="submit_doctor_note.php">
            <textarea name="doctor_note" required placeholder="Enter doctor's note here..."></textarea>
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['id']); ?>">
            <button type="submit" name="submit_doctor_note">Submit Doctor's Note</button>
        </form>
        <div class="back-link">
            <a href="doctor_portal.php">Back to Doctor Portal</a>
        </div>
    </div>
</body>
</html>
