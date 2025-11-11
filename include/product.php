<?php
require_once '../classes/connection.php';
session_start();

header('Content-Type: application/json');

// =============================
// ROLE VALIDATION
// =============================
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Not logged in.']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$stmt = $mysqli->prepare("SELECT role FROM admins WHERE admin_id = ? LIMIT 1");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_result = $stmt->get_result();
$admin = $admin_result->fetch_assoc();
$stmt->close();

// if (!$admin || !in_array($admin['role'], ['main_admin', 'manager'])) {
//     echo json_encode(['status' => 'error', 'message' => 'Access denied. You do not have permission to perform this action.']);
//     exit;
// }

// =============================
// MAIN ACTION HANDLER
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {

        // =============================
        // ADD PRODUCT
        // =============================
        case 'add':
            $name = trim($_POST['name']);
            $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
            $category_id = (int) $_POST['category_id'];
            $description = trim($_POST['description']);
            $price = (float) $_POST['price'];
            $stock = (int) $_POST['stock_quantity'];
            $status = $_POST['status'];

            $stmt = $mysqli->prepare("
                INSERT INTO products (category_id, name, slug, description, price, stock_quantity, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssdis", $category_id, $name, $slug, $description, $price, $stock, $status);

            if ($stmt->execute()) {
                $product_id = $stmt->insert_id;
                $stmt->close();

                // Handle image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $upload_dir = '../uploads/products/';
                    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                        $target_path = $upload_dir . $file_name;

                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $is_main = ($key == 0) ? 1 : 0;
                            $img_stmt = $mysqli->prepare("
                                INSERT INTO product_images (product_id, image_path, is_main)
                                VALUES (?, ?, ?)
                            ");
                            $img_stmt->bind_param("isi", $product_id, $file_name, $is_main);
                            $img_stmt->execute();
                            $img_stmt->close();
                        }
                    }
                }

                echo json_encode(['status' => 'success', 'message' => 'Product added successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add product: ' . $stmt->error]);
            }
            break;

        // =============================
        // UPDATE PRODUCT
        // =============================
        case 'update':
            $product_id = (int) $_POST['product_id'];
            $name = trim($_POST['name']);
            $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
            $category_id = (int) $_POST['category_id'];
            $description = trim($_POST['description']);
            $price = (float) $_POST['price'];
            $stock = (int) $_POST['stock_quantity'];
            $status = $_POST['status'];

            $stmt = $mysqli->prepare("
                UPDATE products
                SET category_id=?, name=?, slug=?, description=?, price=?, stock_quantity=?, status=?
                WHERE product_id=?
            ");
            $stmt->bind_param("isssdisi", $category_id, $name, $slug, $description, $price, $stock, $status, $product_id);

            if ($stmt->execute()) {
                $stmt->close();

                // Check if main image exists
                $has_main = false;
                $check_main = $mysqli->prepare("SELECT COUNT(*) FROM product_images WHERE product_id=? AND is_main=1");
                $check_main->bind_param("i", $product_id);
                $check_main->execute();
                $check_main->bind_result($main_count);
                $check_main->fetch();
                $check_main->close();
                $has_main = ($main_count > 0);

                // Handle new image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $upload_dir = '../uploads/products/';
                    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                        $target_path = $upload_dir . $file_name;

                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $is_main = (!$has_main && $key == 0) ? 1 : 0;

                            $img_stmt = $mysqli->prepare("
                                INSERT INTO product_images (product_id, image_path, is_main)
                                VALUES (?, ?, ?)
                            ");
                            $img_stmt->bind_param("isi", $product_id, $file_name, $is_main);
                            $img_stmt->execute();
                            $img_stmt->close();

                            if ($is_main) $has_main = true;
                        }
                    }
                }

                echo json_encode(['status' => 'success', 'message' => 'Product updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update product: ' . $stmt->error]);
            }
            break;

        // =============================
        // DELETE PRODUCT
        // =============================
        case 'delete':
            $product_id = (int) $_POST['product_id'];

            // Delete images from server
            $img_stmt = $mysqli->prepare("SELECT image_path FROM product_images WHERE product_id=?");
            $img_stmt->bind_param("i", $product_id);
            $img_stmt->execute();
            $img_result = $img_stmt->get_result();
            while ($img = $img_result->fetch_assoc()) {
                $file_path = '../uploads/products/' . $img['image_path'];
                if (file_exists($file_path)) unlink($file_path);
            }
            $img_stmt->close();

            // Delete DB records
            $stmt_del = $mysqli->prepare("DELETE FROM product_images WHERE product_id=?");
            $stmt_del->bind_param("i", $product_id);
            $stmt_del->execute();
            $stmt_del->close();

            $stmt_del = $mysqli->prepare("DELETE FROM products WHERE product_id=?");
            $stmt_del->bind_param("i", $product_id);
            if ($stmt_del->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete product: ' . $stmt_del->error]);
            }
            $stmt_del->close();
            break;

        // =============================
        // DELETE IMAGE
        // =============================
        case 'delete_image':
            $image_id = (int) $_POST['image_id'];

            // Get image details
            $stmt_img = $mysqli->prepare("SELECT product_id, image_path, is_main FROM product_images WHERE image_id=? LIMIT 1");
            $stmt_img->bind_param("i", $image_id);
            $stmt_img->execute();
            $res_img = $stmt_img->get_result();

            if ($res_img && $res_img->num_rows > 0) {
                $img = $res_img->fetch_assoc();
                $product_id = $img['product_id'];
                $file_path = '../uploads/products/' . $img['image_path'];
                $was_main = (bool)$img['is_main'];
                $stmt_img->close();

                // Delete record
                $stmt_del = $mysqli->prepare("DELETE FROM product_images WHERE image_id=?");
                $stmt_del->bind_param("i", $image_id);
                if ($stmt_del->execute()) {
                    if (file_exists($file_path)) unlink($file_path);

                    // If it was main image, set another as main if exists
                    if ($was_main) {
                        $next_stmt = $mysqli->prepare("
                            SELECT image_id FROM product_images 
                            WHERE product_id=? ORDER BY image_id ASC LIMIT 1
                        ");
                        $next_stmt->bind_param("i", $product_id);
                        $next_stmt->execute();
                        $next_stmt->bind_result($next_image_id);
                        if ($next_stmt->fetch()) {
                            $update_main = $mysqli->prepare("UPDATE product_images SET is_main=1 WHERE image_id=?");
                            $update_main->bind_param("i", $next_image_id);
                            $update_main->execute();
                            $update_main->close();
                        }
                        $next_stmt->close();
                    }

                    echo json_encode(['status' => 'success', 'message' => 'Image deleted successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to delete image.']);
                }
                $stmt_del->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Image not found.']);
            }
            break;

        // =============================
        // SET MAIN IMAGE
        // =============================
        case 'set_main_image':
            $image_id = (int) $_POST['image_id'];

            $stmt_img = $mysqli->prepare("SELECT product_id FROM product_images WHERE image_id=? LIMIT 1");
            $stmt_img->bind_param("i", $image_id);
            $stmt_img->execute();
            $res_img = $stmt_img->get_result();

            if ($res_img && $res_img->num_rows > 0) {
                $img = $res_img->fetch_assoc();
                $product_id = $img['product_id'];
                $stmt_img->close();

                $stmt_reset = $mysqli->prepare("UPDATE product_images SET is_main=0 WHERE product_id=?");
                $stmt_reset->bind_param("i", $product_id);
                $stmt_reset->execute();
                $stmt_reset->close();

                $stmt_set = $mysqli->prepare("UPDATE product_images SET is_main=1 WHERE image_id=?");
                $stmt_set->bind_param("i", $image_id);
                if ($stmt_set->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Main image updated successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update main image.']);
                }
                $stmt_set->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Image not found.']);
                $stmt_img->close();
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
