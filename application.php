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

    // Email setup
    $to = "info@africabroadcastingacademy.com,Samson.a@africabroadcastingacademy.com,Rokan.o@africabroadcastingacademy.com";
    $subject = "New Mentorship Application from $fullName";

    $body = <<<EOD
You have received a new mentorship application:

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