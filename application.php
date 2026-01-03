<?php
// Namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Function to sanitize safely
function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $fullName       = clean_input($_POST['fullName'] ?? '');
    $dob            = clean_input($_POST['dob'] ?? '');
    $gender         = clean_input($_POST['gender'] ?? '');
    $nationality    = clean_input($_POST['nationality'] ?? '');
    $phone          = clean_input($_POST['phone'] ?? '');
    $email          = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $address        = clean_input($_POST['address'] ?? '');
    $courses        = isset($_POST['courses']) ? implode(', ', array_map('clean_input', $_POST['courses'])) : 'None selected';
    $aboutYou       = clean_input($_POST['aboutYou'] ?? '');
    $mediaExp       = clean_input($_POST['mediaExp'] ?? '');
    $educationLevel = clean_input($_POST['educationLevel'] ?? '');
    $fieldOfStudy   = clean_input($_POST['fieldOfStudy'] ?? '');
    $institution    = clean_input($_POST['institution'] ?? '');
    $graduationYear = (int)($_POST['graduationYear'] ?? 0);
    $expectations   = clean_input($_POST['expectations'] ?? '');
    $additional     = clean_input($_POST['additional'] ?? '');
    $declaration    = isset($_POST['declaration']) ? 'Yes' : 'No';

    // Validate required fields
    $errors = [];
    if (empty($fullName)) $errors[] = "Full Name is required.";
    if (empty($dob)) $errors[] = "Date of Birth is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($nationality)) $errors[] = "Nationality is required.";
    if (empty($phone)) $errors[] = "Phone Number is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($aboutYou)) $errors[] = "About You is required.";
    if (empty($mediaExp)) $errors[] = "Media Experience is required.";
    if (empty($educationLevel)) $errors[] = "Education Level is required.";
    if (empty($fieldOfStudy)) $errors[] = "Field of Study is required.";
    if (empty($institution)) $errors[] = "Institution is required.";
    if (empty($graduationYear)) $errors[] = "Graduation Year is required.";
    if (empty($expectations)) $errors[] = "Expectations are required.";
    if ($declaration !== 'Yes') $errors[] = "Declaration is required.";

    // If no errors, send email
    if (empty($errors)) {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0; // 0 = production, 2 = debugging
            $mail->isSMTP();
            $mail->Host = 'mail.africabroadcastingacademy.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'noreply@africabroadcastingacademy.com';
            $mail->Password = ')oHsH6GEgGHNs[9Q'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // From & Recipients
            $mail->setFrom('noreply@africabroadcastingacademy.com', 'ABA Applications');
            $mail->addReplyTo($email, $fullName); // Applicant's email for replies
            $mail->addAddress('info@africabroadcastingacademy.com', 'Admissions');
            $mail->addAddress('samson.a@africabroadcastingacademy.com', 'Samson A.');

            // Email format
            $mail->isHTML(true);
            $mail->Subject = "ðŸ“© New Application from {$fullName}";

            $bodyContent = "
                <h2 style='color:#2c3e50;'>New Application Submission</h2>
                <p><strong>Full Name:</strong> {$fullName}</p>
                <p><strong>Date of Birth:</strong> {$dob}</p>
                <p><strong>Gender:</strong> {$gender}</p>
                <p><strong>Nationality:</strong> {$nationality}</p>
                <p><strong>Phone Number:</strong> {$phone}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Address:</strong> {$address}</p>
                <p><strong>Courses:</strong> {$courses}</p>
                <p><strong>About You:</strong><br>" . nl2br($aboutYou) . "</p>
                <p><strong>Media Experience:</strong><br>" . nl2br($mediaExp) . "</p>
                <p><strong>Education Level:</strong> {$educationLevel}</p>
                <p><strong>Field of Study:</strong> {$fieldOfStudy}</p>
                <p><strong>Institution:</strong> {$institution}</p>
                <p><strong>Graduation Year:</strong> {$graduationYear}</p>
                <p><strong>Expectations:</strong><br>" . nl2br($expectations) . "</p>
                <p><strong>Additional Information:</strong><br>" . nl2br($additional) . "</p>
                <p><strong>Declaration:</strong> {$declaration}</p>
            ";

            $mail->Body = $bodyContent;
            $mail->AltBody = strip_tags(str_replace("<br>", "\n", $bodyContent));

            $mail->send();
            header("Location: thank-you.html");
            exit;
        } catch (Exception $e) {
            echo "<h2>Error</h2>";
            echo "<p>Failed to send the application. Error: {$mail->ErrorInfo}</p>";
        }



    } else {
        // Display validation errors
        echo "<h2>Submission Errors</h2><ul>";
        foreach ($errors as $error) echo "<li>{$error}</li>";
        echo "</ul><p><a href='javascript:history.back()'>Go back</a></p>";
    }
} else {
    echo "<h2>Error</h2><p>Invalid request method. Please submit the form.</p>";
}
?>