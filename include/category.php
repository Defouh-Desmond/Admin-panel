<?php
require_once '../classes/connection.php'; // your $mysqli connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {

        // =========================
        // LIST CATEGORIES
        // =========================
        case 'list':
            $result = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");
            $categories = [];
            if($result){
                while($row = $result->fetch_assoc()){
                    $categories[] = $row;
                }
                echo json_encode(['status'=>'success','data'=>$categories]);
            } else {
                echo json_encode(['status'=>'error','message'=>'Failed to fetch categories.']);
            }
            break;

        // =========================
        // ADD CATEGORY
        // =========================
        case 'add':
            $name = $mysqli->real_escape_string($_POST['name']);
            $slug = !empty($_POST['slug']) ? $mysqli->real_escape_string($_POST['slug']) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $status = $mysqli->real_escape_string($_POST['status']);

            $sql = "INSERT INTO categories (name, slug, status) VALUES ('$name','$slug','$status')";
            if($mysqli->query($sql)){
                echo json_encode(['status'=>'success','message'=>'Category added successfully!']);
            } else {
                echo json_encode(['status'=>'error','message'=>'Failed to add category: '.$mysqli->error]);
            }
            break;

        // =========================
        // UPDATE CATEGORY
        // =========================
        case 'update':
            $id = (int)$_POST['category_id'];
            $name = $mysqli->real_escape_string($_POST['name']);
            $slug = $mysqli->real_escape_string($_POST['slug']);
            $status = $mysqli->real_escape_string($_POST['status']);

            $sql = "UPDATE categories SET name='$name', slug='$slug', status='$status' WHERE category_id='$id'";
            if($mysqli->query($sql)){
                echo json_encode(['status'=>'success','message'=>'Category updated successfully!']);
            } else {
                echo json_encode(['status'=>'error','message'=>'Failed to update category: '.$mysqli->error]);
            }
            break;

        // =========================
        // DELETE CATEGORY
        // =========================
        case 'delete':
            $id = (int)$_POST['category_id'];

            // Prevent deletion if products exist
            $check = $mysqli->query("SELECT COUNT(*) AS cnt FROM products WHERE category_id='$id'");
            $row = $check->fetch_assoc();
            if($row['cnt'] > 0){
                echo json_encode(['status'=>'error','message'=>'Cannot delete category: products exist.']);
                exit;
            }

            $sql = "DELETE FROM categories WHERE category_id='$id'";
            if($mysqli->query($sql)){
                echo json_encode(['status'=>'success','message'=>'Category deleted successfully!']);
            } else {
                echo json_encode(['status'=>'error','message'=>'Failed to delete category: '.$mysqli->error]);
            }
            break;

        default:
            echo json_encode(['status'=>'error','message'=>'Invalid action.']);
    }
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid request.']);
}
