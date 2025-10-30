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
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $password = $_POST['password'] ?? '';

    if ((empty($email) && empty($phone)) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide your login details.']);
        exit;
    }

    // Determine whether we're logging in via email or phone
    if (!empty($email)) {
        $stmt = $mysqli->prepare("SELECT user_id, full_name, password_hash, block, suspend FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
    } else {
        $stmt = $mysqli->prepare("SELECT user_id, full_name, password_hash, block, suspend FROM users WHERE phone = ? LIMIT 1");
        $stmt->bind_param("s", $phone);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if blocked
        if ($user['block'] == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Your account has been blocked. Please contact support.']);
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
                echo json_encode(['status' => 'error', 'message' => "Your account is suspended until $formattedDate."]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Your account is currently suspended.']);
                exit;
            }
        }

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];

            // Update last login & status
            $updateStmt = $mysqli->prepare("UPDATE users SET last_login = NOW(), status = 'online' WHERE user_id = ?");
            $updateStmt->bind_param("i", $user['user_id']);
            $updateStmt->execute();
            $updateStmt->close();

            echo json_encode(['status' => 'success', 'message' => 'Login successful!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }

    $stmt->close();
}

/* ====================================================
   GET PROFILE
==================================================== */
elseif ($action === 'get_profile') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status'=>'error','message'=>'Not logged in.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT full_name, email, phone, address, profile_picture FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    echo json_encode(['status'=>'success','data'=>$user]);
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
    $full_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate password match if changing
    if (!empty($password) && $password !== $confirm_password) {
        echo json_encode(['status'=>'error','message'=>'Passwords do not match.']);
        exit;
    }

    // Check for email or phone duplicates
    $stmtCheck = $mysqli->prepare("SELECT user_id FROM users WHERE (email = ? OR phone = ?) AND user_id != ?");
    $stmtCheck->bind_param("ssi", $email, $phone, $user_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if($resCheck->num_rows > 0) {
        echo json_encode(['status'=>'error','message'=>'Email or phone already in use.']);
        exit;
    }
    $stmtCheck->close();

    // Handle optional profile picture
    $profile_picture = '';
    if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0){
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $profile_picture = 'profile_'.$user_id.'_'.time().'.'.$ext;
        $target = '../../uploads/'.$profile_picture;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target);
    }

    // Build update query
    $updateFields = "full_name = ?, email = ?, phone = ?, address = ?";
    $params = [$full_name, $email, $phone, $address];
    $types = "ssss";

    if(!empty($password)){
        $updateFields .= ", password_hash = ?";
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $params[] = $password_hash;
        $types .= "s";
    }

    if(!empty($profile_picture)){
        $updateFields .= ", profile_picture = ?";
        $params[] = $profile_picture;
        $types .= "s";
    }

    $params[] = $user_id;
    $types .= "i";

    $sql = "UPDATE users SET $updateFields, updated_at = NOW() WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if($stmt->execute()){
        echo json_encode(['status'=>'success','message'=>'Profile updated successfully!']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update profile.']);
    }
    $stmt->close();
}

/* ====================================================
   UPLOAD PROFILE PICTURE (Instant Upload)
==================================================== */
elseif ($action === 'upload_profile_picture') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status'=>'error','message'=>'Not logged in.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== 0) {
        echo json_encode(['status'=>'error','message'=>'No image uploaded or upload error.']);
        exit;
    }

    $file = $_FILES['profile_picture'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['status'=>'error','message'=>'Invalid image format. Only JPG, PNG, GIF, or WEBP allowed.']);
        exit;
    }

    // --- Get current profile picture to delete it later ---
    $stmtOld = $mysqli->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
    $stmtOld->bind_param("i", $user_id);
    $stmtOld->execute();
    $resultOld = $stmtOld->get_result();
    $oldPic = '';
    if ($resultOld->num_rows > 0) {
        $data = $resultOld->fetch_assoc();
        $oldPic = $data['profile_picture'];
    }
    $stmtOld->close();

    // --- Generate new file name ---
    $profile_picture = 'profile_'.$user_id.'_'.time().'.'.$ext;
    $target = '../../uploads/'.$profile_picture;

    // --- Move new file ---
    if (move_uploaded_file($file['tmp_name'], $target)) {

        // --- Delete old file if it exists ---
        if (!empty($oldPic)) {
            $oldPath = '../../uploads/'.$oldPic;
            if (file_exists($oldPath) && is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        // --- Update database ---
        $stmt = $mysqli->prepare("UPDATE users SET profile_picture = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->bind_param("si", $profile_picture, $user_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Profile picture updated successfully!',
            'new_picture' => $profile_picture
        ]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to save profile picture.']);
    }
}

/* ====================================================
   FORGOT PASSWORD (Email or Phone)
==================================================== */
elseif ($action === 'forgot_password') {
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (empty($email) && empty($phone)) {
        echo json_encode(['status'=>'error','message'=>'Please provide your email or phone.']);
        exit;
    }

    // Find user by email or phone
    if (!empty($email)) {
        $stmt = $mysqli->prepare("SELECT user_id, full_name, email, phone FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
    } else {
        $stmt = $mysqli->prepare("SELECT user_id, full_name, email, phone FROM users WHERE phone = ? LIMIT 1");
        $stmt->bind_param("s", $phone);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo json_encode(['status'=>'error','message'=>'User not found.']);
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

    // Store token in password_resets table
    $stmtInsert = $mysqli->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmtInsert->bind_param("iss", $user['user_id'], $token, $expires_at);
    $stmtInsert->execute();
    $stmtInsert->close();

    // Prepare reset link
    $resetLink = "https://yourdomain.com/reset_password.php?token=$token";

    // If email is available, send via email
    if (!empty($user['email'])) {
        $to = $user['email'];
        $subject = "Password Reset Request";
        $message = "Hi {$user['full_name']},\n\n";
        $message .= "We received a request to reset your password.\n";
        $message .= "Click the link below to reset your password:\n\n";
        $message .= "$resetLink\n\n";
        $message .= "If you didn't request this, ignore this email.\n";
        $headers = "From: no-reply@yourdomain.com\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo json_encode(['status'=>'success','message'=>'A password reset link has been sent to your email.']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to send reset email. Please try again later.']);
        }
    } 
    // If phone is provided, send SMS (requires SMS gateway)
    else if (!empty($user['phone'])) {
        // Example placeholder for SMS sending
        $smsSent = true; // Replace with your SMS API call

        if ($smsSent) {
            echo json_encode(['status'=>'success','message'=>'A password reset link has been sent to your phone via SMS.']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to send reset SMS. Please try again later.']);
        }
    } 
    else {
        echo json_encode(['status'=>'error','message'=>'No valid contact method found.']);
    }
}

/* ====================================================
   INVALID ACTION
==================================================== */
else {
    echo json_encode(['status'=>'error','message'=>'Invalid action.']);
}
?>
