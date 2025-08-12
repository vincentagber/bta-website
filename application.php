<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Clean and assign values
    $fullName = htmlspecialchars(trim($_POST['fullName']));
    $dob = htmlspecialchars(trim($_POST['dob']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $nationality = htmlspecialchars(trim($_POST['nationality']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars(trim($_POST['address']));

    $aboutYou = htmlspecialchars(trim($_POST['aboutYou']));
    $mediaExp = htmlspecialchars(trim($_POST['mediaExp']));
    $educationLevel = htmlspecialchars(trim($_POST['educationLevel']));
    $fieldOfStudy = htmlspecialchars(trim($_POST['fieldOfStudy']));
    $institution = htmlspecialchars(trim($_POST['institution']));
    $graduationYear = htmlspecialchars(trim($_POST['graduationYear']));
    $expectations = htmlspecialchars(trim($_POST['expectations']));
    $additional = htmlspecialchars(trim($_POST['additional'] ?? ''));

    // Handle checkboxes
    $courses = isset($_POST['courses']) ? implode(", ", $_POST['courses']) : 'None selected';

    // Handle video upload
    $videoPath = "No video uploaded.";
    if (isset($_FILES['introVideo']) && $_FILES['introVideo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['introVideo']['tmp_name'];
        $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $_FILES['introVideo']['name']);
        $fileSize = $_FILES['introVideo']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['mp4', 'mov', 'avi', 'mkv'];
        $maxFileSize = 50 * 1024 * 1024; // 50MB

        // Validate MIME type
        $mime = mime_content_type($fileTmpPath);
        $allowedMimes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];

        if (in_array($fileExt, $allowedExtensions) && in_array($mime, $allowedMimes)) {
            if ($fileSize <= $maxFileSize) {
                $newFileName = uniqid("intro_", true) . "." . $fileExt;
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $videoPath = $destPath;
                } else {
                    $videoPath = "Error moving the uploaded file.";
                    error_log("Failed to move file for $email");
                }
            } else {
                $videoPath = "Video exceeds 50MB limit.";
                error_log("File too large from $email");
            }
        } else {
            $videoPath = "Invalid video format.";
            error_log("Invalid video format: $fileName from $email");
        }
    }

    // Email setup
    $to = "info@africabroadcastingacademy.com,Samson.a@africabroadcastingacademy.com";
    $subject = "New Mentorship Application from $fullName";

    $body = <<<EOD
You have received a new Application Form application:

Full Name: $fullName
Date of Birth: $dob
Gender: $gender
Nationality: $nationality
Phone: $phone
Email: $email
Address: $address

Selected Courses: $courses

About Applicant:
$aboutYou

Media Experience:
$mediaExp

Education:
- Level: $educationLevel
- Field: $fieldOfStudy
- Institution: $institution
- Graduation Year: $graduationYear

Expectations:
$expectations

Additional Info:
$additional

Video Introduction: $videoPath
EOD;

    $headers = "From: noreply@africabroadcastingacademy.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Application submitted successfully!'); window.location.href='thank-you.html';</script>";
    } else {
        error_log("Mail failed for: $email");
        echo "<script>alert('Sorry, there was a problem submitting your application.'); window.history.back();</script>";
    }
}
?>