<?php
require_once '../include/header.php'; 


// Fetch all in-shop sales
$query = "
    SELECT s.sale_id, s.customer_name, s.customer_phone, s.total_amount, s.created_at
    FROM sales s
    ORDER BY s.created_at DESC
";
$result = $mysqli->query($query);
?>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">In-Shop Sales History</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">All In-Shop Sales</div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="salesTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date & Time</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Total (FCFA)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $i=1; while($row = $result->fetch_assoc()): ?>
                                            <tr id="sale-<?php echo $row['sale_id']; ?>">
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo $row['created_at']; ?></td>
                                                <td><?php echo htmlspecialchars($row['customer_name'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($row['customer_phone'] ?: '-'); ?></td>
                                                <td><?php echo number_format($row['total_amount'], 2); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-info btn-xs view-sale" 
                                                            data-id="<?php echo $row['sale_id']; ?>">
                                                        View Details
                                                    </button>
                                                    <button class="btn btn-danger btn-xs delete-sale"
                                                            data-id="<?php echo $row['sale_id']; ?>">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">No sales recorded</td></tr>
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
<!-- /#page-wrapper -->

<!-- Sale Details Modal -->
<div class="modal fade" id="saleDetailsModal" tabindex="-1" role="dialog">
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">Sale Details</h4>
    </div>
    <div class="modal-body" id="saleDetailsBody">
        <p>Loading...</p>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
    </div>
</div>
</div>

</div>
<!-- /#wrapper -->

<!-- JS Dependencies -->
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/metisMenu.min.js"></script>
<script src="../js/startmin.js"></script>

<script>
// Wait for DOM
document.addEventListener('DOMContentLoaded', function() {
    const modalBody = document.getElementById('saleDetailsBody');

    // View sale details
    document.querySelectorAll('.view-sale').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.id;
            modalBody.innerHTML = '<p>Loading...</p>';
            $('#saleDetailsModal').modal('show');

            fetch('../include/sale.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'fetch_items', sale_id: saleId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    let html = '<table class="table table-bordered"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr></thead><tbody>';
                    data.items.forEach(item => {
                        html += `<tr>
                            <td>${item.product_name}</td>
                            <td>${parseFloat(item.price).toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    html += `<p><strong>Total: </strong>${parseFloat(data.total).toFixed(2)} FCFA</p>`;
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `<p>${data.message}</p>`;
                }
            })
            .catch(err => {
                console.error(err);
                modalBody.innerHTML = '<p>Error loading sale details.</p>';
            });
        });
    });

    // Delete sale
    document.querySelectorAll('.delete-sale').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.id;
            if (!confirm('Are you sure you want to delete this sale?')) return;

            fetch('../include/sale.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'delete', sale_id: saleId })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    const row = document.getElementById('sale-' + saleId);
                    if(row) row.remove();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error deleting sale.');
            });
        });
    });
});
</script>

</body>
</html>
