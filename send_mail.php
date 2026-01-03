<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['contact-name'] ?? 'No Name');
    $email   = htmlspecialchars($_POST['contact-email'] ?? 'No Email');
    $phone   = htmlspecialchars($_POST['contact-phone'] ?? 'No Phone');
    $message = htmlspecialchars($_POST['message'] ?? 'No Message');

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.africabroadcastingacademy.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@africabroadcastingacademy.com';
        $mail->Password   = ')oHsH6GEgGHNs[9Q'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('noreply@africabroadcastingacademy.com', 'ABA Website Contact');
        $mail->addAddress('info@africabroadcastingacademy.com');
        $mail->addAddress('samson.a@africabroadcastingacademy.com');
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = "New Contact Form Submission from $name";
        $mail->Body    = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";

        $mail->send();
        header("Location: thank-you.html");
        exit;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        echo "error";
    }
}
?>