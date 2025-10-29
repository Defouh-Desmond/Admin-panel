<?php
// ======================================================================
// All admin-related account actions (profile updates, blocking, etc.)
// ======================================================================

require_once '../classes/connection.php';
session_start();

// ----------------------------------------------------------------------
// SECURITY: Ensure admin is logged in
// ----------------------------------------------------------------------
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$action = $_POST['action'] ?? '';

switch ($action) {

    /*
    |----------------------------------------------------------------------
    | UPDATE CURRENT ADMIN PROFILE
    |----------------------------------------------------------------------
    */
    case 'update_profile':
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $profile_picture = null;

        if (empty($full_name) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Full name and email are required']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit;
        }

        // Handle optional profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "../uploads/profile/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            $file_name = basename($_FILES['profile_picture']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_ext, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid image format (JPG, PNG, GIF only)']);
                exit;
            }

            $new_name = 'admin_' . $admin_id . '_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $new_name;

            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
                exit;
            }

            $profile_picture = $new_name;
        }

        // Build update SQL
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?";
        $params = [$full_name, $email, $phone];
        $types = "sss";

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password_hash = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        if (!empty($profile_picture)) {
            $sql .= ", profile_picture = ?";
            $params[] = $profile_picture;
            $types .= "s";
        }

        $sql .= " WHERE user_id = ?";
        $params[] = $admin_id;
        $types .= "i";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $mysqli->error]);
            exit;
        }

        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $_SESSION['admin_name'] = $full_name;
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
        }

        $stmt->close();
        $mysqli->close();
        exit;


    /*
    |----------------------------------------------------------------------
    | BLOCK / UNBLOCK ADMIN
    | Only main_admin can perform this action
    |----------------------------------------------------------------------
    */
    case 'block_admin':
        $target_id = intval($_POST['user_id'] ?? 0);

        if ($target_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid admin ID']);
            exit;
        }

        // Check current admin’s role
        $role_query = $mysqli->prepare("SELECT role FROM admins WHERE user_id = ?");
        $role_query->bind_param("i", $admin_id);
        $role_query->execute();
        $role_result = $role_query->get_result();
        $role_row = $role_result->fetch_assoc();
        $role_query->close();

        if (!$role_row || $role_row['role'] !== 'main_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Only the main admin can block or unblock admins']);
            exit;
        }

        // Prevent blocking self
        if ($target_id === $admin_id) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot block your own account']);
            exit;
        }

        // Verify target admin exists
        $check = $mysqli->prepare("
            SELECT a.role, u.block 
            FROM admins a
            INNER JOIN users u ON a.user_id = u.user_id
            WHERE a.user_id = ?
        ");
        $check->bind_param("i", $target_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
            $check->close();
            exit;
        }

        $target = $result->fetch_assoc();
        $check->close();

        // Prevent blocking another main admin
        if ($target['role'] === 'main_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Main admin accounts cannot be blocked']);
            exit;
        }

        // Toggle block status
        $new_block = $target['block'] ? 0 : 1;
        $stmt = $mysqli->prepare("UPDATE users SET block = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $new_block, $target_id);

        if ($stmt->execute()) {
            $msg = $new_block ? 'Admin has been blocked' : 'Admin has been unblocked';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update admin block status']);
        }

        $stmt->close();
        $mysqli->close();
        exit;
        
        /*
        |----------------------------------------------------------------------
        | SUSPEND / UNSUSPEND USER
        | Only main_admin can perform this action
        |----------------------------------------------------------------------
        */
        case 'suspend_user':
            $target_id = intval($_POST['user_id'] ?? 0);
            $suspension_due = $_POST['suspension_due'] ?? null; // format: 'YYYY-MM-DD HH:MM:SS'
            $reason = trim($_POST['reason'] ?? '');

            if ($target_id <= 0 || empty($suspension_due)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
                exit;
            }

            // Check current admin’s role
            $role_query = $mysqli->prepare("SELECT role FROM admins WHERE user_id = ?");
            $role_query->bind_param("i", $admin_id);
            $role_query->execute();
            $role_result = $role_query->get_result();
            $role_row = $role_result->fetch_assoc();
            $role_query->close();

            if (!$role_row || $role_row['role'] !== 'main_admin') {
                echo json_encode(['status' => 'error', 'message' => 'Only the main admin can suspend users']);
                exit;
            }

            // Prevent suspending self
            if ($target_id === $admin_id) {
                echo json_encode(['status' => 'error', 'message' => 'You cannot suspend your own account']);
                exit;
            }

            // Verify target user exists
            $check = $mysqli->prepare("SELECT suspend FROM users WHERE user_id = ?");
            $check->bind_param("i", $target_id);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
                $check->close();
                exit;
            }

            $target = $result->fetch_assoc();
            $check->close();

            // Start transaction
            $mysqli->begin_transaction();
            try {
                // 1. Toggle suspension
                $new_suspend = $target['suspend'] ? 0 : 1;
                $stmt1 = $mysqli->prepare("UPDATE users SET suspend = ? WHERE user_id = ?");
                $stmt1->bind_param("ii", $new_suspend, $target_id);
                $stmt1->execute();
                $stmt1->close();

                // 2. Insert into suspended_users if suspending
                if ($new_suspend) {
                    $stmt2 = $mysqli->prepare("INSERT INTO suspended_users (user_id, suspension_due, reason) VALUES (?, ?, ?)");
                    $stmt2->bind_param("iss", $target_id, $suspension_due, $reason);
                    $stmt2->execute();
                    $stmt2->close();
                }

                $mysqli->commit();
                $msg = $new_suspend ? 'User has been suspended' : 'User suspension lifted';
                echo json_encode(['status' => 'success', 'message' => $msg]);

            } catch (Exception $e) {
                $mysqli->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Failed to update suspension: ' . $e->getMessage()]);
            }

            $mysqli->close();
            exit;

    /*
    |----------------------------------------------------------------------
    | INVALID ACTION
    |----------------------------------------------------------------------
    */
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
}
