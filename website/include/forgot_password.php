<?php
// include/forgot_password.php

header('Content-Type: application/json');
require_once '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$mysqli = new mysqli("localhost", "root", "", "admin_dashboard_db");
if ($mysqli->connect_errno) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to database.']);
    exit;
}

// Get email
$email = $_POST['email'] ?? '';
if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide your email.']);
    exit;
}

// Find user by email
$stmt = $mysqli->prepare("SELECT user_id, full_name, email FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['status' => 'error', 'message' => 'No account found with this email.']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// --- Check if a valid reset link already exists (not expired) ---
$stmt = $mysqli->prepare("SELECT expires_at FROM password_resets WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $lastReset = $result->fetch_assoc();
    $currentTime = time();
    $expiresTime = strtotime($lastReset['expires_at']);

    if ($currentTime < $expiresTime) {
        $remaining = $expiresTime - $currentTime;
        $minutes = floor($remaining / 60);
        $seconds = $remaining % 60;
        echo json_encode([
            'status' => 'error',
            'message' => "You already have a valid reset link. Please wait {$minutes}m {$seconds}s before requesting a new one."
        ]);
        exit;
    }
}
$stmt->close();

// --- Limit to 2 reset requests per day ---
$stmt = $mysqli->prepare("
    SELECT COUNT(*) AS request_count 
    FROM password_resets 
    WHERE user_id = ? AND DATE(created_at) = CURDATE()
");
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['request_count'] >= 2) {
    echo json_encode(['status' => 'error', 'message' => 'You have reached the limit of 2 reset requests for today. Please try again tomorrow.']);
    exit;
}

// --- Generate new secure token ---
$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// --- Store token in password_resets table ---
$stmtInsert = $mysqli->prepare("
    INSERT INTO password_resets (user_id, token, expires_at, created_at) 
    VALUES (?, ?, ?, NOW())
");
$stmtInsert->bind_param("iss", $user['user_id'], $token, $expires_at);
$stmtInsert->execute();
$stmtInsert->close();

// --- Prepare reset link ---
$resetLink = "http://localhost/licrestor/website/reset_password.php?token=$token";

// --- Send email with PHPMailer ---
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'desmonddefouh5@gmail.com'; // Replace with your email
    $mail->Password   = 'egtd jiel nztl bpbz'; // Replace with your Gmail app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('no-reply@licrestor.com', 'LICRestor');
    $mail->addAddress($user['email'], $user['full_name']);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "Hi {$user['full_name']},<br><br>"
                   . "We received a request to reset your password.<br>"
                   . "Click the link below to reset your password (expires in 5 minutes):<br><br>"
                   . "<a href='$resetLink'>$resetLink</a><br><br>"
                   . "If you didn't request this, ignore this email.<br><br>"
                   . "Regards,<br>LICRestor Team";

    $mail->send();

    echo json_encode([
        'status' => 'success',
        'message' => 'A password reset link has been sent to your email.',
        'expires_at' => $expires_at
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
}
?>
