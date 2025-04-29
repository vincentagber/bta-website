<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "info@yourcompanydomain.com"; // 🔁 Replace with your real email
    $subject = "New Mentorship Application from " . $_POST['fullName'];

    // Sanitize and collect form data
    $fields = [
        'Full Name' => $_POST['fullName'] ?? '',
        'Date of Birth' => $_POST['dob'] ?? '',
        'Gender' => $_POST['gender'] ?? '',
        'Nationality' => $_POST['nationality'] ?? '',
        'Phone' => $_POST['phone'] ?? '',
        'Email' => $_POST['email'] ?? '',
        'Address' => $_POST['address'] ?? '',
        'Courses' => isset($_POST['courses']) ? implode(", ", $_POST['courses']) : '',
        'About You' => $_POST['aboutYou'] ?? '',
        'Media Experience' => $_POST['mediaExp'] ?? '',
        'Education Level' => $_POST['educationLevel'] ?? '',
        'Field of Study' => $_POST['fieldOfStudy'] ?? '',
        'Institution' => $_POST['institution'] ?? '',
        'Graduation Year' => $_POST['graduationYear'] ?? '',
        'Expectations' => $_POST['expectations'] ?? '',
        'Additional Info' => $_POST['additional'] ?? '',
    ];

    // Construct the message body
    $body = "New Mentorship Application:\n\n";
    foreach ($fields as $label => $value) {
        $body .= "$label: $value\n";
    }

    // Headers
    $headers = "From: BTA Website <no-reply@yourdomain.com>\r\n";
    $headers .= "Reply-To: " . $_POST['email'] . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8";

    // Send email
    if (mail($to, $subject, $body, $headers)) {
        echo "<script>
            alert('Your application has been submitted successfully!');
            window.location.href = 'application.html';
        </script>";
    } else {
        echo "<script>
            alert('Something went wrong. Please try again.');
            window.history.back();
        </script>";
    }
} else {
    // Prevent direct access
    header("Location: application.html");
    exit();
}
?>