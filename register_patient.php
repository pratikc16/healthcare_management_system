<?php 
include 'config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input data
    $full_name      = trim($_POST['full_name']);
    $email          = trim($_POST['email']);
    $password       = trim($_POST['password']);
    $date_of_birth  = $_POST['date_of_birth'];
    $phone          = trim($_POST['phone']);
    $city           = trim($_POST['city']);
    
    // Handle Profile Image Upload
    $profile_image = null; // Initialize as null
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file_tmp_path = $_FILES['profile_image']['tmp_name'];
        $file_name = $_FILES['profile_image']['name'];
        $file_size = $_FILES['profile_image']['size'];
        $file_type = mime_content_type($file_tmp_path);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
        }
        
        // Validate file size
        if ($file_size > $max_size) {
            $errors[] = "Image size should not exceed 2MB.";
        }
        
        // If no errors, proceed to upload
        if (empty($errors)) {
            $upload_dir = 'uploads/patients/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate a unique file name to prevent overwriting
            $new_file_name = uniqid('patient_', true) . '.' . $file_ext;
            $dest_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $profile_image = $dest_path;
            } else {
                $errors[] = "There was an error uploading your profile image. Please try again.";
            }
        }
    }
    
    // Validate input fields
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (empty($date_of_birth)) {
        $errors[] = "Date of birth is required.";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }

    if (empty($city)) {
        $errors[] = "City is required.";
    }

    if (empty($errors)) {
        try {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into users table
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'patient')");
            $stmt->execute([$full_name, $email, $hashed_password]);

            // Get the user_id of the newly inserted user
            $user_id = $pdo->lastInsertId();

            // Insert into patients table with the profile image path
            $stmt = $pdo->prepare("INSERT INTO patients (user_id, full_name, date_of_birth, phone, city, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $full_name, $date_of_birth, $phone, $city, $profile_image]);

            echo "<script>alert('Patient registered successfully!'); window.location.href='login.php';</script>";
            exit();
        } catch (PDOException $e) {
            // Handle duplicate email error
            if ($e->getCode() == 23000) {
                $errors[] = "Email already in use. Please use a different email.";
            } else {
                $errors[] = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register Patient</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- CSS Styles -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #ecf0f1;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(135deg, #1e1e1e 25%, #2c3e50 100%);
        }
        .container {
            max-width: 500px;
            width: 90%;
            padding: 30px 40px;
            background-color: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            text-align: center;
            color: #1abc9c;
            margin-bottom: 30px;
            font-size: 28px;
            animation: slideDown 0.8s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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
            color: #ecf0f1;
            position: relative;
        }
        form label i {
            position: absolute;
            left: -30px;
            top: 50%;
            transform: translateY(-50%);
            color: #1abc9c;
        }
        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form input[type="date"],
        form input[type="file"] {
            width: 100%;
            padding: 12px 15px;
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
        form input[type="date"]:focus,
        form input[type="file"]:focus {
            border-color: #1abc9c;
            background-color: #34495e;
        }
        form button {
            width: 100%;
            padding: 14px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        form button i {
            margin-right: 8px;
        }
        form button:hover {
            background-color: #16a085;
        }
        form button:active {
            transform: scale(0.98);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #1abc9c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        .back-link a:hover {
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
            form label i {
                left: -25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-user-plus"></i> Register Patient</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register_patient.php" enctype="multipart/form-data">
            <label for="full_name"><i class="fas fa-user"></i> Full Name:</label>
            <input type="text" name="full_name" id="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>

            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
            <input type="email" name="email" id="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>

            <label for="password"><i class="fas fa-lock"></i> Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="date_of_birth"><i class="fas fa-birthday-cake"></i> Date of Birth:</label>
            <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo isset($date_of_birth) ? htmlspecialchars($date_of_birth) : ''; ?>" required>

            <label for="phone"><i class="fas fa-phone"></i> Phone:</label>
            <input type="text" name="phone" id="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>

            <label for="city"><i class="fas fa-city"></i> City:</label>
            <input type="text" name="city" id="city" value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>" required>

            <label for="profile_image"><i class="fas fa-image"></i> Profile Image (Optional):</label>
            <input type="file" name="profile_image" id="profile_image" accept="image/*">

            <button type="submit"><i class="fas fa-user-plus"></i> Register Patient</button>
        </form>

        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</body>
</html>
