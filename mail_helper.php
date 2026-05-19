<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// vendor is already handled by db.php which is required before this

function send_verification_email($to_email, $code) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("CRITICAL ERROR: PHPMailer class not found. The 'vendor' folder might be missing or incomplete on the server.");
        // If we are in debug mode or on local, maybe show more info
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = get_env_var('MAIL_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = get_env_var('MAIL_USERNAME');
        $mail->Password   = get_env_var('MAIL_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = get_env_var('MAIL_PORT', 587);

        // Recipients
        $mail->setFrom(get_env_var('MAIL_FROM'), get_env_var('MAIL_FROM_NAME'));
        $mail->addAddress($to_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'ChromaAi - Your Verification Code';
        $mail->Body    = "Your verification code is: <b>$code</b>";
        $mail->AltBody = "Your verification code is: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
