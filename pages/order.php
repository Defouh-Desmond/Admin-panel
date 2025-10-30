<?php
require_once '../include/header.php';


// Fetch all orders
$query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.total_amount, 
        o.payment_status, 
        o.delivery_status, 
        o.created_at, 
        o.support_admin_id,
        u.full_name AS customer_name,
        u.phone AS customer_phone,
        u.address AS customer_address,
        sa_admin.admin_id AS assigned_admin_id,
        sa_user.full_name AS assigned_admin_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN admins sa_admin ON o.support_admin_id = sa_admin.admin_id
    LEFT JOIN users sa_user ON sa_admin.user_id = sa_user.user_id
    ORDER BY o.created_at DESC
";
$orders = $mysqli->query($query);

// Fetch all support admins
$support_admins = $mysqli->query("
    SELECT a.admin_id, u.full_name
    FROM admins a
    JOIN users u ON a.user_id = u.user_id
    WHERE a.role = 'support' AND a.active = 1
");
?>
<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Orders</h1>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No.</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_address']); ?></td>
                            <td><?php echo number_format($row['total_amount'], 2); ?> XAF</td>
                            <td>
                                <span class="label label-<?php 
                                    echo $row['payment_status'] == 'paid' ? 'success' : 
                                        ($row['payment_status'] == 'failed' ? 'danger' : 'default'); ?>">
                                    <?php echo ucfirst($row['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="label label-<?php 
                                    echo $row['delivery_status'] == 'delivered' ? 'success' : 
                                        ($row['delivery_status'] == 'out_for_delivery' ? 'info' : 'default'); ?>">
                                    <?php echo ucfirst($row['delivery_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $row['assigned_admin_name'] 
                                    ? htmlspecialchars($row['assigned_admin_name']) 
                                    : '<em>Unassigned</em>'; ?>
                            </td>
                            <td>
                                <?php if ($row['delivery_status'] == 'pending'): ?>
                                    <button class="btn btn-xs btn-primary assign-btn" 
                                            data-order-id="<?php echo $row['order_id']; ?>">
                                        Assign
                                    </button>
                                <?php else: ?>
                                    <em>No action</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center">No orders found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Assign Modal -->
        <div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="assignForm" method="POST" action="../include/order.php">
                        <input type="hidden" name="action" value="assign_order">
                        <div class="modal-header">
                            <h4 class="modal-title">Assign Order</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="order_id" id="modalOrderId">
                            <div class="form-group">
                                <label>Select Support Admin</label>
                                <select name="support_admin_id" class="form-control" required>
                                    <option value="">-- Select --</option>
                                    <?php while ($admin = $support_admins->fetch_assoc()): ?>
                                        <option value="<?php echo $admin['admin_id']; ?>">
                                            <?php echo htmlspecialchars($admin['full_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Assign</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

</div> <!-- /#wrapper -->

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/metisMenu.min.js"></script>
<script src="../js/startmin.js"></script>
<script>
$('.assign-btn').on('click', function() {
    $('#modalOrderId').val($(this).data('order-id'));
    $('#assignModal').modal('show');
});
</script>
</body>
</html>