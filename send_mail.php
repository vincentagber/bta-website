<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['contact-name']);
    $email   = htmlspecialchars($_POST['contact-email']);
    $phone   = htmlspecialchars($_POST['contact-phone']);
    $message = htmlspecialchars($_POST['message']);

    $to      = "info@africabroadcastingacademy.com, samson.a@africabroadcastingacademy.com";
    $subject = "New Contact Form Submission from $name";
    $body    = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
    $headers = "From: $email\r\nReply-To: $email\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>