<?php
session_start();
include 'config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $full_name = trim($full_name);
    $email = trim($email);
    $password = trim($password);
    $confirm_password = trim($confirm_password);
    $phone = trim($phone);

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = 'An account with this email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashed_password, 'pharmacist']);
            $user_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO pharmacists (user_id, full_name, phone, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $full_name, $phone, $email]);

            $success_message = 'Registration successful. You can now log in.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pharmacist Registration</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- CSS Styles -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-image: linear-gradient(135deg, #f0f4f8 25%, #dfe9f3 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }
        .register-container {
            background-color: #ffffff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
            animation: fadeIn 0.8s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #2c3e50;
            animation: slideDown 0.8s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-size: 16px;
            color: #34495e;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccd1d9;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #3498db;
        }
        button {
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
        button:hover {
            background-color: #2980b9;
        }
        button:active {
            transform: scale(0.98);
        }
        .error-message, .success-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .success-message {
            color: #2ecc71;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        .login-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .register-container {
                width: 90%;
                padding: 30px 20px;
            }
            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <h1>Pharmacist Registration</h1>

    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <form method="POST" action="register_pharmacy.php">
        <label for="full_name">Full Name:</label>
        <input type="text" name="full_name" id="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>

        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" id="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <!-- Login Link -->
    <div class="login-link">
        <p>Already have an account?</p>
        <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>
