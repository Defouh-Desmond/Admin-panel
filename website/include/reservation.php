<?php
session_start();
header('Content-Type: application/json');

// Optional: send email to admin
require_once '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../classes/connection.php';

// Collect POST data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$date    = trim($_POST['date'] ?? '');
$time    = trim($_POST['time'] ?? '');
$guests  = trim($_POST['guests'] ?? '');
$message = trim($_POST['message'] ?? '');

// Server-side validation
if (empty($name) || empty($email) || empty($phone) || empty($date) || empty($time) || empty($guests)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit;
}

// Phone validation
if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number.']);
    exit;
}

// Guests validation
if (!is_numeric($guests) || $guests < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Number of guests must be at least 1.']);
    exit;
}

// Date validation
$reservation_date = DateTime::createFromFormat('Y-m-d', $date);
$today = new DateTime();
$today->setTime(0,0,0);
if (!$reservation_date || $reservation_date < $today) {
    echo json_encode(['status' => 'error', 'message' => 'Reservation date cannot be in the past.']);
    exit;
}

// Optional: message length check
if (strlen($message) > 500) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot exceed 500 characters.']);
    exit;
}

// Optional: if logged in
$user_id = $_SESSION['user_id'] ?? null;

// Insert into database
$stmt = $mysqli->prepare("INSERT INTO reservations (user_id, name, email, phone, reservation_date, reservation_time, guests, message, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("isssssis", $user_id, $name, $email, $phone, $date, $time, $guests, $message);

if ($stmt->execute()) {

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
        $mail->Subject = "New Reservation Request";
        $mail->Body    = "<p><strong>Name:</strong> $name</p>
                          <p><strong>Email:</strong> $email</p>
                          <p><strong>Phone:</strong> $phone</p>
                          <p><strong>Date:</strong> $date</p>
                          <p><strong>Time:</strong> $time</p>
                          <p><strong>Guests:</strong> $guests</p>
                          <p><strong>Message:</strong><br>$message</p>";

        $mail->send();
    } catch (Exception $e) {
        // Fail silently
    }

    echo json_encode(['status' => 'success', 'message' => 'Your reservation has been successfully submitted!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit your reservation. Please try again.']);
}

$stmt->close();
$mysqli->close();
