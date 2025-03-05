<?php
session_start();
if ($_SESSION['role'] != 'pharmacist') {
    header("Location: unauthorized.php");
    exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM pharmacists WHERE user_id = ?");
$stmt->execute([$user_id]);
$pharmacist = $stmt->fetch();

if (!$pharmacist) {
    echo "Pharmacist data not found.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT pr.*, p.full_name AS patient_name, p.date_of_birth, p.phone, p.city, d.full_name AS doctor_name
    FROM prescriptions pr
    JOIN appointments a ON pr.appointment_id = a.id
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    ORDER BY pr.created_at DESC
");
$stmt->execute();
$prescriptions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Portal</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <style>
        /* Redesigned CSS for the pharmacy portal */
        body {
            font-family: 'Roboto', sans-serif;
            background-image: linear-gradient(135deg, #f0f4f8 25%, #dfe9f3 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .container {
            max-width: 1100px;
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
        h1, h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        h1 {
            font-size: 36px;
            animation: slideDown 0.8s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        thead {
            background-color: #3498db;
        }
        thead th {
            color: #fff;
            padding: 15px;
            font-size: 16px;
            text-align: left;
        }
        tbody tr {
            border-bottom: 1px solid #ecf0f1;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fbfd;
        }
        tbody td {
            padding: 15px;
            font-size: 15px;
            color: #2c3e50;
        }
        .button {
            padding: 10px 15px;
            background-color: #2ecc71;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.1s;
            display: inline-block;
        }
        .button:hover {
            background-color: #27ae60;
        }
        .button:active {
            transform: scale(0.98);
        }
        .logout-link {
            text-align: right;
            margin-top: 30px;
        }
        .logout-link a {
            color: #e74c3c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        .logout-link a:hover {
            color: #c0392b;
            text-decoration: underline;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            h1, h2 {
                font-size: 24px;
            }
            table thead th, table tbody td {
                padding: 10px;
                font-size: 14px;
            }
            .button {
                font-size: 14px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($pharmacist['full_name']); ?></h1>
        <h2>Prescriptions</h2>
        <table>
            <thead>
                <tr>
                    <th>Prescription ID</th>
                    <th>Patient Name</th>
                    <th>Patient Details</th>
                    <th>Doctor Name</th>
                    <th>Prescription Details</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prescriptions)): ?>
                    <?php foreach ($prescriptions as $prescription): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prescription['id']); ?></td>
                            <td><?php echo htmlspecialchars($prescription['patient_name']); ?></td>
                            <td>
                                Date of Birth: <?php echo htmlspecialchars($prescription['date_of_birth']); ?><br>
                                Phone: <?php echo htmlspecialchars($prescription['phone']); ?><br>
                                City: <?php echo htmlspecialchars($prescription['city']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($prescription['doctor_name']); ?></td>
                            <td>
                                <?php if ($prescription['typed_prescription']): ?>
                                    <?php echo nl2br(htmlspecialchars($prescription['typed_prescription'])); ?>
                                <?php endif; ?>
                                <?php if ($prescription['prescription_image']): ?>
                                    <br><a href="<?php echo htmlspecialchars($prescription['prescription_image']); ?>" target="_blank">View Image</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="process_prescription.php?prescription_id=<?php echo htmlspecialchars($prescription['id']); ?>" class="button">Process</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No prescriptions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
