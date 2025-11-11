<?php
session_start();
header('Content-Type: application/json');

require_once '../../classes/connection.php'; // ensures $mysqli

// Load PHPMailer properly (no "use" before PHP logic)
require_once '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Collect POST data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$date    = trim($_POST['date'] ?? '');
$time    = trim($_POST['time'] ?? '');
$guests  = trim($_POST['guests'] ?? '');
$message = trim($_POST['message'] ?? '');

// Required fields check
if (!$name || !$email || !$phone || !$date || !$time || !$guests) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit;
}

// Validate Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit;
}

// Validate Phone (only digits & length)
if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number.']);
    exit;
}

// Validate Guests
if (!is_numeric($guests) || $guests < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Number of guests must be at least 1.']);
    exit;
}

// Validate message max length
if (strlen($message) > 500) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot exceed 500 characters.']);
    exit;
}

// TIME RESTRICTION LOGIC
$current = new DateTime();
$reservationDateTime = new DateTime("$date $time");
$hoursDifference = ($reservationDateTime->getTimestamp() - $current->getTimestamp()) / 3600;

if ($guests < 8 && $hoursDifference < 2) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Reservations for fewer than 8 guests must be made at least 2 hours in advance.'
    ]);
    exit;
}

if ($guests >= 8 && $hoursDifference < 24) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Reservations for 8 or more guests must be made at least 24 hours in advance.'
    ]);
    exit;
}

// If user logged in, store ID
$user_id = $_SESSION['user_id'] ?? null;

// Insert into database
$stmt = $mysqli->prepare("
    INSERT INTO reservations (user_id, name, email, phone, reservation_date, reservation_time, guests, message, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("isssssis", $user_id, $name, $email, $phone, $date, $time, $guests, $message);

if ($stmt->execute()) {

    // === SEND EMAIL TO ADMIN ===
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'desmonddefouh5@gmail.com';
        $mail->Password   = 'egtd jiel nztl bpbz';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('info@licrestaurant.cm', 'LICRestor Website');
        $mail->addAddress('info@licrestaurant.cm', 'LICRestor Admin');

        $mail->isHTML(true);
        $mail->Subject = "New Reservation Request - $name";
        $mail->Body = "
            <h3>New Reservation Submitted</h3>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Date:</strong> $date</p>
            <p><strong>Time:</strong> $time</p>
            <p><strong>Guests:</strong> $guests</p>
            <p><strong>Message:</strong><br>$message</p>
        ";
        $mail->send();
    } catch (Exception $e) {}

    // === SEND CONFIRMATION EMAIL TO CUSTOMER ===
    try {
        $mail2 = new PHPMailer(true);
        $mail2->isSMTP();
        $mail2->Host       = 'smtp.gmail.com';
        $mail2->SMTPAuth   = true;
        $mail2->Username   = 'desmonddefouh5@gmail.com';
        $mail2->Password   = 'egtd jiel nztl bpbz';
        $mail2->SMTPSecure = 'tls';
        $mail2->Port       = 587;

        $mail2->setFrom('info@licrestaurant.cm', 'LIC Restaurant');
        $mail2->addAddress($email, $name);

        $mail2->isHTML(true);
        $mail2->Subject = "Reservation Confirmation";
        $mail2->Body = "
            <h3>Your Reservation is Received!</h3>
            <p>Thank you, <strong>$name</strong>, for choosing LIC Restaurant.</p>
            <p><strong>Date:</strong> $date</p>
            <p><strong>Time:</strong> $time</p>
            <p><strong>Guests:</strong> $guests</p>
            <p>We will confirm your table shortly.</p>
            <p>— LIC Restaurant Team</p>
        ";

        $mail2->send();
    } catch (Exception $e) {}

    echo json_encode(['status' => 'success', 'message' => 'Your reservation request has been submitted and a confirmation email has been sent.']);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit reservation. Please try again.']);
}

$stmt->close();
$mysqli->close();
