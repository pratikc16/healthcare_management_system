<?php
session_start();
include 'config.php';

if ($_SESSION['role'] != 'patient' && $_SESSION['role'] != 'doctor') {
    header("Location: unauthorized.php");
    exit();
}

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    echo "<script>alert('Patient ID not provided.'); window.location.href='patient_portal.php';</script>";
    exit();
}

$stmt = $pdo->prepare("SELECT full_name, medical_history FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    echo "<script>alert('No medical history found for this patient.'); window.location.href='patient_portal.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Medical History</title>
    <!-- Google Fonts -->
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
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 28px;
            animation: slideDown 0.8s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .medical-history {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border-left: 5px solid #3498db;
            animation: fadeInContent 1s ease;
        }
        @keyframes fadeInContent {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        h2 {
            color: #34495e;
            font-size: 22px;
            margin-bottom: 15px;
        }
        p {
            color: #2c3e50;
            line-height: 1.6;
            font-size: 16px;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.1s;
        }
        .back-link a:hover {
            background-color: #2980b9;
        }
        .back-link a:active {
            transform: scale(0.98);
        }
        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 20px 15px;
                margin: 30px auto;
            }
            h1 {
                font-size: 24px;
            }
            h2 {
                font-size: 18px;
            }
            p {
                font-size: 14px;
            }
            .back-link a {
                font-size: 14px;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Medical History of <?php echo htmlspecialchars($patient['full_name']); ?></h1>
        <div class="medical-history">
            <h2>History Details:</h2>
            <p><?php echo nl2br(htmlspecialchars($patient['medical_history'])); ?></p>
        </div>
        <div class="back-link">
            <a href="patient_portal.php">Back to Patient Portal</a>
        </div>
    </div>
</body>
</html>
