<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Licrestor</title>

        <!-- Google font -->
        <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700%7CCabin:400%7CDancing+Script" rel="stylesheet">

        <!-- Bootstrap -->
        <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>

        <!-- Font Awesome Icon -->
        <link rel="stylesheet" href="css/font-awesome.min.css">

        <!-- Custom stylesheet -->
        <link type="text/css" rel="stylesheet" href="css/style.css"/>
    </head>
    <body>
        
        <!-- Login Section -->
        <div id="login-section" class="section">
            <div class="container">
                <div class="row signup-container">
        
                    <!-- Left Side: Welcome Message -->
                    <div class="col-md-6 col-sm-12 signup-welcome text-center">
                        <div class="welcome-content">
                            <h2>Welcome Back to LIC Restaurant</h2>
                            <p>Log in to manage your reservations, view your rewards, and stay updated with our latest events.</p>
                        </div>
                    </div>
        
                    <!-- Right Side: Login Form -->
                    <div class="col-md-6 col-sm-12 signup-form-wrapper">
                        <div class="signup-form">
                            <h3>Log In to Your Account</h3>

                            <!-- Messages -->
                            <p class="text-danger text-center" id="error" style="display:none;"></p>
                            <p class="text-success text-center" id="success" style="display:none;"></p>

                            <form id="loginForm" method="post">
                                <div class="form-group">
                                    <input type="email" class="input" name="email" placeholder="Email Address" required>
                                </div>

                                <div class="form-group password-group">
                                    <input type="password" class="input" name="password" placeholder="Password" id="password" required>
                                    <span class="toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>

                                <div class="form-group text-right">
                                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                                </div>

                                <button type="submit" class="main-button btn btn-block">Log In</button>
                            </form>

                            <div class="social-signup text-center">
                                <p>or log in with</p>
                                <div class="social-buttons">
                                    <a href="#" class="btn btn-google"><i class="fa fa-google"></i> Google</a>
                                    <a href="#" class="btn btn-facebook"><i class="fa fa-facebook"></i> Facebook</a>
                                </div>
                            </div>

                            <p class="login-link text-center">Don’t have an account? <a href="signup.html">Sign up here</a></p>
                        </div>
                    </div>
        
                </div>
            </div>
        </div>
        <!-- /Login Section -->

        <!-- jQuery Plugins -->
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>

        <script>
            // Toggle password visibility
            document.querySelector('.toggle-password').addEventListener('click', function() {
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

            // Handle login form submission
            $(document).ready(function(){
                $('#loginForm').on('submit', function(e){
                    e.preventDefault();

                    $('#error').hide();
                    $('#success').hide();

                    $.ajax({
                        type: 'POST',
                        url: 'auth.php',
                        data: $(this).serialize() + '&action=login',
                        dataType: 'json',
                        success: function(response){
                            if(response.status === 'success'){
                                $('#success').text(response.message).show();
                                $('#error').hide();
                                setTimeout(function(){
                                    window.location.href = 'index.php'; // Redirect after login
                                }, 1000);
                            } else {
                                $('#error').text(response.message).show();
                                $('#success').hide();
                            }
                        },
                        error: function(){
                            $('#error').text('Something went wrong. Please try again.').show();
                            $('#success').hide();
                        }
                    });
                });
            });
        </script>
            
    </body>
</html>