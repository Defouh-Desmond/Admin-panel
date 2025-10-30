<?php
// auth.php

require_once '../classes/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ---------------------------
    // SIGNUP LOGIC
    // ---------------------------
    if ($action === 'signup') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];

        // Basic validation
        if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
            echo json_encode(['status'=>'error','message'=>'All fields are required.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status'=>'error','message'=>'Invalid email format.']);
            exit;
        }

        // Check if email or phone already exists
        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email=? OR phone=?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(['status'=>'error','message'=>'Email or phone already exists.']);
            exit;
        }
        $stmt->close();

        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users
        $stmt = $mysqli->prepare("INSERT INTO users (full_name,email,phone,password_hash,status,is_verified) VALUES (?, ?, ?, ?, 'offline', 1)");
        $stmt->bind_param("ssss", $full_name, $email, $phone, $password_hash);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            // Insert into admins
            $role = 'manager';
            $permissions = null;
            $stmt = $mysqli->prepare("INSERT INTO admins (user_id, role, permissions, active) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("iss", $user_id, $role, $permissions);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['status'=>'success','message'=>'Admin registered successfully!']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Signup failed.']);
        }

    // ---------------------------
    // LOGIN LOGIC
    // ---------------------------
    } elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
            exit;
        }

        // Fetch user + admin info
        $stmt = $mysqli->prepare("
            SELECT 
                u.user_id,
                u.password_hash,
                u.full_name,
                u.status,
                u.block,
                u.suspend,
                a.role,
                a.active
            FROM users u
            INNER JOIN admins a ON u.user_id = a.user_id
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // ✅ Check if admin account is active
            if ($row['active'] != 1) {
                echo json_encode(['status' => 'error', 'message' => 'Admin account is inactive.']);
                exit;
            }

            // ✅ Check if user is blocked
            if ($row['block'] == 1 || $row['status'] === 'blocked') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Your account has been blocked. Please contact support.'
                ]);
                exit;
            }

            // ✅ Check if user is suspended
            if ($row['suspend'] == 1 || $row['status'] === 'suspended') {

                // Fetch latest suspension record
                $suspStmt = $mysqli->prepare("
                    SELECT suspension_id, suspension_due 
                    FROM suspended_users 
                    WHERE user_id = ? 
                    ORDER BY suspended_at DESC 
                    LIMIT 1
                ");
                $suspStmt->bind_param("i", $row['user_id']);
                $suspStmt->execute();
                $suspResult = $suspStmt->get_result();

                if ($suspResult->num_rows > 0) {
                    $suspData = $suspResult->fetch_assoc();
                    $suspension_id = $suspData['suspension_id'];
                    $suspension_due = $suspData['suspension_due'];

                    $currentDate = new DateTime();
                    $dueDate = new DateTime($suspension_due);

                    // Check if suspension is still active
                    if ($dueDate > $currentDate) {
                        $formattedDate = $dueDate->format("l, F j, Y");
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Your account is suspended until ' . $formattedDate . '.'
                        ]);
                        exit;
                    } else {
                        // ✅ Suspension expired — reactivate the account
                        $updateStmt = $mysqli->prepare("
                            UPDATE users 
                            SET suspend = 0, status = 'offline' 
                            WHERE user_id = ?
                        ");
                        $updateStmt->bind_param("i", $row['user_id']);
                        $updateStmt->execute();
                        $updateStmt->close();

                        // 🧹 Delete expired suspension record
                        $delStmt = $mysqli->prepare("DELETE FROM suspended_users WHERE suspension_id = ?");
                        $delStmt->bind_param("i", $suspension_id);
                        $delStmt->execute();
                        $delStmt->close();
                    }
                } else {
                    // No record found but suspend flag is set
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Your account is currently suspended.'
                    ]);
                    exit;
                }
                $suspStmt->close();
            }

            // ✅ Verify password
            if (password_verify($password, $row['password_hash'])) {
                // Set session
                $_SESSION['admin_id'] = $row['user_id'];
                $_SESSION['admin_name'] = $row['full_name'];
                $_SESSION['admin_role'] = $row['role'];

                // Update last_login in admins table
                $stmt2 = $mysqli->prepare("UPDATE admins SET last_login = NOW() WHERE user_id = ?");
                $stmt2->bind_param("i", $row['user_id']);
                $stmt2->execute();
                $stmt2->close();

                // Update user status to online
                $stmt3 = $mysqli->prepare("UPDATE users SET status = 'online', last_login = NOW() WHERE user_id = ?");
                $stmt3->bind_param("i", $row['user_id']);
                $stmt3->execute();
                $stmt3->close();

                echo json_encode(['status' => 'success', 'message' => 'Login successful!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
            }

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Admin not found.']);
        }

        $stmt->close();
    }
     else {
        echo json_encode(['status'=>'error','message'=>'Invalid action.']);
    }

    $mysqli->close();
 
    // ---------------------------
    // BECOME ADMIN LOGIC
    // ---------------------------
    
    }elseif ($action === 'become_admin') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
            exit;
        }

        // Check if user exists in users table
        $stmt = $mysqli->prepare("SELECT user_id, full_name, password_hash, block, suspend, status FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check block/suspend status
            if ($user['block'] == 1) {
                echo json_encode(['status' => 'error', 'message' => 'Your account is blocked. You cannot become an admin.']);
                exit;
            }

            if ($user['suspend'] == 1) {
                echo json_encode(['status' => 'error', 'message' => 'Your account is suspended. Please wait until suspension ends.']);
                exit;
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
                exit;
            }

            // Check if already admin
            $checkAdmin = $mysqli->prepare("SELECT admin_id FROM admins WHERE user_id = ? LIMIT 1");
            $checkAdmin->bind_param("i", $user['user_id']);
            $checkAdmin->execute();
            $adminResult = $checkAdmin->get_result();

            if ($adminResult->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'You are already an admin.']);
                exit;
            }

            // Insert new admin record
            $insert = $mysqli->prepare("INSERT INTO admins (user_id, role, active, created_at) VALUES (?, 'standard', 1, NOW())");
            $insert->bind_param("i", $user['user_id']);

            if ($insert->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'You are now an admin! You can log in to the admin dashboard.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error creating admin account. Please try again.']);
            }

            $insert->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No user found with that email. Please sign up first.']);
        }

        $stmt->close();
    }else {
        echo json_encode(['status'=>'error','message'=>'Invalid request method.']);
    }
?>
