<?php
require_once '../classes/connection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$action = $_POST['action'] ?? '';

if ($action === 'mark_delivered') {
    $order_id = intval($_POST['order_id'] ?? 0);

    if ($order_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid order ID.']);
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE orders SET delivery_status = 'delivered' WHERE order_id = ? AND support_admin_id = ?");
    $stmt->bind_param("ii", $order_id, $admin_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Order marked as delivered.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update order.']);
    }

    $stmt->close();
    exit;
}

if ($action === 'update_location') {
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    if ($latitude === null || $longitude === null) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid coordinates.']);
        exit;
    }

    // Update admins table with last known location
    $stmt = $mysqli->prepare("UPDATE admins SET last_latitude = ?, last_longitude = ? WHERE admin_id = ?");
    $stmt->bind_param("ddi", $latitude, $longitude, $admin_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Location updated.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update location.']);
    }

    $stmt->close();
    exit;
}

// Unknown action
echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
exit;
?>
