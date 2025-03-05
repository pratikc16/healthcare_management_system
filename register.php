<?php
include 'config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];

    if (!in_array($role, ['doctor', 'patient'])) {
        $errors[] = "Invalid role selected.";
    }

    if (empty($errors)) {
        if ($role == 'doctor') {
            header("Location: doctor_registration.php");
            exit();
        } elseif ($role == 'patient') {
            header("Location: register_patient.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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

        /* Register Form */
        .form-structor .register {
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

        /* Login Link */
        .form-structor .login-link {
            margin-top: 20px;
            text-align: center;
        }

        .form-structor .login-link a {
            color: #1abc9c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-structor .login-link a:hover {
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

        /* Error Messages */
        .error-messages p {
            color: #e74c3c;
            background-color: #2c1f1f;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
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

        select {
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

        select:focus {
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
            .form-structor .register {
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
        <div class="register">
            <h1>Register</h1>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <label for="role">I am a:</label>
                <select name="role" id="role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>

                <button type="submit">Next</button>
            </form>

            <div class="login-link">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
