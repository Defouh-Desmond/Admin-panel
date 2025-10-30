<?php
require_once '../include/header.php'; // starts session, sets $admin_name, $admin_role, includes head, navbar, sidebar


// Fetch editable admin details from DB
$admin_id = $_SESSION['admin_id'];

$stmt = $mysqli->prepare("
    SELECT u.full_name, u.email, u.phone, u.profile_picture, a.active
    FROM users u
    INNER JOIN admins a ON u.user_id = a.user_id
    WHERE a.admin_id = ?
");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) die("Admin not found.");
$admin = $result->fetch_assoc();
$stmt->close();
$mysqli->close();
?>

<style>
.input-icon { position: relative; }
.input-icon i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color:#888; }
.input-icon input { padding-left:30px; }
.label { font-size: 14px; margin-left:5px; }
.profile-img { width: 100%; max-width: 150px; border-radius: 50%; margin-bottom: 15px; }
</style>

    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Edit Profile</h1>
                </div>
            </div>

            <div class="row">

                <!-- Left Column: Current Profile -->
                <div class="col-lg-4">
                    <div class="panel panel-default text-center">
                        <div class="panel-body">
                            <img src="../uploads/profile/<?php echo htmlspecialchars($admin['profile_picture']); ?>" 
                                alt="Profile Picture" class="profile-img">
                            <h3><?php echo htmlspecialchars($admin['full_name']); ?></h3>
                            <p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($admin['email']); ?></p>
                            <p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($admin['phone']); ?></p>
                            <p>
                                <span class="label <?php echo $admin['active'] ? 'label-success' : 'label-danger'; ?>">
                                    <?php echo $admin['active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                <span class="label label-info"><?php echo htmlspecialchars($admin_role); ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Editable Form -->
                <div class="col-lg-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Update Profile
                        </div>
                        <div class="panel-body">
                            <form id="profileForm" enctype="multipart/form-data">
                                <div class="form-group input-icon">
                                    <i class="fa fa-user"></i>
                                    <input type="text" name="full_name" class="form-control"
                                        placeholder="Full Name"
                                        value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                </div>
                                <div class="form-group input-icon">
                                    <i class="fa fa-envelope"></i>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="Email"
                                        value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                </div>
                                <div class="form-group input-icon">
                                    <i class="fa fa-phone"></i>
                                    <input type="text" name="phone" class="form-control"
                                        placeholder="Phone"
                                        value="<?php echo htmlspecialchars($admin['phone']); ?>">
                                </div>
                                <div class="form-group input-icon">
                                    <i class="fa fa-lock"></i>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="New Password (leave blank to keep current)">
                                </div>
                                <div class="form-group">
                                    <label>Profile Picture</label>
                                    <input type="file" name="profile_picture" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                                <p id="profileMsg" class="text-center" style="margin-top:10px;"></p>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="../js/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../js/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../js/startmin.js"></script>

    <script>
        $(document).ready(function(){
            $('#profileForm').submit(function(e){
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'update_profile');

                $.ajax({
                    url: '../include/admin.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res){
                        $('#profileMsg').text(res.message).css('color', res.status==='success'?'green':'red');
                        if(res.status==='success'){
                            setTimeout(()=>location.reload(),1000);
                        }
                    }
                });
            });
        });
    </script>

</body>

</html>