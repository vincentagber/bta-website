<?php
/**
 * Africa Broadcasting Academy - Application Processor
 * File: application.php
 * FULL CONTENT VERSION - Covers all 7 sections of the form
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// GLOBAL SETTINGS
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

define('SMTP_HOST', 'mail.africabroadcastingacademy.com');
define('SMTP_USER', 'noreply@africabroadcastingacademy.com');
define('SMTP_PASS', ')oHsH6GEgGHNs[9Q'); 
define('SMTP_PORT', 465);

function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // HONEYPOT
    if (!empty($_POST['website'])) { exit("Unauthorized Access"); }

    // 1. DATA EXTRACTION (Matching your form exactly)
    $fullName       = clean_input($_POST['fullName'] ?? '');
    $dob            = clean_input($_POST['dob'] ?? '');
    $gender         = clean_input($_POST['gender'] ?? '');
    $nationality    = clean_input($_POST['nationality'] ?? '');
    $phone          = clean_input($_POST['phone'] ?? '');
    $email          = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $address        = clean_input($_POST['address'] ?? '');
    
    // Course Selection (Checkboxes)
    $courses        = isset($_POST['courses']) ? implode(', ', array_map('clean_input', (array)$_POST['courses'])) : 'None selected';
    
    $aboutYou       = clean_input($_POST['aboutYou'] ?? '');
    $mediaExp       = clean_input($_POST['mediaExp'] ?? '');
    
    // Educational Background
    $educationLevel = clean_input($_POST['educationLevel'] ?? '');
    $fieldOfStudy   = clean_input($_POST['fieldOfStudy'] ?? '');
    $institution    = clean_input($_POST['institution'] ?? '');
    $graduationYear = clean_input($_POST['graduationYear'] ?? '');
    
    $expectations   = clean_input($_POST['expectations'] ?? '');
    $additional     = clean_input($_POST['additional'] ?? '');
    $declaration    = isset($_POST['declaration']) ? 'Yes' : 'No';

    // SMTP INITIALIZATION
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPKeepAlive = true; 

        // --- EMAIL 1: COMPLETE ADMIN REPORT ---
        $mail->setFrom(SMTP_USER, 'ABA Admissions');
        $mail->addAddress('info@africabroadcastingacademy.com');
        $mail->addAddress('samson.a@africabroadcastingacademy.com');
        $mail->addReplyTo($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = "New Student Application: {$fullName}";
        
        $adminBody = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='background: #00B7EB; color: white; padding: 10px;'>Section 1: Personal Information</h2>
                <p><strong>Full Name:</strong> {$fullName}<br>
                <strong>Date of Birth:</strong> {$dob}<br>
                <strong>Gender:</strong> {$gender}<br>
                <strong>Nationality:</strong> {$nationality}<br>
                <strong>Phone:</strong> {$phone}<br>
                <strong>Email:</strong> {$email}<br>
                <strong>Residential Address:</strong> {$address}</p>

                <h2 style='background: #00B7EB; color: white; padding: 10px;'>Section 2: Course Selection</h2>
                <p><strong>Selected Courses:</strong> {$courses}</p>

                <h2 style='background: #00B7EB; color: white; padding: 10px;'>Section 3 & 4: Profile & Experience</h2>
                <p><strong>About You:</strong><br>" . nl2br($aboutYou) . "</p>
                <p><strong>Media Experience:</strong><br>" . nl2br($mediaExp) . "</p>

                <h2 style='background: #00B7EB; color: white; padding: 10px;'>Section 5: Educational Background</h2>
                <p><strong>Level:</strong> {$educationLevel}<br>
                <strong>Field:</strong> {$fieldOfStudy}<br>
                <strong>Institution:</strong> {$institution}<br>
                <strong>Graduation Year:</strong> {$graduationYear}</p>

                <h2 style='background: #00B7EB; color: white; padding: 10px;'>Section 6 & 7: Expectations & Declaration</h2>
                <p><strong>Goals:</strong><br>" . nl2br($expectations) . "</p>
                <p><strong>Additional Info:</strong><br>" . nl2br($additional) . "</p>
                <p><strong>Declaration Accepted:</strong> {$declaration}</p>
            </div>";

        $mail->Body = $adminBody;
        $mail->send();

        // --- EMAIL 2: APPLICANT AUTO-REPLY ---
        $mail->clearAddresses();
        $mail->clearReplyTos(); // Critical: Ensure user doesn't reply to themselves
        
        $mail->addAddress($email, $fullName);
        $mail->addReplyTo('info@africabroadcastingacademy.com', 'African Broadcasting Academy');
        
        $mail->Subject = "🎬 Application Secured - Africa Broadcasting Academy";
        
        $mail->Body = "
            <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; border: 1px solid #eee; border-radius: 15px; overflow: hidden;'>
                <div style='background: #000; padding: 30px; text-align: center;'>
                    <img src='https://www.africabroadcastingacademy.com/assets/images/logo.png' width='140' alt='ABA Logo'>
                </div>
                <div style='padding: 40px; text-align: center;'>
                    <div style='color: #00B7EB; font-size: 50px; margin-bottom: 10px;'>✓</div>
                    <h1 style='color: #333; margin: 0;'>Application Secured</h1>
                    <p style='color: #666; font-size: 16px; margin-top: 15px;'>Thank you for choosing the <strong>Africa Broadcasting Academy</strong>. Your authentic narrative is now in motion.</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>
                    <p style='text-align: left;'>Hello {$fullName}, we have successfully received your application for the <strong>Waterfront Content Creators Bootcamp</strong> (19–31 Jan 2026). Our team is now reviewing your profile.</p>
                    <a href='https://africabroadcastingacademy.com' style='display: inline-block; background: #00B7EB; color: #fff; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px;'>Explore Academy Insights</a>
                </div>
            </div>";
        
        $mail->send();

        header("Location: thank-you.html");
        exit;

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        header("Location: applicationform.html?error=failed");
        exit;
    }
}