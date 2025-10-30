<?php
session_start();
header('Content-Type: application/json');

// Database connection
require_once '../../classes/connection.php';

// Get action
$action = isset($_POST['action']) ? $_POST['action'] : '';

/* ====================================================
   USER SIGNUP
==================================================== */
if ($action === 'signup') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(['status'=>'error','message'=>'All fields are required.']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['status'=>'error','message'=>'Passwords do not match.']);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ? OR phone = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['status'=>'error','message'=>'Email or phone number already exists.']);
        exit;
    }
    $stmt->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $mysqli->prepare("INSERT INTO users (full_name, email, phone, password_hash, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $insert->bind_param("ssss", $full_name, $email, $phone, $password_hash);

    if ($insert->execute()) {
        echo json_encode(['status'=>'success','message'=>'Account created successfully! Redirecting to login...']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Signup failed. Please try again.']);
    }
    $insert->close();
}

/* ====================================================
   USER LOGIN
==================================================== */
elseif ($action === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo json_encode(['status'=>'error','message'=>'Email and password are required.']);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT user_id, full_name, password_hash, block, suspend FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if blocked
        if ($user['block'] == 1) {
            echo json_encode(['status'=>'error','message'=>'Your account has been blocked. Please contact support.']);
            exit;
        }

        // Check if suspended
        if ($user['suspend'] == 1) {
            $suspStmt = $mysqli->prepare("SELECT suspension_due FROM suspended_users WHERE user_id = ? ORDER BY suspended_at DESC LIMIT 1");
            $suspStmt->bind_param("i", $user['user_id']);
            $suspStmt->execute();
            $suspResult = $suspStmt->get_result();

            if ($suspResult->num_rows > 0) {
                $suspData = $suspResult->fetch_assoc();
                $suspension_due = $suspData['suspension_due'];
                $formattedDate = date("l, F j, Y", strtotime($suspension_due));
                echo json_encode(['status'=>'error','message'=>"Your account is suspended until $formattedDate."]);
                exit;
            } else {
                echo json_encode(['status'=>'error','message'=>'Your account is currently suspended.']);
                exit;
            }
        }

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];

            // Update last_login and status
            $stmt2 = $mysqli->prepare("UPDATE users SET last_login = NOW(), status = 'online' WHERE user_id = ?");
            $stmt2->bind_param("i", $user['user_id']);
            $stmt2->execute();
            $stmt2->close();

            echo json_encode(['status'=>'success','message'=>'Login successful!']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Incorrect password.']);
        }

    } else {
        echo json_encode(['status'=>'error','message'=>'User not found.']);
    }
    $stmt->close();
}

/* ====================================================
   EDIT PROFILE
==================================================== */
elseif ($action === 'edit_profile') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status'=>'error','message'=>'Not logged in.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $stmt = $mysqli->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status'=>'success','message'=>'Profile updated successfully!']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update profile.']);
    }

    $stmt->close();
}

/* ====================================================
   INVALID ACTION
==================================================== */
else {
    echo json_encode(['status'=>'error','message'=>'Invalid action.']);
}
?>
