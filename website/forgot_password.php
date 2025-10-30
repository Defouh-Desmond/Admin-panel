<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Licrestor - Forgot Password</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700%7CCabin:400%7CDancing+Script" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="css/font-awesome.min.css">

    <!-- Intl-Tel-Input CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />

    <!-- Custom stylesheet -->
    <link rel="stylesheet" href="css/style.css"/>

    <style>
        .iti {
            width: 100%;
        }
        .iti__flag-container {
            z-index: 5;
        }
        .reset-option {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .reset-option button {
            margin: 0 5px;
            border: none;
            background: #f5f5f5;
            color: #333;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .reset-option button.active {
            background: #ff6600;
            color: #fff;
        }
        .reset-input {
            display: none;
        }
    </style>
</head>
<body>
    
    <!-- Forgot Password Section -->
    <div id="forgot-section" class="section">
        <div class="container">
            <div class="row signup-container">
    
                <!-- Left Side -->
                <div class="col-md-6 col-sm-12 signup-welcome text-center">
                    <div class="welcome-content">
                        <h2>Forgot Your Password?</h2>
                        <p>No worries — reset your password quickly using your registered email or phone number.</p>
                    </div>
                </div>
    
                <!-- Right Side -->
                <div class="col-md-6 col-sm-12 signup-form-wrapper">
                    <div class="signup-form">
                        <h3>Reset Your Password</h3>

                        <!-- Messages -->
                        <p class="text-danger text-center" id="error" style="display:none;"></p>
                        <p class="text-success text-center" id="success" style="display:none;"></p>

                        <div class="reset-option">
                            <button type="button" id="emailOption" class="active">Email</button>
                            <button type="button" id="phoneOption">Phone</button>
                        </div>

                        <form id="forgotForm" method="post">
                            <!-- Email Input -->
                            <div class="form-group reset-input" id="emailInput" style="display:block;">
                                <input type="email" class="input" name="email" placeholder="Enter your email address">
                            </div>

                            <!-- Phone Input -->
                            <div class="form-group reset-input" id="phoneInput">
                                <input type="tel" class="input" id="phoneField" name="phone" placeholder="Enter your phone number">
                            </div>

                            <button type="submit" class="main-button btn btn-block">Send Reset Link</button>
                        </form>

                        <p class="login-link text-center">Remembered your password? <a href="login.php">Log in here</a></p>
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
        // Initialize intl-tel-input
        const phoneField = document.querySelector("#phoneField");
        const iti = window.intlTelInput(phoneField, {
            initialCountry: "cm",
            preferredCountries: ["cm", "ng", "gh", "us"],
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
        });

        // Toggle between email and phone reset
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

        // Handle Forgot Password form
        $('#forgotForm').on('submit', function(e){
            e.preventDefault();
            $('#error').hide();
            $('#success').hide();

            let method = $('.reset-option button.active').attr('id');
            let formData = { action: 'forgot_password' };

            if(method === 'emailOption'){
                formData.email = $('input[name="email"]').val().trim();
                if(!formData.email){
                    $('#error').text('Please enter your email address.').show();
                    return;
                }
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
                        $('#forgotForm')[0].reset();
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
