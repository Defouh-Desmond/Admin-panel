<?php
require_once '../include/header.php';
require_once '../classes/connection.php';

// ==================== CONFIG ====================
$limit = 50; // records per page
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ==================== COUNT TOTAL ====================
$whereClause = "WHERE u.user_id NOT IN (SELECT user_id FROM admins)";
if (!empty($search)) {
    $safeSearch = $mysqli->real_escape_string($search);
    $whereClause .= " AND (u.full_name LIKE '%$safeSearch%' 
                        OR u.email LIKE '%$safeSearch%' 
                        OR u.phone LIKE '%$safeSearch%')";
}

$countQuery = "SELECT COUNT(*) AS total FROM users u $whereClause";
$countResult = $mysqli->query($countQuery);
$totalUsers = ($countResult && $countResult->num_rows > 0) ? $countResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalUsers / $limit);

// ==================== FETCH USERS ====================
$query = "
    SELECT 
        u.user_id, 
        u.full_name, 
        u.email, 
        u.phone, 
        u.created_at, 
        u.block, 
        u.suspend, 
        s.suspension_due,
        s.reason
    FROM users u
    LEFT JOIN suspended_users s ON u.user_id = s.user_id
    $whereClause
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
";
$result = $mysqli->query($query);
?>

<div id="page-wrapper">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">User List</h1>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row">
            <div class="col-md-6">
                <form method="GET" class="form-inline" id="searchForm">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone"
                               value="<?php echo htmlspecialchars($search); ?>">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <br>

        <!-- Users Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Showing <?php echo min($limit, $totalUsers - $offset); ?> of <?php echo number_format($totalUsers); ?> users
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="userTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Suspension</th>
                                        <th>Date Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $i = $offset + 1; while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                                <td class="text-center">
                                                    <?php if ($row['block'] == 0): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Blocked</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['suspend'] == 0): ?>
                                                        <span class="label label-default">Not Suspended</span>
                                                    <?php else: ?>
                                                        <span class="label label-warning"
                                                              title="Due: <?php echo $row['suspension_due']; ?>&#10;Reason: <?php echo htmlspecialchars($row['reason']); ?>">
                                                            Suspended
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($row['created_at']))); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                                                            Action <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu pull-right" role="menu">
                                                            <li>
                                                                <a href="#" 
                                                                   class="block-user" 
                                                                   data-id="<?php echo $row['user_id']; ?>" 
                                                                   data-name="<?php echo htmlspecialchars($row['full_name']); ?>"
                                                                   data-block="<?php echo $row['block']; ?>">
                                                                    <i class="fa fa-ban"></i>
                                                                    <?php echo ($row['block'] == 0) ? 'Block' : 'Unblock'; ?>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" 
                                                                   class="suspend-user" 
                                                                   data-id="<?php echo $row['user_id']; ?>" 
                                                                   data-name="<?php echo htmlspecialchars($row['full_name']); ?>"
                                                                   data-suspend="<?php echo $row['suspend']; ?>"
                                                                   data-due="<?php echo $row['suspension_due']; ?>"
                                                                   data-reason="<?php echo htmlspecialchars($row['reason']); ?>">
                                                                    <i class="fa fa-clock-o"></i>
                                                                    <?php echo ($row['suspend'] == 0) ? 'Suspend' : 'Unsuspend'; ?>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="8" class="text-center">No users found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="User pagination">
                                <ul class="pagination">
                                    <!-- Previous -->
                                    <li class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Prev</a>
                                    </li>

                                    <!-- Page numbers -->
                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    for ($i = $start; $i <= $end; $i++): ?>
                                        <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next -->
                                    <li class="<?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next &raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- === MODALS === -->
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

<div class="modal fade" id="suspendModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">Suspend User</h4>
      </div>
      <div class="modal-body">
        <form id="suspendForm">
          <input type="hidden" name="user_id" id="suspendUserId">
          <div class="form-group">
            <label for="suspension_due">Suspension Due Date</label>
            <input type="datetime-local" class="form-control" name="suspension_due" id="suspension_due" required>
          </div>
          <div class="form-group">
            <label for="suspend_reason">Reason (Optional)</label>
            <textarea class="form-control" name="reason" id="suspend_reason" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="confirmSuspendBtn">Suspend</button>
      </div>
    </div>
  </div>
</div>

<!-- === JS === -->
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/metisMenu.min.js"></script>
<script src="../js/startmin.js"></script>

<script>
$(document).ready(function() {
    var selectedId = null;
    var selectedAction = null;

    // Block/unblock
    $('.block-user').click(function(e){
        e.preventDefault();
        selectedId = $(this).data('id');
        selectedAction = 'block_user';
        var status = $(this).data('block') == 0 ? 'block' : 'unblock';
        var name = $(this).data('name');
        $('#confirmMessage').text('Are you sure you want to ' + status + ' user "' + name + '"?');
        $('#confirmActionModal').modal('show');
    });

    // Suspend/unsuspend
    $('.suspend-user').click(function(e){
        e.preventDefault();
        selectedId = $(this).data('id');
        var name = $(this).data('name');
        if($(this).data('suspend') == 0){
            $('#suspendUserId').val(selectedId);
            var due = $(this).data('due') ? $(this).data('due').replace(' ', 'T') : '';
            $('#suspension_due').val(due);
            $('#suspend_reason').val($(this).data('reason') || '');
            $('#suspendModal').modal('show');
        } else {
            selectedAction = 'suspend_user';
            $('#confirmMessage').text('Are you sure you want to unsuspend user "' + name + '"?');
            $('#confirmActionModal').modal('show');
        }
    });

    // Confirm block/unblock/unsuspend
    $('#confirmActionBtn').click(function(){
        $('#confirmActionModal').modal('hide');
        $.ajax({
            url: '../include/user.php',
            type: 'POST',
            dataType: 'json',
            data: { action: selectedAction, user_id: selectedId },
            success: function(res){
                alert(res.message);
                if(res.status === 'success') location.reload();
            }
        });
    });

    // Confirm suspend
    $('#confirmSuspendBtn').click(function(){
        var due = $('#suspension_due').val();
        if(!due) return alert('Please select a suspension due date');
        var reason = $('#suspend_reason').val();
        $.ajax({
            url: '../include/user.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'suspend_user',
                user_id: selectedId,
                suspension_due: due,
                reason: reason
            },
            success: function(res){
                alert(res.message);
                if(res.status === 'success') location.reload();
            }
        });
        $('#suspendModal').modal('hide');
    });
});
</script>
</body>
</html>
