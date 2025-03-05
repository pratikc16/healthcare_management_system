<?php
// update_medical_history.php

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['patient_id'], $_POST['new_notes'])) {
    $patient_id = $_POST['patient_id'];
    $new_notes = $_POST['new_notes'];

    // Update the medical history for the selected patient
    $stmt = $pdo->prepare("UPDATE patients SET medical_history = :new_notes WHERE id = :patient_id");
    $stmt->execute([
        ':new_notes' => $new_notes,
        ':patient_id' => $patient_id,
    ]);

    // Update the appointment status to 'Completed'
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE patient_id = :patient_id AND status = 'pending'");
    $stmt->execute([
        ':patient_id' => $patient_id,
    ]);

    echo "<script>alert('Medical history updated and appointment status changed to Completed.'); window.location.href='doctor_portal.php';</script>";
}
?>
