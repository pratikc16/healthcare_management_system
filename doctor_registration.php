<?php
// Start the session
session_start();

// Include the database configuration file
include 'config.php';

// Initialize an array to store error messages
$errors = [];

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize user inputs
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $department = trim($_POST['department']);
    $specialty  = trim($_POST['specialty']);

    // Handle profile image upload (optional)
    $image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading image.";
        } elseif (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $errors[] = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
        } elseif ($_FILES['profile_image']['size'] > $max_size) {
            $errors[] = "Image size exceeds 2MB.";
        } else {
            // Generate a unique file name
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('doctor_', true) . '.' . $ext;
            $target_dir = 'uploads/doctors/';
            $target_file = $target_dir . $new_filename;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $errors[] = "Failed to move uploaded image.";
            }
        }
    }

    // Input Validation
    if (empty($full_name)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (empty($department)) {
        $errors[] = "Department is required.";
    }

    if (empty($specialty)) {
        $errors[] = "Specialty is required.";
    }

    // Proceed if there are no validation errors
    if (empty($errors)) {
        try {
            // Check if the email is already registered
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "An account with this email already exists.";
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Begin a transaction
                $pdo->beginTransaction();

                // Insert into the users table
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'doctor')");
                $stmt->execute([$full_name, $email, $hashed_password]);

                // Get the last inserted user ID
                $user_id = $pdo->lastInsertId();

                // Insert into the doctors table with image_path
                $stmt = $pdo->prepare("INSERT INTO doctors (user_id, full_name, department, specialty, image_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $full_name, $department, $specialty, $image_path]);

                // Commit the transaction
                $pdo->commit();

                // Redirect to the login page with a success message
                echo "<script>alert('Doctor registered successfully! Please log in.'); window.location.href='login.php';</script>";
                exit();
            }
        } catch (PDOException $e) {
            // Rollback the transaction if something failed
            $pdo->rollBack();
            $errors[] = "An error occurred during registration. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor Registration</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- CSS Styles -->
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
            max-width: 500px;
            width: 90%;
            padding: 30px 40px;
            background-color: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease;
            position: relative;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            text-align: center;
            color: #1abc9c;
            margin-bottom: 30px;
            font-size: 28px;
            animation: slideDown 0.8s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 i {
            margin-right: 10px;
        }
        .error-messages p {
            color: #e74c3c;
            background-color: #2c1f1f;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #bdc3c7;
            display: flex;
            align-items: center;
        }
        form label i {
            margin-right: 5px;
            color: #1abc9c;
        }
        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form select,
        form input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #34495e;
            font-size: 16px;
            outline: none;
            background-color: #2c3e50;
            color: #ecf0f1;
            transition: border-color 0.3s, background-color 0.3s;
        }
        form input[type="text"]:focus,
        form input[type="email"]:focus,
        form input[type="password"]:focus,
        form select:focus,
        form input[type="file"]:focus {
            border-color: #1abc9c;
            background-color: #34495e;
        }
        form button {
            width: 100%;
            padding: 14px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form button i {
            margin-right: 5px;
        }
        form button:hover {
            background-color: #2980b9;
        }
        form button:active {
            transform: scale(0.98);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #1abc9c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-link a i {
            margin-right: 5px;
        }
        .login-link a:hover {
            color: #16a085;
            text-decoration: underline;
        }
        /* Responsive Design */
        @media (max-width: 480px) {
            .container {
                padding: 20px 25px;
            }
            h1 {
                font-size: 24px;
            }
            form label {
                font-size: 14px;
            }
            form input[type="text"],
            form input[type="email"],
            form input[type="password"],
            form select,
            form input[type="file"] {
                font-size: 14px;
            }
            form button {
                font-size: 16px;
            }
            .login-link a {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-user-plus"></i> Register as a Doctor</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register_doctor.php" enctype="multipart/form-data">
            <label for="full_name"><i class="fas fa-id-badge"></i> Full Name:</label>
            <input type="text" name="full_name" id="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>

            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
            <input type="email" name="email" id="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>

            <label for="password"><i class="fas fa-lock"></i> Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="department"><i class="fas fa-building"></i> Department:</label>
            <select name="department" id="department" required>
                <option value="" disabled selected>Select Department</option>
                <option value="Cardiology" <?php echo (isset($department) && $department == 'Cardiology') ? 'selected' : ''; ?>>Cardiology</option>
                <option value="Neurology" <?php echo (isset($department) && $department == 'Neurology') ? 'selected' : ''; ?>>Neurology</option>
                <option value="Oncology" <?php echo (isset($department) && $department == 'Oncology') ? 'selected' : ''; ?>>Oncology</option>
                <option value="Pediatrics" <?php echo (isset($department) && $department == 'Pediatrics') ? 'selected' : ''; ?>>Pediatrics</option>
                <!-- Add more departments as needed -->
            </select>

            <label for="specialty"><i class="fas fa-stethoscope"></i> Specialty:</label>
            <input type="text" name="specialty" id="specialty" value="<?php echo isset($specialty) ? htmlspecialchars($specialty) : ''; ?>" required>

            <label for="profile_image"><i class="fas fa-image"></i> Profile Image (Optional):label>
            <input type="file" name="profile_image" id="profile_image" accept=".jpg, .jpeg, .png, .gif">

            <button type="submit"><i class="fas fa-user-check"></i> Register Doctor</button>
        </form>

        <div class="login-link">
            <p>Already have an account?</p>
            <a href="login.php"><i class="fas fa-arrow-left"></i> Login here</a>
        </div>
    </div>
</body>
</html>
