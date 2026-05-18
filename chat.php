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
    <title>ChromaAi - Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <style>
        body { background-color: #fdfaff; color: #1a1a1a; font-family: 'Inter', sans-serif; }
        .container { margin-top: 100px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="fw-bold">Chat Interface</h1>
        <p class="text-muted">This is where the conversation will take place. (Placeholder)</p>
        <div class="alert alert-info d-inline-block">
            Logged in as: <b><?php echo htmlspecialchars($_SESSION['user_email']); ?></b>
        </div>
        <br><br>
        <a href="index.php" class="btn btn-primary" style="background-color: #9b30ff; border: none;">Back to Home</a>
    </div>
</body>
</html>
