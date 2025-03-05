<?php 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: pharmacy_login.php");
    exit();
}

include 'config.php';

$prescription_id = $_GET['prescription_id'] ?? null;

if (!$prescription_id || !is_numeric($prescription_id)) {
    echo "Invalid prescription ID.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT pr.*, p.full_name AS patient_name, p.id AS patient_id
    FROM prescriptions pr
    JOIN appointments a ON pr.appointment_id = a.id
    JOIN patients p ON a.patient_id = p.id
    WHERE pr.id = ?
");
$stmt->execute([$prescription_id]);
$prescription = $stmt->fetch();

if (!$prescription) {
    echo "Prescription not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medications = $_POST['medications'];
    $quantities = $_POST['quantities'];
    $prices = $_POST['prices'];

    if (empty($medications) || empty($quantities) || empty($prices)) {
        echo "Please fill in all medication details.";
        exit();
    }

    $total_amount = 0;
    $bill_items = [];

    for ($i = 0; $i < count($medications); $i++) {
        $med_name = trim($medications[$i]);
        $quantity = (int)$quantities[$i];
        $price = (float)$prices[$i];

        if (empty($med_name) || $quantity <= 0 || $price < 0) {
            continue;
        }

        $line_total = $quantity * $price;
        $total_amount += $line_total;

        $bill_items[] = [
            'medication_name' => $med_name,
            'quantity' => $quantity,
            'price' => $price,
            'line_total' => $line_total
        ];
    }

    if (empty($bill_items)) {
        echo "No valid medication details provided.";
        exit();
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bills (prescription_id, patient_id, pharmacist_id, total_amount)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $prescription['id'],
            $prescription['patient_id'],
            $_SESSION['user_id'],
            $total_amount
        ]);

        $bill_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO bill_items (bill_id, medication_name, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($bill_items as $item) {
            $stmt->execute([
                $bill_id,
                $item['medication_name'],
                $item['quantity'],
                $item['price']
            ]);
        }

        $pdo->commit();

        header("Location: view_invoice.php?bill_id=$bill_id");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error generating bill: " . $e->getMessage();
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Process Prescription</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <style>
        /* Redesigned CSS */
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
        .prescription-details h3 {
            color: #34495e;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .prescription-details p {
            font-size: 16px;
            color: #2c3e50;
            background-color: #f9fbfd;
            padding: 15px;
            border-radius: 8px;
            line-height: 1.6;
        }
        .prescription-details img {
            max-width: 100%;
            height: auto;
            border: 1px solid #bdc3c7;
            border-radius: 8px;
            margin-top: 20px;
        }
        form h3 {
            color: #34495e;
            margin-bottom: 15px;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ecf0f1;
            text-align: left;
            font-size: 16px;
        }
        table th {
            background-color: #3498db;
            color: #fff;
        }
        table input[type="text"], table input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        table input[type="text"]:focus, table input[type="number"]:focus {
            border-color: #3498db;
        }
        .add-row-btn {
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
        }
        .add-row-btn:hover {
            background-color: #27ae60;
        }
        .add-row-btn:active {
            transform: scale(0.98);
        }
        button[type="submit"] {
            margin-top: 30px;
            padding: 15px 25px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            width: 100%;
        }
        button[type="submit"]:hover {
            background-color: #2980b9;
        }
        button[type="submit"]:active {
            transform: scale(0.98);
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            h1 {
                font-size: 24px;
            }
            .prescription-details h3, form h3 {
                font-size: 18px;
            }
            table th, table td {
                font-size: 14px;
                padding: 10px;
            }
            .add-row-btn, button[type="submit"] {
                font-size: 16px;
                padding: 12px 20px;
            }
        }
    </style>
    <script>
        // JavaScript to dynamically add rows to the medications table
        function addRow() {
            var table = document.getElementById('medications-table');
            var row = table.insertRow(-1);
            row.innerHTML = `
                <td><input type="text" name="medications[]" required></td>
                <td><input type="number" name="quantities[]" min="1" required></td>
                <td><input type="number" name="prices[]" step="0.01" min="0" required></td>
            `;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Process Prescription for <?php echo htmlspecialchars($prescription['patient_name']); ?></h1>
        <div class="prescription-details">
            <h3>Prescription Details:</h3>
            <?php if ($prescription['typed_prescription']): ?>
                <p><?php echo nl2br(htmlspecialchars($prescription['typed_prescription'])); ?></p>
            <?php endif; ?>
            <?php if ($prescription['prescription_image']): ?>
                <h3>Prescription Image:</h3>
                <img src="<?php echo htmlspecialchars($prescription['prescription_image']); ?>" alt="Prescription Image">
            <?php endif; ?>
        </div>
        <form method="POST">
            <h3>Enter Medication Details:</h3>
            <table id="medications-table">
                <tr>
                    <th>Medication Name</th>
                    <th>Quantity</th>
                    <th>Price per Unit</th>
                </tr>
                <tr>
                    <td><input type="text" name="medications[]" required></td>
                    <td><input type="number" name="quantities[]" min="1" required></td>
                    <td><input type="number" name="prices[]" step="0.01" min="0" required></td>
                </tr>
            </table>
            <button type="button" class="add-row-btn" onclick="addRow()">Add More Medications</button>
            <button type="submit">Generate Bill</button>
        </form>
    </div>
</body>
</html>
