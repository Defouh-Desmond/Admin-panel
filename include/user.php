<?php
// ======================================================================
// User-related account actions (blocking, suspending) for admins only
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

// ----------------------------------------------------------------------
// Check current admin role
// ----------------------------------------------------------------------
$role_query = $mysqli->prepare("SELECT role FROM admins WHERE user_id = ?");
$role_query->bind_param("i", $admin_id);
$role_query->execute();
$role_result = $role_query->get_result();
$role_row = $role_result->fetch_assoc();
$role_query->close();

if (!$role_row || $role_row['role'] !== 'main_admin') {
    echo json_encode(['status' => 'error', 'message' => 'Only main admin can perform this action']);
    exit;
}

// ----------------------------------------------------------------------
// ACTION SWITCH
// ----------------------------------------------------------------------
switch ($action) {

    /*
    |---------------------------------------------------------------------- 
    | BLOCK / UNBLOCK USER
    |---------------------------------------------------------------------- 
    */
    case 'block_user':
        $target_id = intval($_POST['user_id'] ?? 0);
        if ($target_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
            exit;
        }

        if ($target_id === $admin_id) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot block yourself']);
            exit;
        }

        // Ensure target is NOT an admin
        $check_admin = $mysqli->prepare("SELECT user_id FROM admins WHERE user_id = ?");
        $check_admin->bind_param("i", $target_id);
        $check_admin->execute();
        $res_admin = $check_admin->get_result();
        $check_admin->close();

        if ($res_admin->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Admins cannot be blocked']);
            exit;
        }

        // Toggle block status
        $check_user = $mysqli->prepare("SELECT block FROM users WHERE user_id = ?");
        $check_user->bind_param("i", $target_id);
        $check_user->execute();
        $res_user = $check_user->get_result();
        $user = $res_user->fetch_assoc();
        $check_user->close();

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }

        $new_block = $user['block'] ? 0 : 1;
        $stmt = $mysqli->prepare("UPDATE users SET block = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $new_block, $target_id);
        if ($stmt->execute()) {
            $msg = $new_block ? 'User has been blocked' : 'User has been unblocked';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update block status']);
        }
        $stmt->close();
        $mysqli->close();
        exit;

    /*
    |---------------------------------------------------------------------- 
    | SUSPEND / UNSUSPEND USER
    |---------------------------------------------------------------------- 
    */
    case 'suspend_user':
        $target_id = intval($_POST['user_id'] ?? 0);
        $suspension_due = $_POST['suspension_due'] ?? null; // 'YYYY-MM-DD HH:MM:SS'
        $reason = trim($_POST['reason'] ?? '');

        if ($target_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
            exit;
        }

        if ($target_id === $admin_id) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot suspend yourself']);
            exit;
        }

        // Ensure target is NOT an admin
        $check_admin = $mysqli->prepare("SELECT user_id FROM admins WHERE user_id = ?");
        $check_admin->bind_param("i", $target_id);
        $check_admin->execute();
        $res_admin = $check_admin->get_result();
        $check_admin->close();

        if ($res_admin->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Admins cannot be suspended']);
            exit;
        }

        // Fetch current suspension
        $check_user = $mysqli->prepare("SELECT suspend FROM users WHERE user_id = ?");
        $check_user->bind_param("i", $target_id);
        $check_user->execute();
        $res_user = $check_user->get_result();
        $user = $res_user->fetch_assoc();
        $check_user->close();

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }

        // Determine new suspend status
        $new_suspend = $user['suspend'] ? 0 : 1;

        // Start transaction
        $mysqli->begin_transaction();
        try {
            // Update users table
            $stmt1 = $mysqli->prepare("UPDATE users SET suspend = ? WHERE user_id = ?");
            $stmt1->bind_param("ii", $new_suspend, $target_id);
            $stmt1->execute();
            $stmt1->close();

            if ($new_suspend) {
                if (empty($suspension_due)) {
                    throw new Exception('Suspension due date required');
                }
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
