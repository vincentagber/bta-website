<?php
/**
 * Africa Broadcasting Academy - Test Suite for Application Submission & Anti-Spam Pipeline
 * File: tests/run_tests.php
 */

$testDir = __DIR__;
$rootDir = dirname($testDir);

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function run_test($name, $closure) {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    echo "Running Test #{$totalTests}: {$name} ... ";
    try {
        $result = $closure();
        if ($result === true) {
            $passedTests++;
            echo "[\033[32mPASS\033[0m]\n";
        } else {
            $failedTests++;
            echo "[\033[31mFAIL\033[0m]: " . ($result ?: 'Condition returned false') . "\n";
        }
    } catch (Throwable $t) {
        $failedTests++;
        echo "[\033[31mEXCEPTION\033[0m]: " . $t->getMessage() . "\n";
    }
}

// Helper to simulate request to application.php
function simulate_application_request($postData, $isAjax = true) {
    global $rootDir;
    
    // Save original state
    $oldServer = $_SERVER;
    $oldPost = $_POST;
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if ($isAjax) {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
    } else {
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        unset($_SERVER['HTTP_ACCEPT']);
    }
    
    $_POST = $postData;

    // Capture output
    ob_start();
    // Run script via isolated execution
    $phpCode = '
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
    ' . ($isAjax ? '
        $_SERVER["HTTP_X_REQUESTED_WITH"] = "xmlhttprequest";
        $_SERVER["HTTP_ACCEPT"] = "application/json";
    ' : '
        unset($_SERVER["HTTP_X_REQUESTED_WITH"]);
        unset($_SERVER["HTTP_ACCEPT"]);
    ') . '
        $_POST = ' . var_export($postData, true) . ';
        include "' . addslashes($rootDir . '/application.php') . '";
    ';
    
    $descriptorSpec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    
    $proc = proc_open("php", $descriptorSpec, $pipes, $rootDir);
    fwrite($pipes[0], "<?php\n" . $phpCode);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $returnCode = proc_close($proc);
    
    return [
        'stdout' => $stdout,
        'stderr' => $stderr,
        'code'   => $returnCode
    ];
}

echo "\n=======================================================\n";
echo "AFRICA BROADCASTING ACADEMY - COMPREHENSIVE PIPELINE AUDIT\n";
echo "=======================================================\n\n";

// TEST 1: Validation - Missing Full Name
run_test("Validation: Rejection of missing name", function() {
    $res = simulate_application_request([
        'fullName' => '',
        'email'    => 'test@example.com',
        'phone'    => '+2348012345678',
        'courses'  => ['Digital Video Editing']
    ]);
    $json = json_decode($res['stdout'], true);
    if (!$json || $json['status'] !== 'error') {
        return "Expected error status, got: " . $res['stdout'];
    }
    if (strpos($json['message'], 'full name') === false) {
        return "Expected message mentioning 'full name', got: " . $json['message'];
    }
    return true;
});

// TEST 2: Validation - Invalid Email
run_test("Validation: Rejection of invalid email format", function() {
    $res = simulate_application_request([
        'fullName' => 'Chidi Okonkwo',
        'email'    => 'not-an-email',
        'phone'    => '+2348012345678',
        'courses'  => ['Cinematography']
    ]);
    $json = json_decode($res['stdout'], true);
    if (!$json || $json['status'] !== 'error') {
        return "Expected error status, got: " . $res['stdout'];
    }
    if (strpos($json['message'], 'valid email') === false) {
        return "Expected message mentioning 'valid email', got: " . $json['message'];
    }
    return true;
});

// TEST 3: Validation - Invalid Phone (< 6 digits)
run_test("Validation: Rejection of phone number with insufficient digits", function() {
    $res = simulate_application_request([
        'fullName' => 'Fatima Bello',
        'email'    => 'fatima@example.com',
        'phone'    => '12',
        'courses'  => ['Presenting']
    ]);
    $json = json_decode($res['stdout'], true);
    if (!$json || $json['status'] !== 'error') {
        return "Expected error status, got: " . $res['stdout'];
    }
    if (strpos($json['message'], 'phone number') === false) {
        return "Expected message mentioning 'phone number', got: " . $json['message'];
    }
    return true;
});

// TEST 4: Validation Simplification - Optional fields can be empty without causing errors
run_test("Validation: Minimal viable fields accepted without non-essential friction", function() {
    $res = simulate_application_request([
        'fullName'       => 'Amara Diallo',
        'email'          => 'amara.diallo@example.com',
        'phone'          => '+229 97 12 34 56',
        'courses'        => ['Documentary Filmmaking'],
        'dob'            => '', // Empty DOB
        'gender'         => '', // Empty Gender
        'nationality'    => '', // Empty Nationality
        'address'        => '', // Empty Address
        'aboutYou'       => '', // Empty About You
        'educationLevel' => ''  // Empty Education Level
    ]);
    $json = json_decode($res['stdout'], true);
    if (!$json || $json['status'] !== 'success') {
        return "Expected success status for minimal submission, got: " . $res['stdout'];
    }
    return true;
});

