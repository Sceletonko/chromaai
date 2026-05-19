<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<b>Error [$errno]:</b> $errstr in <b>$errfile</b> on line <b>$errline</b><br>";
});
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        echo "<b>Fatal Error:</b> " . $error['message'] . " in <b>" . $error['file'] . "</b> on line <b>" . $error['line'] . "</b><br>";
    }
});
session_start();
require_once 'db.php';

require_once 'mail_helper.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $pdo = get_db_connection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Even if verified, we send a code for sign-in verification
            $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE id = ?");
            $stmt->execute([$verification_code, $user['id']]);
            
            if (send_verification_email($email, $verification_code)) {
                $_SESSION['verify_email'] = $email;
                header("Location: verify.php");
                exit();
            } else {
                // Fallback for debug
                $_SESSION['verify_email'] = $email;
                header("Location: verify.php?debug_code=$verification_code");
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - ChromaAi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary-purple: #9b30ff; --light-bg: #fdfaff; }
        body { background-color: var(--light-bg); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .auth-card { width: 100%; max-width: 400px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(155, 48, 255, 0.1); }
        .btn-primary { background-color: var(--primary-purple); border: none; padding: 12px; border-radius: 10px; }
        .form-control { padding: 12px; border-radius: 10px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="auth-card shadow">
        <div class="text-center mb-4">
            <img src="logo.png" alt="ChromaAi" style="height: 40px;">
            <h4 class="mt-3 fw-bold">Welcome Back</h4>
            <p class="text-muted small">Sign in to your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Sign In</button>
        </form>
        
        <div class="text-center mt-4">
            <p class="small text-muted">Don't have an account? <a href="register.php" class="text-decoration-none" style="color: var(--primary-purple)">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
