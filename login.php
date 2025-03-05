<?php
session_start();
include 'config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'patient') {
                header("Location: patient_portal.php");
            } elseif ($user['role'] == 'doctor') {
                header("Location: doctor_portal.php");
            } else {
                $error_message = 'Invalid user role.';
            }
            exit();
        } else {
            $error_message = 'Incorrect password. Please try again.';
        }
    } else {
        $error_message = 'No user found with this email and role.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor/Patient Login</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
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
            color: #ffffff;
            overflow: hidden;
        }

        /* Container */
        .form-structor {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1f1f1f 25%, #2c2c2c 100%);
        }

        /* Login Form */
        .form-structor .login {
            position: absolute;
            width: 350px;
            padding: 40px;
            background-color: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            opacity: 1;
            visibility: visible;
            transition: all 0.5s ease;
            z-index: 5;
        }

        /* Register Link */
        .form-structor .register-link {
            margin-top: 20px;
            text-align: center;
        }

        .form-structor .register-link a {
            color: #1abc9c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-structor .register-link a:hover {
            color: #16a085;
        }

        /* Form Title */
        .form-structor h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #1abc9c;
            animation: fadeInDown 0.5s ease;
        }

        /* Error Message */
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 15px;
            font-size: 16px;
        }

        /* Form Elements */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-size: 16px;
            color: #ecf0f1;
        }

        input[type="email"], input[type="password"], select {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #34495e;
            background-color: #2c3e50;
            color: #ecf0f1;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s, background-color 0.3s;
        }

        input:focus, select:focus {
            border-color: #1abc9c;
            background-color: #34495e;
        }

        button {
            padding: 14px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
        }

        button:hover {
            background-color: #16a085;
        }

        button:active {
            transform: scale(0.98);
        }

        /* Animations */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .form-structor .login {
                width: 90%;
                padding: 30px 20px;
            }

            .form-structor h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="form-structor">
        <div class="login">
            <h1>Doctor/Patient Login</h1>

            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required autofocus>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <label for="role">Login as:</label>
                <select name="role" id="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>

                <button type="submit">Login</button>
            </form>

            <div class="register-link">
                <p>Don't have an account?</p>
                <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>
