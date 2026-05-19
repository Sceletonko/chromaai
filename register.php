<?php
session_start();
require_once 'db.php';
require_once 'mail_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (empty($name)) {
        $error = "Name is required.";
    } else {
        try {
            $pdo = get_db_connection();
        } catch (Exception $e) {
            $error = "Database connection error: " . $e->getMessage();
        }
        
        if (isset($pdo)) {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_code) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hashed_password, $verification_code])) {
                    if (send_verification_email($email, $verification_code)) {
                        $_SESSION['verify_email'] = $email;
                        header("Location: verify.php");
                        exit();
                    } else {
                        $success = "Registered, but failed to send email. Code is: $verification_code (Debug mode)";
                        // In a real app, you'd handle this better.
                        $_SESSION['verify_email'] = $email;
                        // For now, let's still redirect to verify so they can at least see it works if we show the code for debug
                        header("Location: verify.php?debug_code=$verification_code");
                        exit();
                    }
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ChromaAi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-purple: #9b30ff;
            --dark-purple: #7a1fd1;
            --light-purple: #f3e8ff;
            --light-bg: #fdfaff;
        }
        body {
            background-color: var(--light-bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(155, 48, 255, 0.1);
        }
        .btn-primary {
            background-color: var(--primary-purple);
            border: none;
            padding: 12px;
            border-radius: 10px;
        }
        .btn-primary:hover {
            background-color: var(--dark-purple);
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        .form-control:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 0.25rem rgba(155, 48, 255, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            height: 40px;
        }
    </style>
</head>
<body>
    <div class="auth-card shadow">
        <div class="logo">
            <img src="logo.png" alt="ChromaAi">
            <h4 class="mt-3 fw-bold">Create Account</h4>
            <p class="text-muted small">Join ChromaAi today</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Sign Up</button>
        </form>
        
        <div class="text-center mt-4">
            <p class="small text-muted">Already have an account? <a href="login.php" class="text-decoration-none" style="color: var(--primary-purple)">Sign In</a></p>
        </div>
    </div>
</body>
</html>
