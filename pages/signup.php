<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Startmin - Bootstrap Admin Theme</title>

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
                        <h3 class="panel-title">Please Sign up</h3>
                    </div>
                    <div class="panel-body">
                        <!-- Error / success message -->
                        <p class="text-danger text-center" id="error" style="display:none;"></p>
                        <p class="text-success text-center" id="success" style="display:none;"></p>

                        <form id="signupForm">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Full Name" name="full_name" type="text" required>
                                </div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="E-mail" name="email" type="email" required>
                                </div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="Phone Number" name="phone" type="text" required>
                                </div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="password" type="password" required>
                                </div>

                                <button type="submit" class="btn btn-lg btn-success btn-block">Sign Up</button>

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
            $('#signupForm').on('submit', function (e) {
                e.preventDefault();

                // Hide previous messages
                $('#error').hide();
                $('#success').hide();

                $.ajax({
                    type: 'POST',
                    url: '../include/auth.php', // the auth file
                    data: $(this).serialize() + '&action=signup',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#success').text(response.message).show();
                            $('#error').hide();
                            // Redirect to login after 2 seconds
                            setTimeout(function () {
                                window.location.href = 'login.php';
                            }, 2000);
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
