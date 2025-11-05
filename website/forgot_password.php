<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Licrestor - Forgot Password</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700%7CCabin:400%7CDancing+Script" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="css/font-awesome.min.css">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/style.css"/>

    <style>
        #countdown {
            font-size: 16px;
            font-weight: bold;
            color: #ff6600;
            text-align: center;
            margin-top: 10px;
            display: none;
        }

        .disabled-btn {
            opacity: 0.6;
            pointer-events: none;
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
                        <p>No worries — reset your password quickly using your registered email address.</p>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-6 col-sm-12 signup-form-wrapper">
                    <div class="signup-form">
                        <h3>Reset Your Password</h3>

                        <!-- Messages -->
                        <p class="text-danger text-center" id="error" style="display:none;"></p>
                        <p class="text-success text-center" id="success" style="display:none;"></p>
                        <p id="countdown"></p>

                        <!-- Forgot Password Form -->
                        <form id="forgotForm" method="post">
                            <div class="form-group">
                                <input type="email" class="input" name="email" placeholder="Enter your email address" required>
                            </div>

                            <button type="submit" id="submitBtn" class="main-button btn btn-block">Send Reset Link</button>
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

    <script>
    // Handle Forgot Password form submission
    $('#forgotForm').on('submit', function(e){
        e.preventDefault();
        $('#error').hide();
        $('#success').hide();
        $('#countdown').hide();

        const email = $('input[name="email"]').val().trim();

        if(!email){
            $('#error').text('Please enter your email address.').show();
            return;
        }

        $('#submitBtn').addClass('disabled-btn').text('Sending...');

        $.ajax({
            type: 'POST',
            url: 'include/forgot_password.php',
            data: { email: email },
            dataType: 'json',
            success: function(response){
                $('#submitBtn').removeClass('disabled-btn').text('Send Reset Link');

                if(response.status === 'success'){
                    $('#success').text(response.message).show();
                    $('#forgotForm')[0].reset();

                    if (response.expires_at) {
                        startCountdown(response.expires_at);
                    }
                } else if(response.status === 'cooldown'){
                    $('#error').text(response.message).show();
                    if (response.expires_at) {
                        startCountdown(response.expires_at);
                    }
                } else {
                    $('#error').text(response.message).show();
                }
            },
            error: function(xhr){
                console.error(xhr.responseText);
                $('#submitBtn').removeClass('disabled-btn').text('Send Reset Link');
                $('#error').text('Something went wrong. Please try again.').show();
            }
        });
    });

    // Countdown timer for cooldown
    function startCountdown(expiryTime){
        const countdownElem = $('#countdown');
        const submitBtn = $('#submitBtn');
        countdownElem.show();
        submitBtn.addClass('disabled-btn');

        const endTime = new Date(expiryTime).getTime();

        const timer = setInterval(function(){
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance <= 0) {
                clearInterval(timer);
                countdownElem.text("⏰ You can now request a new reset link.");
                submitBtn.removeClass('disabled-btn');
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElem.text(`⏳ Please wait ${minutes}:${seconds < 10 ? '0' + seconds : seconds} before requesting again.`);
        }, 1000);
    }
    </script>

</body>
</html>
