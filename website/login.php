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

    <!-- Intl-Tel-Input CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="css/style.css"/>

    <style>
        .iti {
            width: 100%;
        }
        .iti__flag-container {
            z-index: 5;
        }
        .login-option {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .login-option button {
            margin: 0 5px;
            border: none;
            background: #f5f5f5;
            color: #333;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .login-option button.active {
            background: #ff6600;
            color: #fff;
        }
        .login-input {
            display: none;
        }
    </style>
</head>
<body>
    
    <!-- Login Section -->
    <div id="login-section" class="section">
        <div class="container">
            <div class="row signup-container">
    
                <!-- Left Side -->
                <div class="col-md-6 col-sm-12 signup-welcome text-center">
                    <div class="welcome-content">
                        <h2>Welcome Back to LIC Restaurant</h2>
                        <p>Log in to manage your reservations, view your rewards, and stay updated with our latest events.</p>
                    </div>
                </div>
    
                <!-- Right Side -->
                <div class="col-md-6 col-sm-12 signup-form-wrapper">
                    <div class="signup-form">
                        <h3>Log In to Your Account</h3>

                        <!-- Messages -->
                        <p class="text-danger text-center" id="error" style="display:none;"></p>
                        <p class="text-success text-center" id="success" style="display:none;"></p>

                        <div class="login-option">
                            <button type="button" id="emailOption" class="active">Email</button>
                            <button type="button" id="phoneOption">Phone</button>
                        </div>

                        <form id="loginForm" method="post">
                            <!-- Email Input -->
                            <div class="form-group login-input" id="emailInput" style="display:block;">
                                <input type="email" class="input" name="email" placeholder="Email Address">
                            </div>

                            <!-- Phone Input -->
                            <div class="form-group login-input" id="phoneInput">
                                <input type="tel" class="input" id="phoneField" name="phone" placeholder="Phone Number">
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

                        <p class="login-link text-center">Don’t have an account? <a href="signup.php">Sign up here</a></p>
                    </div>
                </div>
    
            </div>
        </div>
    </div>

    <!-- jQuery Plugins -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- Intl-Tel-Input -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

    <script>
        // Initialize phone input with flags
        const phoneField = document.querySelector("#phoneField");
        const iti = window.intlTelInput(phoneField, {
            initialCountry: "cm",
            preferredCountries: ["cm", "ng", "gh", "us"],
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
        });

        // Toggle login method
        $('#emailOption').on('click', function(){
            $(this).addClass('active');
            $('#phoneOption').removeClass('active');
            $('#phoneInput').fadeOut(200, function(){
                $('#emailInput').fadeIn(200);
            });
        });

        $('#phoneOption').on('click', function(){
            $(this).addClass('active');
            $('#emailOption').removeClass('active');
            $('#emailInput').fadeOut(200, function(){
                $('#phoneInput').fadeIn(200);
            });
        });

        // Password toggle
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

        // Form submission
        $('#loginForm').on('submit', function(e){
            e.preventDefault();

            $('#error').hide();
            $('#success').hide();

            let method = $('.login-option button.active').attr('id');
            let formData = {
                action: 'login',
                password: $('#password').val()
            };

            if(method === 'emailOption'){
                formData.email = $('input[name="email"]').val().trim();
            } else {
                if(iti.isValidNumber()){
                    formData.phone = iti.getNumber();
                } else {
                    $('#error').text('Please enter a valid phone number.').show();
                    return;
                }
            }

            $.ajax({
                type: 'POST',
                url: 'include/auth.php',
                data: formData,
                dataType: 'json',
                success: function(response){
                    if(response.status === 'success'){
                        $('#success').text(response.message).show();
                        setTimeout(()=> window.location.href='index.php', 1000);
                    } else {
                        $('#error').text(response.message).show();
                    }
                },
                error: function(){
                    $('#error').text('Something went wrong. Please try again.').show();
                }
            });
        });
    </script>
</body>
</html>
