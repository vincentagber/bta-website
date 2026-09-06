<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check honeypot
    if (!empty($_POST['_honey'])) {
        exit("Unauthorized Access");
    }

    $name    = htmlspecialchars($_POST['name'] ?? 'No Name');
    $email   = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message'] ?? 'No Message');

    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid input.";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.africabroadcastingacademy.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@africabroadcastingacademy.com';
        $mail->Password   = ')oHsH6GEgGHNs[9Q'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('noreply@africabroadcastingacademy.com', 'ABA Sponsorship Inquiry');
        $mail->addAddress('sponsor@africabroadcastingacademy.com');
        $mail->addAddress('info@africabroadcastingacademy.com');
        $mail->addAddress('samson.a@africabroadcastingacademy.com');
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = "New Sponsorship Inquiry from $name";
        $mail->Body    = "Organization/Name: $name\nEmail: $email\n\nMessage:\n$message";

        $mail->send();
        header("Location: thank-you.html");
        exit;
    } catch (Exception $e) {
        error_log("PHPMailer Error (Sponsor): " . $mail->ErrorInfo);
        echo "An error occurred while sending your inquiry. Please try again later.";
    }
}
?>
