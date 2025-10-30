<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Licrestor-admin</title>

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
                        <h3 class="panel-title">Admin Login</h3>
                    </div>
                    <div class="panel-body">
                        <!-- Error / success messages -->
                        <p class="text-danger text-center" id="error" style="display:none;"></p>
                        <p class="text-success text-center" id="success" style="display:none;"></p>

                        <form id="loginForm">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="E-mail" name="email" type="email" required>
                                </div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="password" type="password" required>
                                </div>

                                <button type="submit" class="btn btn-lg btn-success btn-block">Login</button>

                                <div class="text-center" style="margin-top:10px;">
                                    <a href="signup.php">Don't have an account? Sign up</a>
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
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();

                // Hide previous messages
                $('#error').hide();
                $('#success').hide();

                $.ajax({
                    type: 'POST',
                    url: '../include/auth.php', // our auth file
                    data: $(this).serialize() + '&action=login',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#success').text(response.message).show();
                            $('#error').hide();
                            // Redirect to dashboard after 1 second
                            setTimeout(function () {
                                window.location.href = 'dashboard.php';
                            }, 1000);
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
