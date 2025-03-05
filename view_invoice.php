<?php
session_start();

include 'config.php';

$bill_id = $_GET['bill_id'] ?? null;

if (!$bill_id || !is_numeric($bill_id)) {
    echo "Invalid bill ID.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT b.*, p.full_name AS patient_name, p.date_of_birth, p.phone, p.city
    FROM bills b
    JOIN patients p ON b.patient_id = p.id
    WHERE b.id = ?
");
$stmt->execute([$bill_id]);
$bill = $stmt->fetch();

if (!$bill) {
    echo "Bill not found.";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = ?");
$stmt->execute([$bill_id]);
$bill_items = $stmt->fetchAll();

$user_role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if ($user_role == 'pharmacist') {
    // Pharmacist can view all invoices
} elseif ($user_role == 'patient') {
    $stmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
    $stmt->execute([$bill['patient_id']]);
    $patient_user = $stmt->fetch();

    if (!$patient_user || $patient_user['user_id'] != $user_id) {
        echo "You do not have permission to view this invoice.";
        exit();
    }
} else {
    echo "You do not have permission to view this invoice.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Invoice #<?php echo htmlspecialchars($bill['id']); ?></title>
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
            max-width: 900px;
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
            margin-bottom: 10px;
            text-align: center;
            font-size: 32px;
            animation: slideDown 0.8s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .invoice-header p {
            text-align: center;
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        .patient-details, .invoice-details {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 22px;
            color: #34495e;
            margin-bottom: 15px;
            border-left: 5px solid #3498db;
            padding-left: 10px;
        }
        .patient-details p {
            font-size: 16px;
            color: #2c3e50;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table thead {
            background-color: #3498db;
        }
        table thead th {
            color: #fff;
            padding: 12px;
            text-align: left;
            font-size: 16px;
        }
        table tbody td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 16px;
            color: #2c3e50;
        }
        .total {
            text-align: right;
            font-size: 20px;
            margin-top: 20px;
            color: #2c3e50;
        }
        .download-link {
            margin-top: 30px;
            text-align: center;
        }
        .download-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 18px;
            transition: color 0.3s;
        }
        .download-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        .back-link {
            margin-top: 30px;
            text-align: center;
        }
        .back-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 18px;
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
                font-size: 28px;
            }
            .section-title {
                font-size: 20px;
            }
            table thead th, table tbody td {
                font-size: 14px;
            }
            .total {
                font-size: 18px;
            }
            .download-link a, .back-link a {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-header">
            <h1>Invoice #<?php echo htmlspecialchars($bill['id']); ?></h1>
            <p>Date: <?php echo htmlspecialchars(date('d-m-Y', strtotime($bill['created_at']))); ?></p>
        </div>

        <div class="patient-details">
            <h2 class="section-title">Patient Details:</h2>
            <p>
                <strong>Name:</strong> <?php echo htmlspecialchars($bill['patient_name']); ?><br>
                <strong>Date of Birth:</strong> <?php echo htmlspecialchars($bill['date_of_birth']); ?><br>
                <strong>Phone:</strong> <?php echo htmlspecialchars($bill['phone']); ?><br>
                <strong>City:</strong> <?php echo htmlspecialchars($bill['city']); ?>
            </p>
        </div>

        <div class="invoice-details">
            <h2 class="section-title">Invoice Details:</h2>
            <table>
                <thead>
                    <tr>
                        <th>Medication Name</th>
                        <th>Quantity</th>
                        <th>Price per Unit ($)</th>
                        <th>Line Total ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bill_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['medication_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total">
                <strong>Total Amount: $<?php echo htmlspecialchars(number_format($bill['total_amount'], 2)); ?></strong>
            </div>
        </div>

        <div class="download-link">
            <a href="download_invoice.php?bill_id=<?php echo htmlspecialchars($bill['id']); ?>">Download Invoice as PDF</a>
        </div>

        <div class="back-link">
            <?php if ($user_role == 'pharmacist'): ?>
                <a href="pharmacy_portal.php">Back to Pharmacy Portal</a>
            <?php elseif ($user_role == 'patient'): ?>
                <a href="patient_portal.php">Back to Patient Portal</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
