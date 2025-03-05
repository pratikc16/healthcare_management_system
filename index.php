<?php
session_start();
include 'config.php';

$error_message = '';
$registration_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // Handle Login
        $email = trim($_POST['login_email']);
        $password = trim($_POST['login_password']);
        $role = trim($_POST['login_role']);

        // Input Validation
        if (empty($email)) {
            $error_message = 'Please enter your email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } elseif (empty($password)) {
            $error_message = 'Please enter your password.';
        } elseif (empty($role) || !in_array($role, ['doctor', 'patient'])) {
            $error_message = 'Please select a valid role.';
        } else {
            // Prepare and execute the SQL statement to prevent SQL injection
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
            $stmt->execute([$email, $role]);
            $user = $stmt->fetch();

            if ($user) {
                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Password is correct; set session variables
                    session_regenerate_id(true); // Prevent session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] == 'patient') {
                        header("Location: patient_portal.php");
                    } elseif ($user['role'] == 'doctor') {
                        header("Location: doctor_portal.php");
                    }
                    exit();
                } else {
                    // Incorrect password
                    $error_message = 'Incorrect password. Please try again.';
                }
            } else {
                // No user found with the provided email and role
                $error_message = 'No user found with this email and role.';
            }
        }
    }

    if (isset($_POST['register'])) {
        // Handle Register
        $role = $_POST['register_role'];

        if (!in_array($role, ['doctor', 'patient'])) {
            $registration_errors[] = "Invalid role selected.";
        }

        if (empty($registration_errors)) {
            if ($role == 'doctor') {
                header("Location: doctor_registration.php");
                exit();
            } elseif ($role == 'patient') {
                header("Location: register_patient.php");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor/Patient Login & Register</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHq6xL6mE6R1GqXTV6js6L9dk9Vl4L7o5xT8rjx6zlvPBRrfs0fHc5jIpVHK7C5JkDgFn12Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        .form-structor {
            position: relative;
            width: 800px;
            max-width: 90%;
            height: 600px;
            background: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Forms */
        .form-container {
            width: 50%;
            padding: 60px 40px;
            transition: all 0.5s ease;
        }

        /* Hidden Form */
        .form-container.hidden {
            transform: translateX(100%);
            opacity: 0;
            visibility: hidden;
        }

        /* Active Form */
        .form-container.active {
            transform: translateX(0);
            opacity: 1;
            visibility: visible;
        }

        /* Form Titles */
        .form-container h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #1abc9c;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: slideInDown 0.5s ease;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container h1 i {
            margin-right: 10px;
        }

        /* Error Messages */
        .error-message, .registration-errors p {
            background-color: #2c1f1f;
            color: #e74c3c;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        /* Form Elements */
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

        input[type="email"], input[type="password"], select {
            padding: 14px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #34495e;
            background-color: #2c3e50;
            color: #ecf0f1;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s, background-color 0.3s;
        }

        input[type="email"]:focus, input[type="password"]:focus, select:focus {
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
            display: flex;
            justify-content: center;
            align-items: center;
        }

        button i {
            margin-right: 5px;
        }

        button:hover {
            background-color: #16a085;
        }

        button:active {
            transform: scale(0.98);
        }

        /* Toggle Links */
        .toggle-link {
            margin-top: 20px;
            text-align: center;
        }

        .toggle-link a {
            color: #1abc9c;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .toggle-link a:hover {
            color: #16a085;
        }

        .toggle-link a i {
            margin-right: 5px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .form-structor {
                flex-direction: column;
                height: auto;
            }

            .form-container {
                width: 100%;
                transform: translateX(0) !important;
                opacity: 1 !important;
                visibility: visible !important;
            }

            .form-container.hidden {
                transform: translateX(0);
                opacity: 0;
                visibility: hidden;
                height: 0;
            }
        }

        @media (max-width: 480px) {
            .form-structor {
                padding: 20px;
            }

            .form-container {
                padding: 40px 20px;
            }

            .form-container h1 {
                font-size: 24px;
            }

            label, input[type="email"], input[type="password"], select, button {
                font-size: 14px;
            }

            button {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="form-structor">
        <!-- Login Form -->
        <div class="form-container active" id="login-form">
            <h1><i class="fas fa-sign-in-alt"></i> Login</h1>

            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <label for="login_email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" name="login_email" id="login_email" placeholder="Enter your email" required autofocus>

                <label for="login_password"><i class="fas fa-lock"></i> Password:</label>
                <input type="password" name="login_password" id="login_password" placeholder="Enter your password" required>

                <label for="login_role"><i class="fas fa-user-tag"></i> Role:</label>
                <select name="login_role" id="login_role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>

                <button type="submit" name="login"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>

            <div class="toggle-link">
                <p>Don't have an account?</p>
                <a id="show-register"><i class="fas fa-user-plus"></i> Register here</a>
            </div>
        </div>

        <!-- Register Form -->
        <div class="form-container hidden" id="register-form">
            <h1><i class="fas fa-user-plus"></i> Register</h1>

            <?php if (!empty($registration_errors)): ?>
                <div class="registration-errors">
                    <?php foreach ($registration_errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <label for="register_role"><i class="fas fa-user-tag"></i> I am a:</label>
                <select name="register_role" id="register_role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>

                <button type="submit" name="register"><i class="fas fa-arrow-right"></i> Continue</button>
            </form>

            <div class="toggle-link">
                <a id="show-login"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        const showRegister = document.getElementById('show-register');
        const showLogin = document.getElementById('show-login');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        showRegister.addEventListener('click', () => {
            loginForm.classList.remove('active');
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            registerForm.classList.add('active');
        });

        showLogin.addEventListener('click', () => {
            registerForm.classList.remove('active');
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
            loginForm.classList.add('active');
        });
    </script>
</body>
</html>
