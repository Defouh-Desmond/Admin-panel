<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Become an Admin Page">
    <meta name="author" content="">

    <title>Licrestor - Become an Admin</title>

    <!-- Bootstrap Core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../css/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../css/startmin.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Become an Admin</h3>
                    </div>
                    <div class="panel-body">
                        <p class="text-danger text-center" id="error" style="display:none;"></p>
                        <p class="text-success text-center" id="success" style="display:none;"></p>

                        <form id="becomeAdminForm">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="E-mail" name="email" type="email" required>
                                </div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="password" type="password" required>
                                </div>

                                <div class="checkbox">
                                    <label>
                                        <input name="confirm" type="checkbox" required> I understand that becoming an admin gives me management privileges.
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-lg btn-primary btn-block">Become an Admin</button>

                                <div class="text-center" style="margin-top:10px;">
                                    <a href="login.php">Back to Admin Login</a>
                                </div>
                            </fieldset>
                        </form>
                    </div>
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
        $(document).ready(function () {
            $('#becomeAdminForm').on('submit', function (e) {
                e.preventDefault();
                $('#error').hide();
                $('#success').hide();

                $.ajax({
                    type: 'POST',
                    url: '../include/auth.php',
                    data: $(this).serialize() + '&action=become_admin',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#success').text(response.message).show();
                            $('#error').hide();
                            setTimeout(function () {
                                window.location.href = 'login.php';
                            }, 1500);
                        } else {
                            $('#error').text(response.message).show();
                            $('#success').hide();
                        }
                    },
                    error: function () {
                        $('#error').text('Something went wrong. Please try again.').show();
                        $('#success').hide();
                    }
                });
            });
        });
    </script>
</body>

</html>
