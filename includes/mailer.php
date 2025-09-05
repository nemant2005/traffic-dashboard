<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure you have PHPMailer via Composer

function sendAlertEmail($subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST;       // e.g. smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_USERNAME;   // e.g. your Gmail
        $mail->Password   = EMAIL_PASSWORD;   // App password or real one
        $mail->SMTPSecure = 'tls';            // Encryption - tls or ssl
        $mail->Port       = EMAIL_PORT;       // 587 for tls, 465 for ssl

        // Recipients
        $mail->setFrom(EMAIL_USERNAME, 'Traffic Alert System');
        $mail->addAddress(ALERT_EMAIL); // Receiver email

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Plain text fallback

        $mail->send();
        error_log('Message has been sent');
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
