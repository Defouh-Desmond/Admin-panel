<?php 
require_once '../include/header.php';
require_once '../classes/connection.php';

// Fetch all products with their category name
$query = "
    SELECT 
        p.product_id, 
        p.name AS product_name, 
        p.price, 
        p.stock_quantity, 
        p.status, 
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC
";
$result = $mysqli->query($query);
?>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Product List</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">All Products</div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-products">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                            <tr id="product-row-<?php echo $row['product_id']; ?>">
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                                <td><?php echo (int)$row['stock_quantity']; ?></td>
                                                <td class="text-center">
                                                    <?php if ($row['status'] === 'active'): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php elseif ($row['status'] === 'inactive'): ?>
                                                        <span class="label label-default">Inactive</span>
                                                    <?php else: ?>
                                                        <span class="label label-warning">Out of Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                                                            Action <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu pull-right" role="menu">
                                                            <li>
                                                                <a href="edit_product.php?id=<?php echo $row['product_id']; ?>">
                                                                    <i class="fa fa-pencil"></i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" 
                                                                    class="delete-product" 
                                                                    data-id="<?php echo $row['product_id']; ?>" 
                                                                    data-name="<?php echo htmlspecialchars($row['product_name']); ?>">
                                                                    <i class="fa fa-trash"></i> Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center">No products found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            <h4 class="modal-title">Confirm Action</h4>
        </div>
        <div class="modal-body">
            <p id="confirmMessage">Are you sure?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmActionBtn">Yes, Proceed</button>
        </div>
        </div>
    </div>
</div>

<!-- TOAST (notification container) -->
<div id="toast-container" style="
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
"></div>

<!-- jQuery -->
<script src="../js/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="../js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="../js/metisMenu.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="../js/startmin.js"></script>

<script>
$(document).ready(function() {
    let selectedId = null;
    let selectedName = '';
    let selectedRow = null;

    // Function to show toast message
    function showToast(message, type = 'success') {
        const bgColor = type === 'success' ? '#5cb85c' : '#d9534f';
        const toast = $(`
            <div class="toast" style="
                background: ${bgColor};
                color: #fff;
                padding: 12px 20px;
                border-radius: 4px;
                margin-bottom: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                display: none;
                min-width: 200px;
            ">
                ${message}
            </div>
        `);
        $('#toast-container').append(toast);
        toast.fadeIn(400).delay(2500).fadeOut(600, function() {
            $(this).remove();
        });
    }

    // Handle delete button click
    $('.delete-product').on('click', function(e) {
        e.preventDefault();
        selectedId = $(this).data('id');
        selectedName = $(this).data('name');
        selectedRow = $('#product-row-' + selectedId);
        $('#confirmMessage').text(`Are you sure you want to delete product "${selectedName}"?`);
        $('#confirmActionModal').modal('show');
    });

    // Confirm delete
    $('#confirmActionBtn').on('click', function() {
        if (!selectedId) return;

        $.ajax({
            url: '../include/product.php',
            type: 'POST',
            data: { action: 'delete', product_id: selectedId },
            dataType: 'json',
            beforeSend: function() {
                $('#confirmActionBtn').prop('disabled', true).text('Deleting...');
            },
            success: function(response) {
                $('#confirmActionBtn').prop('disabled', false).text('Yes, Proceed');
                $('#confirmActionModal').modal('hide');

                if (response.status === 'success') {
                    // Smoothly remove product row
                    selectedRow.css('background-color', '#f2dede').fadeOut(600, function() {
                        $(this).remove();
                    });
                    showToast('✅ Product deleted successfully!', 'success');
                } else {
                    showToast(response.message || '⚠️ Failed to delete product.', 'error');
                }
            },
            error: function() {
                $('#confirmActionBtn').prop('disabled', false).text('Yes, Proceed');
                $('#confirmActionModal').modal('hide');
                showToast('❌ Error deleting product.', 'error');
            }
        });
    });
});
</script>

</body>
</html>
