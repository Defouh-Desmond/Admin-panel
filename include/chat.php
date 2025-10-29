<?php
session_start();
require_once '../classes/connection.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$admin_id = $_SESSION['admin_id'];

/* ====================================================
   HANDLE GET REQUESTS
==================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {

    /* ----------------------------------------------------
       GET ALL CONTACTS (other admins)
    ---------------------------------------------------- */
    if ($_GET['action'] === 'contacts') {
        $query = "
            SELECT 
                a.admin_id, 
                u.full_name, 
                u.profile_picture, 
                a.role,
                IF(ac.admin_id IS NOT NULL AND TIMESTAMPDIFF(SECOND, ac.last_seen, NOW()) < 20, 1, 0) AS online,
                IFNULL(ac.is_typing, 0) AS is_typing
            FROM admins a
            JOIN users u ON a.user_id = u.user_id
            LEFT JOIN admin_activity ac ON a.admin_id = ac.admin_id
            WHERE a.admin_id != $admin_id
            ORDER BY u.full_name ASC
        ";

        $result = $mysqli->query($query);
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $row['online'] = (bool)$row['online'];
            $row['is_typing'] = (bool)$row['is_typing'];
            $admins[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($admins);
        exit();
    }

    /* ----------------------------------------------------
       GET ALL ADMIN STATUSES + UNREAD MESSAGE FLAGS
    ---------------------------------------------------- */
    if ($_GET['action'] === 'status_all') {
        $statuses = [];

        // 1️⃣ Online & Typing
        $res = $mysqli->query("
            SELECT admin_id, 
                   IF(TIMESTAMPDIFF(SECOND,last_seen,NOW())<20,1,0) AS online, 
                   IFNULL(is_typing,0) AS is_typing
            FROM admin_activity
        ");
        while ($row = $res->fetch_assoc()) {
            $statuses[$row['admin_id']] = [
                'online' => (bool)$row['online'],
                'is_typing' => (bool)$row['is_typing'],
                'has_new_message' => false
            ];
        }

        // 2️⃣ Unread messages
        $msgRes = $mysqli->query("
            SELECT sender_admin_id, COUNT(*) AS cnt
            FROM admin_messages
            WHERE recipient_admin_id = $admin_id 
              AND seen = 0
            GROUP BY sender_admin_id
        ");
        while ($msg = $msgRes->fetch_assoc()) {
            $sid = $msg['sender_admin_id'];
            if (!isset($statuses[$sid])) {
                $statuses[$sid] = ['online' => false, 'is_typing' => false];
            }
            $statuses[$sid]['has_new_message'] = true;
        }

        header('Content-Type: application/json');
        echo json_encode($statuses);
        exit();
    }

    /* ----------------------------------------------------
       FETCH CHAT MESSAGES (with optional "since" filter)
    ---------------------------------------------------- */
    if ($_GET['action'] === 'fetch' && isset($_GET['recipient_admin_id'])) {
        $recipient_admin_id = intval($_GET['recipient_admin_id']);
        $since = isset($_GET['since']) ? $mysqli->real_escape_string($_GET['since']) : null;

        $query = "
            SELECT 
                m.*, 
                u.full_name, 
                u.profile_picture
            FROM admin_messages m
            JOIN admins a ON m.sender_admin_id = a.admin_id
            JOIN users u ON a.user_id = u.user_id
            WHERE ((m.sender_admin_id = $admin_id AND m.recipient_admin_id = $recipient_admin_id)
               OR (m.sender_admin_id = $recipient_admin_id AND m.recipient_admin_id = $admin_id))
        ";

        if ($since) {
            $query .= " AND m.sent_at > '$since'";
        }

        $query .= " ORDER BY m.sent_at ASC";

        $result = $mysqli->query($query);
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        // ✅ Mark messages from recipient as seen when retrieved
        $mysqli->query("
            UPDATE admin_messages 
            SET seen = 1 
            WHERE sender_admin_id = $recipient_admin_id 
              AND recipient_admin_id = $admin_id
        ");

        header('Content-Type: application/json');
        echo json_encode($messages);
        exit();
    }

    /* ----------------------------------------------------
       CHECK SINGLE ADMIN STATUS
    ---------------------------------------------------- */
    if ($_GET['action'] === 'status' && isset($_GET['admin_id'])) {
        $target_id = intval($_GET['admin_id']);
        $res = $mysqli->query("
            SELECT 
                IF(admin_id IS NOT NULL AND TIMESTAMPDIFF(SECOND, last_seen, NOW()) < 20, 1, 0) AS online,
                IFNULL(is_typing, 0) AS is_typing
            FROM admin_activity
            WHERE admin_id = $target_id
        ");
        $row = $res ? $res->fetch_assoc() : ['online'=>0,'is_typing'=>0];
        header('Content-Type: application/json');
        echo json_encode(['online'=>(bool)$row['online'],'is_typing'=>(bool)$row['is_typing']]);
        exit();
    }
}

/* ====================================================
   HANDLE POST REQUESTS
==================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    /* ----------------------------------------------------
       SEND MESSAGE
    ---------------------------------------------------- */
    if ($_POST['action'] === 'send' && isset($_POST['recipient_admin_id'], $_POST['message'])) {
        $recipient_admin_id = intval($_POST['recipient_admin_id']);
        $message = trim($_POST['message']);
        if ($message === '') exit();

        $stmt = $mysqli->prepare("
            INSERT INTO admin_messages (sender_admin_id, recipient_admin_id, message, seen)
            VALUES (?, ?, ?, 0)
        ");
        $stmt->bind_param("iis", $admin_id, $recipient_admin_id, $message);
        $stmt->execute();
        $stmt->close();

        header('Content-Type: application/json');
        echo json_encode(['status'=>'ok']);
        exit();
    }

    /* ----------------------------------------------------
       HEARTBEAT (keeps admin online)
    ---------------------------------------------------- */
    if ($_POST['action'] === 'heartbeat') {
        $mysqli->query("
            INSERT INTO admin_activity (admin_id, last_seen, is_typing)
            VALUES ($admin_id, NOW(), 0)
            ON DUPLICATE KEY UPDATE last_seen = NOW()
        ");
        exit();
    }

    /* ----------------------------------------------------
       TYPING INDICATOR
    ---------------------------------------------------- */
    if ($_POST['action'] === 'typing' && isset($_POST['is_typing'])) {
        $is_typing = intval($_POST['is_typing']);
        $mysqli->query("
            INSERT INTO admin_activity (admin_id, last_seen, is_typing)
            VALUES ($admin_id, NOW(), $is_typing)
            ON DUPLICATE KEY UPDATE is_typing = $is_typing, last_seen = NOW()
        ");
        exit();
    }
}

/* ====================================================
   DEFAULT RESPONSE (bad request)
==================================================== */
http_response_code(400);
exit('Bad Request');
?>
