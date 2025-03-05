<?php
session_start();

if ($_SESSION['role'] != 'doctor') {
    header("Location: unauthorized.php");
    exit();
}

include 'config.php';

$appointment_id = $_GET['appointment_id'] ?? null;

if (!$appointment_id || !is_numeric($appointment_id)) {
    echo "Invalid appointment ID.";
    exit();
}

$doctor_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    echo "Doctor not found.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.*, p.full_name AS patient_name, p.id AS patient_id
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.id = ? AND a.doctor_id = ?
");
$stmt->execute([$appointment_id, $doctor['id']]);
$appointment = $stmt->fetch();

if (!$appointment) {
    echo "Appointment not found or you do not have permission to access it.";
    exit();
}

$errors = [];
$typed_prescription = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $typed_prescription = $_POST['typed_prescription'] ?? '';
    $image_file = $_FILES['image_file'] ?? null;
    $prescription_image = null;

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
        $stmt = $pdo->prepare("
            INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, typed_prescription, prescription_image)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$appointment_id, $appointment['patient_id'], $doctor['id'], $typed_prescription, $prescription_image]);

        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?");
        $stmt->execute([$appointment_id]);

        echo "<script>alert('Prescription successfully submitted.'); window.location.href = 'doctor_portal.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Prescription</title>
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

        h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            animation: slideDown 0.8s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        form label {
            display: block;
            font-size: 16px;
            color: #34495e;
            margin-bottom: 8px;
        }

        form textarea {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccd1d9;
            resize: vertical;
            min-height: 150px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s;
        }

        form textarea:focus {
            border-color: #3498db;
        }

        form input[type="file"] {
            font-size: 16px;
            margin-bottom: 20px;
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

        .errors p {
            color: #e74c3c;
            background-color: #fceae9;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 25px;
            }

            h2 {
                font-size: 24px;
            }

            form label {
                font-size: 14px;
            }

            form textarea {
                font-size: 14px;
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
        <h2>Add Prescription for <?php echo htmlspecialchars($appointment['patient_name']); ?></h2>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="typed_prescription">Prescription:</label>
            <textarea name="typed_prescription" id="typed_prescription"><?php echo htmlspecialchars($typed_prescription); ?></textarea>

            <label for="image_file">Upload Prescription Image:</label>
            <input type="file" name="image_file" id="image_file">

            <button type="submit">Submit Prescription</button>
        </form>
    </div>
</body>
</html>
