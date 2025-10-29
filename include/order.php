<?php
require_once '../classes/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {

        // =============================
        // ASSIGN ORDER TO SUPPORT ADMIN
        // =============================
        case 'assign_order':
            $order_id = intval($_POST['order_id']);
            $support_admin_id = intval($_POST['support_admin_id']);
            $changed_by_admin_id = $_SESSION['admin_id'];

            // Get current delivery status
            $res = $mysqli->query("SELECT delivery_status FROM orders WHERE order_id = $order_id");
            $old_status = $res->fetch_assoc()['delivery_status'];

            // Update orders table
            $stmt = $mysqli->prepare("
                UPDATE orders 
                SET support_admin_id = ?, delivery_status = 'processing', updated_at = NOW()
                WHERE order_id = ?
            ");
            $stmt->bind_param('ii', $support_admin_id, $order_id);
            $stmt->execute();
            $stmt->close();

            // Insert into history
            $stmt = $mysqli->prepare("
                INSERT INTO order_status_history (order_id, changed_by_admin_id, old_status, new_status)
                VALUES (?, ?, ?, 'processing')
            ");
            $stmt->bind_param('iis', $order_id, $changed_by_admin_id, $old_status);
            $stmt->execute();
            $stmt->close();

            echo "<script>alert('Support admin assigned successfully!'); window.location='../pages/orders.php';</script>";
            break;

        // =============================
        // MARK ORDER AS OUT FOR DELIVERY
        // =============================
        case 'start_delivery':
            $order_id = intval($_POST['order_id']);
            $admin_id = $_SESSION['admin_id'];

            $stmt = $mysqli->prepare("
                UPDATE orders 
                SET delivery_status = 'out_for_delivery', updated_at = NOW()
                WHERE order_id = ? AND support_admin_id = ?
            ");
            $stmt->bind_param('ii', $order_id, $admin_id);
            $stmt->execute();
            $stmt->close();

            // Insert history
            $mysqli->query("
                INSERT INTO order_status_history (order_id, changed_by_admin_id, old_status, new_status)
                VALUES ($order_id, $admin_id, 'processing', 'out_for_delivery')
            ");

            header("Location: ../pages/delivery.php");
            break;

        // =============================
        // MARK ORDER AS DELIVERED
        // =============================
        case 'mark_delivered':
            $order_id = intval($_POST['order_id']);
            $admin_id = $_SESSION['admin_id'];

            $stmt = $mysqli->prepare("
                UPDATE orders 
                SET delivery_status = 'delivered', updated_at = NOW()
                WHERE order_id = ? AND support_admin_id = ?
            ");
            $stmt->bind_param('ii', $order_id, $admin_id);
            $stmt->execute();
            $stmt->close();

            // Insert history
            $mysqli->query("
                INSERT INTO order_status_history (order_id, changed_by_admin_id, old_status, new_status)
                VALUES ($order_id, $admin_id, 'out_for_delivery', 'delivered')
            ");

            echo "<script>alert('Order marked as delivered!'); window.location='../pages/delivery.php';</script>";
            break;

        default:
            echo "<script>alert('Invalid action!'); window.history.back();</script>";
            break;
    }
}
?>
