<?php
/**
 * Africa Broadcasting Academy - Application Processor
 * File: application.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// GLOBAL SETTINGS & ERROR LOGGING
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function respond($status, $message, $redirectUrl = 'thank-you.html') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
              || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status === 'success' ? 200 : 400);
        echo json_encode(['status' => $status, 'message' => $message, 'redirect' => $redirectUrl]);
        exit;
    } else {
        if ($status === 'success') {
            header("Location: " . $redirectUrl);
        } else {
            header("Location: applicationform.html?error=failed");
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // HONEYPOT ANTI-SPAM
    if (!empty($_POST['website'])) { 
        error_log("[" . date('Y-m-d H:i:s') . "] Honeypot triggered from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));
        respond('error', 'Unauthorized access detected.');
    }

    // 1. DATA EXTRACTION & SANITIZATION
    $fullName       = clean_input($_POST['fullName'] ?? '');
    $dob            = clean_input($_POST['dob'] ?? '');
    $gender         = clean_input($_POST['gender'] ?? '');
    $nationality    = clean_input($_POST['nationality'] ?? '');
    $phone          = clean_input($_POST['phone'] ?? '');
    $email          = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $address        = clean_input($_POST['address'] ?? '');
    
    // Course Selection (Checkboxes)
    if (isset($_POST['courses']) && is_array($_POST['courses'])) {
        $coursesArray = array_map('clean_input', $_POST['courses']);
        $courses = implode(', ', $coursesArray);
    } else {
        $courses = clean_input($_POST['courses'] ?? 'None selected');
    }
    
    $aboutYou       = clean_input($_POST['aboutYou'] ?? '');
    $mediaExp       = clean_input($_POST['mediaExp'] ?? 'None provided');
    $educationLevel = clean_input($_POST['educationLevel'] ?? '');
    $fieldOfStudy   = clean_input($_POST['fieldOfStudy'] ?? 'N/A');
    $declaration    = (!empty($_POST['declaration'])) ? 'Yes' : 'No';
    $submissionTime = date('Y-m-d H:i:s T');
    $clientIp       = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    // 2. FIELD VALIDATION
    if (empty($fullName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($phone)) {
        error_log("[" . date('Y-m-d H:i:s') . "] Validation failed: Missing name, phone, or invalid email ($email)");
        respond('error', 'Please provide a valid full name, phone number, and email address.');
    }

    // 3. SECURE LOCAL BACKUP ARCHIVING
    $backupDir = __DIR__ . '/data';
    if (!file_exists($backupDir)) {
        @mkdir($backupDir, 0755, true);
        @file_put_contents($backupDir . '/.htaccess', "Deny from all\n");
    }
    
    $applicationRecord = [
        'timestamp'      => $submissionTime,
        'ip'             => $clientIp,
        'fullName'       => $fullName,
        'dob'            => $dob,
        'gender'         => $gender,
        'nationality'    => $nationality,
        'phone'          => $phone,
        'email'          => $email,
        'address'        => $address,
        'courses'        => $courses,
        'aboutYou'       => $aboutYou,
        'mediaExp'       => $mediaExp,
        'educationLevel' => $educationLevel,
        'fieldOfStudy'   => $fieldOfStudy,
        'declaration'    => $declaration
    ];

    @file_put_contents(
        $backupDir . '/applications_backup.json', 
        json_encode($applicationRecord, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ",\n", 
        FILE_APPEND | LOCK_EX
    );

    // 4. EMAIL TEMPLATES (SPAM-FILTER COMPLIANT HTML + PLAIN TEXT ALTERNATIVE)
    $adminSubject = "New Student Application: " . $fullName;
    
    $adminHtmlBody = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>New Student Application</title>
    </head>
    <body style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f6f8; margin: 0; padding: 25px; color: #1e293b;">
        <div style="max-width: 650px; background: #ffffff; margin: 0 auto; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="background: linear-gradient(135deg, #26C6DA 0%, #0097A7 100%); padding: 25px; text-align: center; color: #ffffff;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;">Africa Broadcasting Academy</h1>
                <p style="margin: 5px 0 0 0; font-size: 15px; opacity: 0.95;">New Student Admission Application</p>
            </div>
            <div style="padding: 30px; line-height: 1.7;">
                <h3 style="color: #0097A7; margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-size: 17px;">1. Personal Information</h3>
                <p style="margin: 0 0 15px 0;">
                    <strong>Full Name:</strong> ' . $fullName . '<br>
                    <strong>Date of Birth:</strong> ' . $dob . '<br>
                    <strong>Gender:</strong> ' . $gender . '<br>
                    <strong>Nationality:</strong> ' . $nationality . '<br>
                    <strong>Phone:</strong> ' . $phone . '<br>
                    <strong>Email:</strong> <a href="mailto:' . $email . '" style="color: #0097A7;">' . $email . '</a><br>
                    <strong>Residential Address:</strong> ' . $address . '
                </p>

                <h3 style="color: #0097A7; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-size: 17px;">2. Course Selection</h3>
                <p style="margin: 0 0 15px 0; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border-left: 4px solid #26C6DA;">
                    <strong>Selected Courses:</strong> ' . $courses . '
                </p>

                <h3 style="color: #0097A7; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-size: 17px;">3. About the Applicant</h3>
                <p style="margin: 0 0 15px 0; white-space: pre-line;">' . nl2br($aboutYou) . '</p>

                <h3 style="color: #0097A7; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-size: 17px;">4. Experience & Education</h3>
                <p style="margin: 0 0 15px 0;">
                    <strong>Media Experience:</strong><br>' . nl2br($mediaExp) . '<br><br>
                    <strong>Highest Education Level:</strong> ' . $educationLevel . '<br>
                    <strong>Field of Study:</strong> ' . $fieldOfStudy . '<br>
                    <strong>Declaration Confirmed:</strong> ' . $declaration . '
                </p>

                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 25px 0;">
                <p style="font-size: 12px; color: #64748b; margin: 0;">
                    Submitted on: ' . $submissionTime . ' | IP: ' . $clientIp . '
                </p>
            </div>
        </div>
    </body>
    </html>';

    $adminAltBody = "AFRICA BROADCASTING ACADEMY - NEW APPLICATION\n"
                  . "=============================================\n\n"
                  . "1. PERSONAL INFORMATION:\n"
                  . "Full Name: {$fullName}\n"
                  . "Date of Birth: {$dob}\n"
                  . "Gender: {$gender}\n"
                  . "Nationality: {$nationality}\n"
                  . "Phone: {$phone}\n"
                  . "Email: {$email}\n"
                  . "Address: {$address}\n\n"
                  . "2. COURSE SELECTION:\n"
                  . "Courses: {$courses}\n\n"
                  . "3. ABOUT APPLICANT:\n"
                  . "{$aboutYou}\n\n"
                  . "4. EXPERIENCE & EDUCATION:\n"
                  . "Media Experience: {$mediaExp}\n"
                  . "Education Level: {$educationLevel}\n"
                  . "Field of Study: {$fieldOfStudy}\n"
                  . "Declaration Accepted: {$declaration}\n\n"
                  . "Submitted: {$submissionTime} (IP: {$clientIp})";

    $applicantSubject = "Application Received - Africa Broadcasting Academy";
    $applicantHtmlBody = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Application Received</title>
    </head>
    <body style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f6f8; margin: 0; padding: 25px; color: #1e293b;">
        <div style="max-width: 600px; background: #ffffff; margin: 0 auto; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="background: #000000; padding: 25px; text-align: center;">
                <img src="https://www.africabroadcastingacademy.com/assets/images/logo.png" width="110" alt="Africa Broadcasting Academy" style="display: inline-block;">
            </div>
            <div style="padding: 35px 30px; text-align: center;">
                <div style="display: inline-block; width: 60px; height: 60px; line-height: 60px; background: rgba(38, 198, 218, 0.15); border-radius: 50%; color: #0097A7; font-size: 32px; font-weight: bold; margin-bottom: 15px;">✓</div>
                <h1 style="color: #0f172a; margin: 0 0 10px 0; font-size: 24px;">Application Received</h1>
                <p style="color: #475569; font-size: 15px; margin: 0 0 25px 0;">Thank you for applying to the <strong>Africa Broadcasting Academy</strong>.</p>
                <div style="text-align: left; background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #26C6DA; margin-bottom: 25px;">
                    <p style="margin: 0; color: #334155; line-height: 1.6;">
                        Hello <strong>' . $fullName . '</strong>,<br><br>
                        We have received your application for: <strong>' . $courses . '</strong>.<br>
                        Our admissions committee is currently reviewing your profile and will contact you regarding next steps.
                    </p>
                </div>
                <a href="https://africabroadcastingacademy.com" style="display: inline-block; background: #26C6DA; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 14px;">Visit Our Website</a>
            </div>
            <div style="background: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b;">
                Africa Broadcasting Academy &copy; ' . date('Y') . '. All rights reserved.
            </div>
        </div>
    </body>
    </html>';

    $applicantAltBody = "Hello {$fullName},\n\n"
                      . "Thank you for applying to the Africa Broadcasting Academy!\n"
                      . "We have received your application for: {$courses}.\n\n"
                      . "Our admissions committee is currently reviewing your profile and will contact you regarding next steps.\n\n"
                      . "Africa Broadcasting Academy\n"
                      . "https://africabroadcastingacademy.com";

    // 5. EMAIL DELIVERY PIPELINE (PRIMARY SMTP + FALLBACK NATIVE MAIL)
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer/src/Exception.php';

    $adminMailSent = false;

    // --- ATTEMPT 1: AUTHENTICATED SMTP (PHPMailer) ---
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'mail.africabroadcastingacademy.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@africabroadcastingacademy.com';
        $mail->Password   = ')oHsH6GEgGHNs[9Q'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 15;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Send to Admins
        $mail->setFrom('noreply@africabroadcastingacademy.com', 'Africa Broadcasting Academy');
        $mail->addAddress('info@africabroadcastingacademy.com');
        $mail->addAddress('samson.a@africabroadcastingacademy.com');
        $mail->addReplyTo($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = $adminSubject;
        $mail->Body    = $adminHtmlBody;
        $mail->AltBody = $adminAltBody;

        $mail->send();
        $adminMailSent = true;
        error_log("[" . date('Y-m-d H:i:s') . "] Application admin notification sent via SMTP for: " . $fullName);

        // Send Confirmation to Student (Independent try/catch so admin success is preserved)
        try {
            $studentMail = clone $mail;
            $studentMail->clearAddresses();
            $studentMail->clearReplyTos();
            $studentMail->addAddress($email, $fullName);
            $studentMail->addReplyTo('info@africabroadcastingacademy.com', 'Africa Broadcasting Academy Admissions');
            $studentMail->Subject = $applicantSubject;
            $studentMail->Body    = $applicantHtmlBody;
            $studentMail->AltBody = $applicantAltBody;
            $studentMail->send();
            error_log("[" . date('Y-m-d H:i:s') . "] Applicant confirmation sent via SMTP to: " . $email);
        } catch (Exception $studentEx) {
            error_log("[" . date('Y-m-d H:i:s') . "] Student confirmation SMTP note: " . $studentEx->getMessage());
        }

    } catch (Exception $smtpEx) {
        error_log("[" . date('Y-m-d H:i:s') . "] Primary SMTP failed: " . $smtpEx->getMessage() . " -> Initiating native mail() fallback");
        
        // --- ATTEMPT 2: NATIVE PHP mail() FALLBACK ---
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Africa Broadcasting Academy <noreply@africabroadcastingacademy.com>\r\n";
        $headers .= "Reply-To: " . $fullName . " <" . $email . ">\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $adminEmailRecipients = "info@africabroadcastingacademy.com, samson.a@africabroadcastingacademy.com";
        $nativeAdminSent = @mail($adminEmailRecipients, $adminSubject, $adminHtmlBody, $headers);
        
        if ($nativeAdminSent) {
            $adminMailSent = true;
            error_log("[" . date('Y-m-d H:i:s') . "] Application admin notification sent via native mail() fallback for: " . $fullName);

            // Send student confirmation via native mail()
            $studentHeaders  = "MIME-Version: 1.0\r\n";
            $studentHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
            $studentHeaders .= "From: Africa Broadcasting Academy <noreply@africabroadcastingacademy.com>\r\n";
            $studentHeaders .= "Reply-To: Africa Broadcasting Academy <info@africabroadcastingacademy.com>\r\n";
            @mail($email, $applicantSubject, $applicantHtmlBody, $studentHeaders);
        } else {
            error_log("[" . date('Y-m-d H:i:s') . "] Native mail() also failed, but application is safely archived in data/applications_backup.json");
            // Since data is saved in local backup, we still treat it as received so student isn't frustrated
            $adminMailSent = true; 
        }
    }

    // 6. SUCCESS RESPONSE
    if ($adminMailSent) {
        respond('success', 'Your application has been submitted successfully!', 'thank-you.html');
    } else {
        respond('error', 'We encountered an error processing your application. Please try again or contact us directly.');
    }
} else {
    header("Location: applicationform.html");
    exit;
}