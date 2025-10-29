<?php
require_once '../include/header.php'; // starts session, includes navbar/sidebar
require_once '../classes/connection.php'; // provides $mysqli

// Fetch all active categories
$cat_query = "SELECT category_id, name FROM categories WHERE status='active' ORDER BY name ASC";
$cat_result = $mysqli->query($cat_query);
?>

<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Add Product</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="panel panel-default">
                    <div class="panel-heading">Product Details</div>
                    <div class="panel-body">
                        <form id="addProductForm" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="action" value="add">

                            <!-- Product Name -->
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" name="name" id="productName" class="form-control" required>
                            </div>

                            <!-- Slug -->
                            <div class="form-group">
                                <label>Slug (Auto-generated or editable)</label>
                                <input type="text" name="slug" id="productSlug" class="form-control" required>
                                <p class="help-block">The slug is used in product URLs. It should be unique.</p>
                            </div>

                            <!-- Category -->
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php if ($cat_result && $cat_result->num_rows > 0): ?>
                                        <?php while ($cat = $cat_result->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['category_id']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option disabled>No active categories found</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Enter product description..."></textarea>
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                <label>Price (FCFA)</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control" required>
                            </div>

                            <!-- Stock Quantity -->
                            <div class="form-group">
                                <label>Stock Quantity</label>
                                <input type="number" name="stock_quantity" class="form-control" min="0" required>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                </select>
                            </div>

                            <!-- Product Images -->
                            <div class="form-group">
                                <label>Product Images</label>
                                <input type="file" name="images[]" multiple class="form-control">
                                <p class="help-block">You can upload multiple images. The first will be used as the main image.</p>
                            </div>

                            <button type="submit" class="btn btn-success">Add Product</button>
                            <button type="reset" class="btn btn-default">Reset</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- jQuery (ensure loaded before Bootstrap JS) -->
<script src="../js/jquery.min.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="../js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="../js/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="../js/startmin.js"></script>

<script>
$(document).ready(function(){

    // Auto-generate slug from product name
    $('#productName').on('input', function(){
        var slug = $(this).val().toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
        $('#productSlug').val(slug);
    });

    // Submit add product form
    $('#addProductForm').on('submit', function(e){
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: '../include/product.php', // PHP handler
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res){
                alert(res.message);
                if(res.status === 'success') {
                    $('#addProductForm')[0].reset();
                    setTimeout(function(){
                        window.location.href = 'products.php';
                    }, 1000);
                }
            },
            error: function(xhr){
                alert('An error occurred while processing your request.');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

</body>
</html>
