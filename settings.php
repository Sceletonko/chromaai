<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - ChromaAi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <style>
        body { background-color: #fdfaff; color: #1a1a1a; font-family: 'Inter', sans-serif; }
        .container { margin-top: 50px; }
        .settings-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(155, 48, 255, 0.05); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="settings-card">
                    <h2 class="fw-bold mb-4">Account Settings</h2>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" disabled>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" disabled>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Status</label>
                        <div><span class="badge bg-success">Verified</span></div>
                    </div>
                    <hr>
                    <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
