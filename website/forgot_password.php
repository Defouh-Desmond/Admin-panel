<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Licrestor</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700%7CCabin:400%7CDancing+Script"
        rel="stylesheet">

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />

    <!-- Owl Carousel -->
    <link type="text/css" rel="stylesheet" href="css/owl.carousel.css" />
    <link type="text/css" rel="stylesheet" href="css/owl.theme.default.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="css/font-awesome.min.css">

    <!-- Custom stlylesheet -->
    <link type="text/css" rel="stylesheet" href="css/style.css" />
</head>

<body>

    <!-- Forgot Password Section -->
    <div id="signup-section" class="section">
        <div class="container">
            <div class="row signup-container">

                <!-- Left Side: Welcome Message -->
                <div class="col-md-6 col-sm-12 signup-welcome text-center">
                    <div class="welcome-content">
                        <h2>Forgot Your Password?</h2>
                        <p>No worries! Enter your email address below and we’ll send you a link to reset your password.
                        </p>
                    </div>
                </div>

                <!-- Right Side: Forgot Password Form -->
                <div class="col-md-6 col-sm-12 signup-form-wrapper">
                    <div class="signup-form">
                        <h3>Reset Your Password</h3>
                        <form id="forgotPasswordForm" action="#" method="post">
                            <div class="form-group">
                                <input type="email" class="input" name="email" placeholder="Enter your registered email"
                                    required>
                            </div>
                            <button type="submit" class="main-button btn btn-block">Send Reset Link</button>
                        </form>

                        <div class="social-signup text-center">
                            <p class="login-link text-center">Remember your password? <a href="login.php">Back to
                                    Login</a></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- /Forgot Password Section -->

    <!-- jQuery Plugins -->
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/owl.carousel.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script type="text/javascript" src="js/google-map.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script>
        document.querySelector('.toggle-password').addEventListener('click', function () {
            var passwordField = document.getElementById('password');
            var icon = this.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>


</body>

</html>