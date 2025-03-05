<?php
session_start();
include 'config.php';

// If the user is already logged in as a pharmacist, redirect to the pharmacy portal
if (isset($_SESSION['role']) && $_SESSION['role'] === 'pharmacist') {
    header("Location: pharmacy_portal.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Input validation
    if (empty($email)) {
        $error_message = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (empty($password)) {
        $error_message = 'Please enter your password.';
    } else {
        // Prepare and execute the SQL statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'pharmacist'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct; set session variables
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Redirect to the pharmacy portal
                header("Location: pharmacy_portal.php");
                exit();
            } else {
                // Incorrect password
                $error_message = 'Incorrect password. Please try again.';
            }
        } else {
            // No user found with the provided email and role
            $error_message = 'No pharmacy user found with this email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacy Login</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- CSS Styles -->
    <style>
        /* Reset and Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #ecf0f1;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Container */
        .login-container {
            background-color: #1e1e1e;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            width: 400px;
            animation: fadeIn 0.8s ease;
            position: relative;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .login-container h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #1abc9c;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: slideDown 0.8s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h1 i {
            margin-right: 10px;
        }

        /* Error Message */
        .error-message {
            background-color: #2c1f1f;
            color: #e74c3c;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 16px;
            text-align: center;
            animation: fadeIn 1s ease;
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-size: 16px;
            color: #bdc3c7;
            display: flex;
            align-items: center;
        }

        label i {
            margin-right: 5px;
            color: #1abc9c;
        }

        input[type="email"],
        input[type="password"] {
            padding: 14px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #34495e;
            font-size: 16px;
            outline: none;
            background-color: #2c3e50;
            color: #ecf0f1;
            transition: border-color 0.3s, background-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #1abc9c;
            background-color: #34495e;
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
            display: flex;
            justify-content: center;
            align-items: center;
        }

        button i {
            margin-right: 5px;
        }

        button:hover {
            background-color: #2980b9;
        }

        button:active {
            transform: scale(0.98);
        }

        /* Back Link */
        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #1abc9c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .back-link a i {
            margin-right: 5px;
        }

        .back-link a:hover {
            color: #16a085;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                width: 90%;
            }

            h1 {
                font-size: 24px;
            }

            button {
                font-size: 16px;
            }

            .back-link a {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1><i class="fas fa-pills"></i> Pharmacy Login</h1>

    <?php if ($error_message): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="pharmacy_login.php">
        <label for="email"><i class="fas fa-envelope"></i> Email:</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required autofocus>

        <label for="password"><i class="fas fa-lock"></i> Password:</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required>

        <button type="submit"><i class="fas fa-sign-in-alt"></i> Login</button>
    </form>

    <div class="back-link">
        <a href="index.php"><i class="fas fa-home"></i> Back to Main Page</a>
    </div>
</div>

</body>
</html>
