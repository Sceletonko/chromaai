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

$error = '';
$email = $_SESSION['verify_email'] ?? '';
$debug_code = $_GET['debug_code'] ?? '';

if (!$email) {
    header("Location: register.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    try {
        $pdo = get_db_connection();
        
        $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE email = ? AND verification_code = ?");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch();
        
        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'] ?? 'User';
            unset($_SESSION['verify_email']);
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid verification code.";
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
    <title>Verify Email - ChromaAi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #9b30ff;
            --light-bg: #fdfaff;
        }
        body { background-color: var(--light-bg); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 400px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(155, 48, 255, 0.1); }
        .btn-primary { background-color: var(--primary-purple); border: none; padding: 12px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="auth-card shadow text-center">
        <h4 class="fw-bold">Verify Your Email</h4>
        <p class="text-muted small">We sent a 6-digit code to <b><?php echo htmlspecialchars($email); ?></b></p>
        
        <?php if ($debug_code): ?>
            <div class="alert alert-info small">Debug Mode: Your code is <b><?php echo $debug_code; ?></b></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" name="code" class="form-control text-center fw-bold fs-4" placeholder="000000" maxlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Verify & Continue</button>
        </form>
        <p class="mt-3 small text-muted">Didn't get the code? <a href="#" class="text-decoration-none" style="color: var(--primary-purple)">Resend</a></p>
    </div>
</body>
</html>
