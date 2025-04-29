<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "info@africabroadcastingacademy.com, Samson.a@africabroadcastingacademy.com, Rokan.o@africabroadcastingacademy.com";
    $subject = "New Mentorship Application from " . htmlspecialchars($_POST['fullName']);

    $body = "You have received a new mentorship application:\n\n";
    $body .= "Full Name: " . $_POST['fullName'] . "\n";
    $body .= "Date of Birth: " . $_POST['dob'] . "\n";
    $body .= "Gender: " . $_POST['gender'] . "\n";
    $body .= "Nationality: " . $_POST['nationality'] . "\n";
    $body .= "Phone: " . $_POST['phone'] . "\n";
    $body .= "Email: " . $_POST['email'] . "\n";
    $body .= "Address: " . $_POST['address'] . "\n\n";

    $body .= "Selected Courses: " . implode(", ", $_POST['courses'] ?? []) . "\n\n";

    $body .= "About Applicant:\n" . $_POST['aboutYou'] . "\n\n";
    $body .= "Media Experience:\n" . $_POST['mediaExp'] . "\n\n";

    $body .= "Education:\n";
    $body .= "- Level: " . $_POST['educationLevel'] . "\n";
    $body .= "- Field: " . $_POST['fieldOfStudy'] . "\n";
    $body .= "- Institution: " . $_POST['institution'] . "\n";
    $body .= "- Graduation Year: " . $_POST['graduationYear'] . "\n\n";

    $body .= "Expectations:\n" . $_POST['expectations'] . "\n\n";
    $body .= "Additional Info:\n" . $_POST['additional'] . "\n";

    $headers = "From: noreply@africabroadcastingacademy.com\r\n";
    $headers .= "Reply-To: " . $_POST['email'] . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Application submitted successfully!'); window.location.href='index.html';</script>";
    } else {
        echo "<script>alert('Sorry, there was a problem submitting your application.'); window.history.back();</script>";
    }
}
?>