// TEST 5: Honeypot - bta_hp_company trap triggers silent success without writing to backup
run_test("Anti-Spam Honeypot: bta_hp_company trap silently disarms bots", function() use ($rootDir) {
    $backupFile = $rootDir . '/data/applications_backup.json';
    $beforeSize = file_exists($backupFile) ? filesize($backupFile) : 0;
    
    $res = simulate_application_request([
        'bta_hp_company' => 'SpamBot Industries LLC',
        'fullName'       => 'Bot McSpammer',
        'email'          => 'spammer@botnet.ru',
        'phone'          => '+12345678901'
    ]);
    
    $json = json_decode($res['stdout'], true);
    if (!$json || $json['status'] !== 'success') {
        return "Expected simulated success response to fool bot, got: " . $res['stdout'];
    }
    
    $afterSize = file_exists($backupFile) ? filesize($backupFile) : 0;
    if ($afterSize > $beforeSize) {
        return "Backup file size increased; bot submission was erroneously saved!";
    }
    return true;
});

// TEST 6: Honeypot - Superhuman submission speed (< 2s) triggers time trap
run_test("Anti-Spam Timing Trap: Submissions under 2 seconds are caught", function() use ($rootDir) {
    $backupFile = $rootDir . '/data/applications_backup.json';
    $beforeSize = file_exists($backupFile) ? filesize($backupFile) : 0;
    
    $res = simulate_application_request([
        'bta_form_time'  => time(), // 0 seconds elapsed
        'fullName'       => 'Instant Script Bot',
        'email'          => 'instant@speedbot.org',
        'phone'          => '+10000000000',
        'courses'        => ['Digital Video Editing']
    ]);
    
    $json = json_decode($res['stdout'], true);
    if (!$json || $json['status'] !== 'success') {
        return "Expected simulated success response for bot, got: " . $res['stdout'];
    }
    
    $afterSize = file_exists($backupFile) ? filesize($backupFile) : 0;
    if ($afterSize > $beforeSize) {
        return "Backup file size increased; speed-bot submission was erroneously saved!";
    }
    return true;
});

// TEST 7: Local Backup Persistence
run_test("Data Persistence: Legitimate applications correctly append to backup JSON", function() use ($rootDir) {
    $backupFile = $rootDir . '/data/applications_backup.json';
    $uniqueName = "TestStudent_" . uniqid();
    
    $res = simulate_application_request([
        'fullName'       => $uniqueName,
        'email'          => 'student_' . uniqid() . '@example.com',
        'phone'          => '+234 812 842 2499',
        'courses'        => ['Digital Video Editing', 'Cinematography'],
        'bta_form_time'  => time() - 10 // 10 seconds ago (human speed)
    ]);
    
    $json = json_decode($res['stdout'], true);
    if (!$json || $json['status'] !== 'success') {
        return "Submission failed: " . $res['stdout'];
    }
    
    $fileContents = file_get_contents($backupFile);
    if (strpos($fileContents, $uniqueName) === false) {
        return "Unique student name was not found in data/applications_backup.json!";
    }
    return true;
});

// TEST 8: Output Buffering - Output is strictly valid JSON without HTML/warnings
run_test("JSON Integrity: Clean response without prepended warnings or BOM", function() {
    $res = simulate_application_request([
        'fullName'       => 'Kofi Mensah',
        'email'          => 'kofi@example.com',
        'phone'          => '+233 24 123 4567',
        'courses'        => ['Script Writing'],
        'bta_form_time'  => time() - 15
    ]);
    
    $trimmed = trim($res['stdout']);
    if (empty($trimmed) || $trimmed[0] !== '{' || substr($trimmed, -1) !== '}') {
        return "Stdout did not start with '{' and end with '}': " . $res['stdout'];
    }
    $decoded = json_decode($trimmed, true);
    if ($decoded === null) {
        return "JSON parsing failed: " . json_last_error_msg();
    }
    return true;
});

// TEST 9: DMARC/SPF Strict Alignment in Code Audit
run_test("Email Deliverability: Strict envelope sender (-f) in native mail fallback", function() use ($rootDir) {
    $appCode = file_get_contents($rootDir . '/application.php');
    if (strpos($appCode, '$envelopeSender = \'-f \' . $fromEmail;') === false &&
        strpos($appCode, '-f noreply@africabroadcastingacademy.com') === false) {
        return "Missing envelope sender parameter in mail() fallback!";
    }
    if (strpos($appCode, '$mail->Sender') === false) {
        return "Missing \$mail->Sender envelope sender configuration in PHPMailer!";
    }
    if (strpos($appCode, '$mail->SMTPKeepAlive = true;') === false) {
        return "Missing \$mail->SMTPKeepAlive for multi-message dispatch!";
    }
    return true;
});

// TEST 10: Frontend Pattern Mismatch Protection in applicationform.html
run_test("Frontend Hardening: applicationform.html protects against Safari pattern mismatch", function() use ($rootDir) {
    $html = file_get_contents($rootDir . '/applicationform.html');
    if (strpos($html, 'responseText') === false || strpos($html, 'JSON.parse') === false) {
        return "Missing safe responseText / JSON.parse check in applicationform.html!";
    }
    if (strpos($html, 'bta_hp_company') === false) {
        return "Missing bta_hp_company honeypot in applicationform.html!";
    }
    if (strpos($html, 'bta_form_time') === false) {
        return "Missing timing trap token in applicationform.html!";
    }
    return true;
});

echo "\n=======================================================\n";
echo "TEST RESULTS: {$passedTests}/{$totalTests} Passed ({$failedTests} Failed)\n";
echo "=======================================================\n";

if ($failedTests > 0) {
    exit(1);
}
exit(0);
