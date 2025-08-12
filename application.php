<?php
// application.php

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    function clean_input($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Required fields
    $requiredFields = [
        'fullName', 'dob', 'gender', 'nationality', 'phone', 'email',
        'address', 'aboutYou', 'mediaExp', 'educationLevel', 'fieldOfStudy',
        'institution', 'graduationYear', 'expectations', 'declaration'
    ];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            die("Error: Missing required field - " . $field);
        }
    }

    // Sanitize
    $fullName       = clean_input($_POST['fullName']);
    $dob            = clean_input($_POST['dob']);
    $gender         = clean_input($_POST['gender']);
    $nationality    = clean_input($_POST['nationality']);
    $phone          = clean_input($_POST['phone']);
    $email          = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) die("Error: Invalid email address.");
    $address        = clean_input($_POST['address']);
    $courses        = isset($_POST['courses']) ? array_map('clean_input', $_POST['courses']) : [];
    $aboutYou       = clean_input($_POST['aboutYou']);
    $mediaExp       = clean_input($_POST['mediaExp']);
    $educationLevel = clean_input($_POST['educationLevel']);
    $fieldOfStudy   = clean_input($_POST['fieldOfStudy']);
    $institution    = clean_input($_POST['institution']);
    $graduationYear = intval($_POST['graduationYear']);
    $expectations   = clean_input($_POST['expectations']);
    $additional     = clean_input($_POST['additional'] ?? '');

    // Handle video upload
    if (!isset($_FILES['introVideo']) || $_FILES['introVideo']['error'] !== UPLOAD_ERR_OK) {
        die("Error: Video upload failed.");
    }

    $video = $_FILES['introVideo'];
    $allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
    $maxSize = 50 * 1024 * 1024; // 50 MB

    if (!in_array($video['type'], $allowedTypes)) {
        die("Error: Invalid video format.");
    }
    if ($video['size'] > $maxSize) {
        die("Error: Video exceeds 50MB size limit.");
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $videoPath = $uploadDir . time() . "_" . basename($video['name']);
    if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
        die("Error: Failed to save uploaded video.");
    }

    // Save to CSV
    $file = __DIR__ . '/applications.csv';
    $fp = fopen($file, 'a');
    if ($fp) {
        fputcsv($fp, [
            $fullName, $dob, $gender, $nationality, $phone, $email, $address,
            implode(", ", $courses), $aboutYou, $mediaExp, $educationLevel,
            $fieldOfStudy, $institution, $graduationYear, $expectations,
            $additional, $videoPath
        ]);
        fclose($fp);
    }

    // Send Email
    $to = "info@africabroadcastingacademy.com, samson.a@africabroadcastingacademy.com";
    $subject = "New Application from " . $fullName;

    $message = "
    A new application has been submitted:\n\n
    Full Name: $fullName
    Date of Birth: $dob
    Gender: $gender
    Nationality: $nationality
    Phone: $phone
    Email: $email
    Address: $address
    Courses: " . implode(", ", $courses) . "
    About You: $aboutYou
    Media Experience: $mediaExp
    Education Level: $educationLevel
    Field of Study: $fieldOfStudy
    Institution: $institution
    Graduation Year: $graduationYear
    Expectations: $expectations
    Additional: $additional
    Video Path: $videoPath
    ";

    $headers = "From: no-reply@africabroadcastingacademy.com\r\n";
    $headers .= "Reply-To: $email\r\n";

    mail($to, $subject, $message, $headers);

    echo "<h2>Application Submitted Successfully!</h2>";
    echo "<p>Thank you, $fullName. We have received your application and will contact you soon.</p>";
} else {
    echo "Invalid request.";
}