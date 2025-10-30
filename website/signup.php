<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Licrestor - Sign Up</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700%7CCabin:400%7CDancing+Script"
        rel="stylesheet">

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="css/font-awesome.min.css">

    <!-- Intl-Tel-Input CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="css/style.css" />

    <style>
        .iti {
            width: 100%;
        }
        .iti__flag-container {
            z-index: 5;
        }
    </style>
</head>

<body>

    <div id="signup-section" class="section">
        <div class="container">
            <div class="row signup-container">

                <!-- Left Side: Welcome Message -->
                <div class="col-md-6 col-sm-12 signup-welcome text-center">
                    <div class="welcome-content">
                        <h2>Welcome to LIC Restaurant</h2>
                        <p>Join our community to get exclusive offers, event updates, and special rewards!</p>
                    </div>
                </div>

                <!-- Right Side: Signup Form -->
                <div class="col-md-6 col-sm-12 signup-form-wrapper">
                    <div class="signup-form">
                        <h3>Create an Account</h3>
                        <p class="text-danger text-center" id="error" style="display:none;"></p>
                        <p class="text-success text-center" id="success" style="display:none;"></p>
                        <form id="signup-form">
                            <div class="form-group">
                                <input type="text" class="input" name="full_name" placeholder="Full Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" class="input" name="email" placeholder="Email Address" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" class="input" id="phone" name="phone" placeholder="Phone Number">
                            </div>
                            <div class="form-group password-group">
                                <input type="password" class="input" name="password" placeholder="Password" required>
                                <span class="toggle-password"><i class="fa fa-eye"></i></span>
                            </div>
                            <div class="form-group password-group">
                                <input type="password" class="input" name="confirm_password"
                                    placeholder="Confirm Password" required>
                                <span class="toggle-password"><i class="fa fa-eye"></i></span>
                            </div>
                            <button type="submit" class="main-button btn btn-block">Sign Up</button>
                        </form>

                        <div class="social-signup text-center">
                            <p>or log in with</p>
                            <div class="social-buttons">
                                <a href="#" class="btn btn-google"><i class="fa fa-google"></i> Google</a>
                                <a href="#" class="btn btn-facebook"><i class="fa fa-facebook"></i> Facebook</a>
                            </div>
                        </div>
                        <p class="login-link text-center">Already have an account? <a href="login.php">Log in here</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- jQuery Plugins -->
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>

    <!-- Intl-Tel-Input JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

    <script>
        // Initialize phone input with intl-tel-input
        const phoneInput = document.querySelector("#phone");
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "cm", // Default Cameroon 🇨🇲
            preferredCountries: ["cm", "ng", "gh", "us"],
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(eye => {
            eye.addEventListener('click', function () {
                const passwordField = this.closest('.password-group').querySelector('input');
                const icon = this.querySelector('i');
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
        });

        // AJAX signup form submission
        $(document).ready(function () {
            $('#signup-form').on('submit', function (e) {
                e.preventDefault();
                $('#error').hide();
                $('#success').hide();

                const password = $('input[name="password"]').val();
                const confirm_password = $('input[name="confirm_password"]').val();

                if (password !== confirm_password) {
                    $('#error').text('Passwords do not match.').show();
                    return;
                }

                // Get the full phone number in international format
                const fullNumber = iti.getNumber();
                $('input[name="phone"]').val(fullNumber);

                // Validate the number
                if (!iti.isValidNumber()) {
                    $('#error').text('Please enter a valid phone number.').show();
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: 'include/auth.php',
                    data: $(this).serialize() + '&action=signup',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#success').text(response.message).show();
                            setTimeout(function () {
                                window.location.href = 'login.php';
                            }, 1500);
                        } else {
                            $('#error').text(response.message).show();
                        }
                    },
                    error: function () {
                        $('#error').text('Something went wrong. Please try again.').show();
                    }
                });
            });
        });
    </script>

</body>

</html>
