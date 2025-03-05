<?php
session_start();

include 'config.php'; // Include database connection
require('C:\Program Files\Ampps\www\healthcare_project\fpdf186\fpdf.php'); // Include FPDF library

$bill_id = $_GET['bill_id'] ?? null;

if (!$bill_id || !is_numeric($bill_id)) {
    echo "Invalid bill ID.";
    exit();
}

// Fetch bill details
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

// Fetch bill items
$stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = ?");
$stmt->execute([$bill_id]);
$bill_items = $stmt->fetchAll();

// Check if the user has permission to view the invoice
$user_role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if ($user_role == 'pharmacist') {
    // Pharmacist can view all invoices
} elseif ($user_role == 'patient') {
    // Ensure the patient can only view their own invoices
    // Fetch patient's user ID
    $stmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
    $stmt->execute([$bill['patient_id']]);
    $patient_user = $stmt->fetch();

    if (!$patient_user || $patient_user['user_id'] != $user_id) {
        echo "You do not have permission to download this invoice.";
        exit();
    }
} else {
    // Other users are not allowed
    echo "You do not have permission to download this invoice.";
    exit();
}

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();

// Invoice Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Invoice #' . $bill['id'], 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Date: ' . date('d-m-Y', strtotime($bill['created_at'])), 0, 1, 'C');
$pdf->Ln(10);

// Patient Details
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Patient Details:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Name: ' . $bill['patient_name'], 0, 1);
$pdf->Cell(0, 8, 'Date of Birth: ' . $bill['date_of_birth'], 0, 1);
$pdf->Cell(0, 8, 'Phone: ' . $bill['phone'], 0, 1);
$pdf->Cell(0, 8, 'City: ' . $bill['city'], 0, 1);
$pdf->Ln(10);

// Invoice Details
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Invoice Details:', 0, 1);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Medication Name', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(40, 10, 'Price per Unit', 1);
$pdf->Cell(40, 10, 'Line Total', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($bill_items as $item) {
    $pdf->Cell(80, 10, $item['medication_name'], 1);
    $pdf->Cell(30, 10, $item['quantity'], 1);
    $pdf->Cell(40, 10, number_format($item['price'], 2), 1);
    $pdf->Cell(40, 10, number_format($item['quantity'] * $item['price'], 2), 1);
    $pdf->Ln();
}

// Total Amount
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'Total Amount', 1);
$pdf->Cell(40, 10, '$' . number_format($bill['total_amount'], 2), 1);

$pdf->Output('D', 'Invoice_' . $bill['id'] . '.pdf');
exit();
?>
