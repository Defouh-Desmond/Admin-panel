<?php
require_once '../include/header.php';


// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Product ID not specified.');
}

$product_id = (int) $_GET['id'];

// Fetch product details
$product_stmt = $mysqli->prepare("SELECT * FROM products WHERE product_id = ? LIMIT 1");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows === 0) {
    die('Product not found.');
}

$product = $product_result->fetch_assoc();

// Fetch product images
$images_stmt = $mysqli->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC");
$images_stmt->bind_param("i", $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();

// Fetch active categories
$cat_query = "SELECT category_id, name FROM categories WHERE status = 'active' ORDER BY name ASC";
$cat_result = $mysqli->query($cat_query);
?>

<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Edit Product</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="panel panel-default">
                    <div class="panel-heading">Product Details</div>
                    <div class="panel-body">
                        <form id="editProductForm" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

                            <!-- Product Name -->
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" name="name" id="productName" class="form-control"
                                    value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>

                            <!-- Slug -->
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" id="productSlug" class="form-control"
                                    value="<?php echo htmlspecialchars($product['slug']); ?>" required>
                                <p class="help-block">The slug is used in product URLs. It should be unique.</p>
                            </div>

                            <!-- Category -->
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php if ($cat_result && $cat_result->num_rows > 0): ?>
                                        <?php while ($cat = $cat_result->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['category_id']; ?>"
                                                <?php if ($cat['category_id'] == $product['category_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option disabled>No categories found</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                <label>Price (FCFA)</label>
                                <input type="number" name="price" step="0.01" class="form-control"
                                    value="<?php echo htmlspecialchars($product['price']); ?>" required>
                            </div>

                            <!-- Stock -->
                            <div class="form-group">
                                <label>Stock Quantity</label>
                                <input type="number" name="stock_quantity" class="form-control"
                                    value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="active" <?php if ($product['status'] == 'active') echo 'selected'; ?>>Active</option>
                                    <option value="inactive" <?php if ($product['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                                    <option value="out_of_stock" <?php if ($product['status'] == 'out_of_stock') echo 'selected'; ?>>Out of Stock</option>
                                </select>
                            </div>

                            <!-- Existing Images -->
                            <div class="form-group">
                                <label>Existing Images</label>
                                <div id="existingImages" style="display:flex; gap: 10px; flex-wrap:wrap;">
                                    <?php if ($images_result && $images_result->num_rows > 0): ?>
                                        <?php while ($img = $images_result->fetch_assoc()): ?>
                                            <div style="display:flex; flex-direction:column; text-align:center;"
                                                 id="img-<?php echo $img['image_id']; ?>">
                                                <img src="../uploads/products/<?php echo htmlspecialchars($img['image_path']); ?>"
                                                     width="80" height="80" style="border:1px solid #ccc; padding:2px;">
                                                <button type="button" class="btn btn-xs btn-danger delete-image"
                                                        data-id="<?php echo $img['image_id']; ?>" style="margin-top:3px;">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                                <?php if ($img['is_main']): ?>
                                                    <div><span class="label label-success">Main</span></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <p>No images uploaded.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Upload New Images -->
                            <div class="form-group">
                                <label>Upload New Images (Optional)</label>
                                <input type="file" name="images[]" multiple class="form-control">
                                <p class="help-block">The first uploaded image becomes main if none exists.</p>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- DELETE IMAGE MODAL -->
<div class="modal fade" id="deleteImageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            <h4 class="modal-title">Delete Image</h4>
        </div>
        <div class="modal-body">
            <p id="deleteImageMessage">Are you sure you want to delete this image?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteImageBtn">Delete</button>
        </div>
        </div>
    </div>
</div>

    <!-- jQuery -->
    <script src="../js/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../js/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../js/startmin.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let deleteImageId = null;
    const deleteModal = document.getElementById('deleteImageModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteImageBtn');

    // 🔹 Auto-generate slug
    const nameInput = document.getElementById('productName');
    const slugInput = document.getElementById('productSlug');
    nameInput.addEventListener('input', () => {
        const slug = nameInput.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
        slugInput.value = slug;
    });

    // 🔹 Delete image handler
    document.querySelectorAll('.delete-image').forEach(btn => {
        btn.addEventListener('click', function () {
            deleteImageId = this.getAttribute('data-id');
            const modal = new bootstrap.Modal(deleteModal);
            modal.show();

            confirmDeleteBtn.onclick = async function () {
                if (!deleteImageId) return;
                const formData = new FormData();
                formData.append('action', 'delete_image');
                formData.append('image_id', deleteImageId);

                try {
                    const res = await fetch('../include/product.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    alert(data.message);
                    if (data.status === 'success') {
                        const imgEl = document.getElementById('img-' + deleteImageId);
                        if (imgEl) imgEl.remove();
                    }
                } catch (err) {
                    alert('Error deleting image.');
                    console.error(err);
                }
                const modalInstance = bootstrap.Modal.getInstance(deleteModal);
                modalInstance.hide();
            };
        });
    });

    // 🔹 Submit form for update
    const form = document.getElementById('editProductForm');
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        try {
            const response = await fetch('../include/product.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();
            alert(res.message);
            if (res.status === 'success') {
                window.location.href = 'products.php';
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred while updating the product.');
        }
    });
});
</script>

</body>
</html>
