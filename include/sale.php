<?php
require_once '../classes/connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {

        // =============================
        // ADD A NEW IN-SHOP SALE
        // =============================
        case 'add':
            $customer_name = !empty($_POST['customer_name']) ? trim($_POST['customer_name']) : null;
            $customer_phone = !empty($_POST['customer_phone']) ? trim($_POST['customer_phone']) : null;
            $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];

            if (empty($items)) {
                echo json_encode(['status' => 'error', 'message' => 'No products selected.']);
                exit;
            }

            $mysqli->begin_transaction();
            try {
                $stmt = $mysqli->prepare("INSERT INTO sales (customer_name, customer_phone, total_amount) VALUES (?, ?, 0.00)");
                $stmt->bind_param('ss', $customer_name, $customer_phone);
                $stmt->execute();
                $sale_id = $stmt->insert_id;
                $stmt->close();

                $total_amount = 0;
                foreach ($items as $item) {
                    $product_id = (int)$item['product_id'];
                    $quantity = (int)$item['quantity'];

                    $p_stmt = $mysqli->prepare("SELECT price, stock_quantity FROM products WHERE product_id=? LIMIT 1");
                    $p_stmt->bind_param("i", $product_id);
                    $p_stmt->execute();
                    $p_stmt->bind_result($price, $stock_quantity);
                    $p_stmt->fetch();
                    $p_stmt->close();

                    if ($quantity > $stock_quantity) {
                        throw new Exception("Not enough stock for product ID $product_id.");
                    }

                    $subtotal = $price * $quantity;
                    $total_amount += $subtotal;

                    $i_stmt = $mysqli->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $i_stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $price);
                    $i_stmt->execute();
                    $i_stmt->close();

                    $u_stmt = $mysqli->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                    $u_stmt->bind_param("ii", $quantity, $product_id);
                    $u_stmt->execute();
                    $u_stmt->close();
                }

                $update_stmt = $mysqli->prepare("UPDATE sales SET total_amount = ? WHERE sale_id = ?");
                $update_stmt->bind_param("di", $total_amount, $sale_id);
                $update_stmt->execute();
                $update_stmt->close();

                $mysqli->commit();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Sale recorded successfully!',
                    'sale_id' => $sale_id,
                    'total' => $total_amount
                ]);
            } catch (Exception $e) {
                $mysqli->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Sale failed: ' . $e->getMessage()]);
            }
            break;

        // =============================
        // DELETE SALE
        // =============================
        case 'delete':
            $sale_id = (int) $_POST['sale_id'];
            $stmt = $mysqli->prepare("DELETE FROM sales WHERE sale_id = ?");
            $stmt->bind_param("i", $sale_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Sale deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete sale.']);
            }
            $stmt->close();
            break;

        // =============================
        // FETCH SALE ITEMS
        // =============================
        case 'fetch_items':
            $sale_id = (int) $_POST['sale_id'];
            $stmt = $mysqli->prepare("
                SELECT si.quantity, si.price, si.subtotal, p.name AS product_name
                FROM sale_items si
                JOIN products p ON si.product_id = p.product_id
                WHERE si.sale_id = ?
            ");
            $stmt->bind_param("i", $sale_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $items = [];
                $total = 0;
                while ($row = $res->fetch_assoc()) {
                    $items[] = $row;
                    $total += $row['subtotal'];
                }
                echo json_encode(['status' => 'success', 'items' => $items, 'total' => $total]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No items found for this sale']);
            }
            $stmt->close();
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
