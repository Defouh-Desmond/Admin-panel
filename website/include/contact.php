<?php
// include/contact.php
session_start();
header('Content-Type: application/json');

require_once '../../classes/connection.php';

// PHPMailer
require_once '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Collect POST data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit;
}

// Optional: if user is logged in, store user_id
$user_id = $_SESSION['user_id'] ?? null;

// Insert into database
$stmt = $mysqli->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);

if ($stmt->execute()) {
    // Send email notification to admin
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'desmonddefouh5@gmail.com';
        $mail->Password   = 'egtd jiel nztl bpbz';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom($email, $name);
        $mail->addAddress('info@licrestaurant.cm', 'LICRestor Admin');

        $mail->isHTML(true);
        $mail->Subject = "Contact Form: $subject";
        $mail->Body    = "<p><strong>Name:</strong> $name</p>
                          <p><strong>Email:</strong> $email</p>
                          <p><strong>Message:</strong><br>$message</p>";

        $mail->send();
    } catch (Exception $e) {
        // Mail failure won't break the submission
    }

    echo json_encode(['status' => 'success', 'message' => 'Your message has been sent successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send your message. Please try again.']);
}

$stmt->close();
$mysqli->close();
