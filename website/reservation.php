<?php
    session_start();
?>

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

    <!-- Header -->
    <header id="header">

        <!-- Top nav -->
        <div id="top-nav">
            <div class="container">

                <!-- logo -->
                <a href="index.php" class="logo">
                    <img src="img/logo.png" alt="logo">
                    <h2>LICRESTOR</h2>
                </a>
                <!-- logo -->

                <!-- Mobile toggle -->
                <button class="navbar-toggle">
                    <span></span>
                </button>
                <!-- Mobile toggle -->

                <!-- social links -->
                <ul class="social-nav">
                    <li><a href="user.php" class="user" title="User Account Info"><i class="fa fa-user"></i></a></li>
                    <li><a href="menu.php#cart-section" class="user" title="User Cart"><i
                                class="fa fa-shopping-cart"></i></a></li>
                </ul>
                <!-- /social links -->

            </div>
        </div>
        <!-- /Top nav -->

        <!-- Bottom nav -->
        <div id="bottom-nav">
            <div class="container">
                <nav id="nav">

                    <!-- nav -->
                    <ul class="main-nav nav navbar-nav">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="reservation.php">Reservation</a></li>
                        <li><a href="event.html">Events</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                    <!-- /nav -->

                    <!-- button nav -->
                    <ul class="cta-nav">
                        <li><a href="signup.php" class="main-button">signup</a></li>
                    </ul>
                    <!-- button nav -->

                    <!-- contact nav -->
                    <ul class="contact-nav nav navbar-nav">
                        <li><a href="#"><i class="fa fa-phone"></i> +237 674 467 985</a></li>
                        <li><a href="#"><i class="fa fa-map-marker"></i> Ekounou Yaounde</a></li>
                    </ul>
                    <!-- contact nav -->

                </nav>
            </div>
        </div>
        <!-- /Bottom nav -->


    </header>
    <!-- /Header -->

    <div class="banner-area">

        <!-- Backgound Image -->
        <div class="bg-image bg-parallax overlay" style="background-image:url(./img/background01.jpg)"></div>
        <!-- /Backgound Image -->

        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h2 class="white-text title">Reservation</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div id="reservation-steps" class="section reservation-steps">
        <div class="container">
            <div class="row">
                <div class="section-header text-center">
                    <h4 class="sub-title">Simple Process</h4>
                    <h2 class="title">How to Reserve Your Table</h2>
                    <p>Follow these easy steps to enjoy a seamless dining experience at LIC Restaurant.</p>
                </div>
            </div>
            <div class="row steps-row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="step-card text-center">
                        <div class="step-icon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        <h4>1. Fill Out the Form</h4>
                        <p>Provide your details, preferred date, and time through our online reservation form.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="step-card text-center">
                        <div class="step-icon">
                            <i class="fa fa-envelope-open-o"></i>
                        </div>
                        <h4>2. Receive Confirmation</h4>
                        <p>Our team will contact you shortly to confirm your reservation and special requests.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="step-card text-center">
                        <div class="step-icon">
                            <i class="fa fa-cutlery"></i>
                        </div>
                        <h4>3. Enjoy Your Dining</h4>
                        <p>Arrive on time and savor the best of Cameroonian cuisine, freshly prepared for you.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-12" style="text-align: center; margin-top: 20px;">
                <a href="reservation.php#reservation-form" class="main-button">Reservation Form</a>
            </div>
        </div>
    </div>
    <!-- /How It Works Section -->

    <!-- Reservation Policies Section -->
    <div id="reservation-policies" class="section reservation-policies">
        <div class="container">
            <div class="row">
                <div class="section-header text-center">
                    <h4 class="sub-title">Before You Book</h4>
                    <h2 class="title">Reservation Policies</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="policies-wrapper">
                        <div class="policy-item">
                            <h4><i class="fa fa-money"></i> Deposit & Cancellation</h4>
                            <p>Reservations are free, but for large groups or special events, a small deposit may be
                                required. Cancellations made less than 2 hours before your reservation may result in
                                forfeiture of the deposit.</p>
                        </div>
                        <div class="policy-item">
                            <h4><i class="fa fa-users"></i> Group Bookings</h4>
                            <p>For parties of 8 or more, please reserve at least 24 hours in advance. Group menus and
                                seating arrangements can be customized to suit your event.</p>
                        </div>
                        <div class="policy-item">
                            <h4><i class="fa fa-clock-o"></i> Late Arrival</h4>
                            <p>We hold tables for 15 minutes past your reservation time. If you expect to be late,
                                kindly contact us so we can accommodate your arrival.</p>
                        </div>
                        <div class="policy-item">
                            <h4><i class="fa fa-check-circle"></i> Dress Code</h4>
                            <p>Smart casual is appreciated. We welcome a comfortable yet refined dining experience for
                                all our guests.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Reservation Policies Section -->

    <!-- Special Dining Requests Section -->
    <div id="special-requests" class="section special-requests">
        <div class="container">
            <div class="row">
                <div class="section-header text-center">
                    <h4 class="sub-title">Make It Memorable</h4>
                    <h2 class="title">Special Dining Requests</h2>
                    <p>We love creating unforgettable dining experiences. Let us know your special requests, and we’ll
                        handle the rest!</p>
                </div>
            </div>
            <div class="row requests-row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="request-card text-center">
                        <div class="request-icon">
                            <i class="fa fa-birthday-cake"></i>
                        </div>
                        <h4>Birthday Celebrations</h4>
                        <p>Surprise your loved ones with personalized cakes, decorations, and special touches to make
                            their day unforgettable.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="request-card text-center">
                        <div class="request-icon">
                            <i class="fa fa-gift"></i>
                        </div>
                        <h4>Anniversaries & Special Events</h4>
                        <p>From intimate dinners to grand celebrations, we’ll help plan the perfect menu and ambiance
                            for your special event.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="request-card text-center">
                        <div class="request-icon">
                            <i class="fa fa-star"></i>
                        </div>
                        <h4>VIP & Custom Menus</h4>
                        <p>Request a bespoke dining experience with chef’s recommendations, exclusive dishes, or dietary
                            accommodations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Special Dining Requests Section -->

    <!-- Reservation Form Section -->
    <div id="reservation-form" class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="section-header text-center">
                        <h4 class="sub-title">Book Your Table</h4>
                        <h2 class="title">Make a Reservation</h2>
                        <p>Fill out the form below to reserve your table at LIC Restaurant.</p>
                    </div>

                    <p id="reservation-success" class="text-success text-center" style="display:none;"></p>
                    <p id="reservation-error" class="text-danger text-center" style="display:none;"></p>

                    <form id="reservation-form-js" class="reservation-form">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <input type="text" name="name" class="input" placeholder="Your Name" 
                                    value="<?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : ''; ?>"
                                    <?php echo isset($_SESSION['full_name']) ? 'readonly' : '' ; ?>
                                    required>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <input type="email" name="email" class="input" placeholder="Your Email"
                                    value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                                    <?php echo isset($_SESSION['email']) ? 'readonly' : '' ; ?>
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <input type="tel" name="phone" class="input" placeholder="Phone Number" required pattern="[0-9]{8,15}" title="Enter a valid phone number">
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <input type="date" name="date" class="input" placeholder="Reservation Date" required id="reservation-date">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <input type="time" name="time" class="input" placeholder="Reservation Time" required>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <input type="number" name="guests" class="input" placeholder="Number of Guests" required min="1">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <textarea name="message" class="input" placeholder="Special Requests (Optional)" rows="4" maxlength="500"></textarea>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-12">
                                <button type="submit" class="main-button">Reserve Now</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <!-- /Reservation Form Section -->

    <!-- Success Modal -->
    <div class="modal fade" id="reservationSuccessModal">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Reservation Successful</h4>
        </div>
        <div class="modal-body" id="reservation-success-text"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
        </div>
        </div>
    </div>
    </div>
    <!-- /Success Modal -->

    <!-- Error Modal -->
    <div class="modal fade" id="reservationErrorModal">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header bg-danger">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Reservation Error</h4>
        </div>
        <div class="modal-body" id="reservation-error-text"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>
    <!-- /Error Modal -->


    <!-- Footer -->
    <footer id="footer">

        <div class="container">

            <div class="footer-top">

                <!-- About -->
                <div class="footer-col about">
                    <h4>About LIC Restaurant</h4>
                    <p>Welcome to <strong>LIC Restaurant</strong> – where authentic Cameroonian meals are cooked with
                        love and tradition. Experience the flavors of Cameroon in every bite.</p>
                </div>

                <!-- Contact Info -->
                <div class="footer-col contact">
                    <h4>Contact Us</h4>
                    <ul class="contact-info">
                        <li><i class="fa fa-map-marker"></i> Ekounou Yaounde, Cameroon</li>
                        <li><i class="fa fa-phone"></i> (+237) 674-467-985</li>
                        <li><i class="fa fa-envelope"></i> info@licrestaurant.cm</li>
                    </ul>

                    <h4>Opening Hours</h4>
                    <ul class="hours">
                        <li>Mon - Fri: 10:00 AM - 10:00 PM</li>
                        <li>Sat - Sun: 12:00 PM - 11:00 PM</li>
                    </ul>
                </div>

                <!-- Social & Newsletter -->
                <div class="footer-col social-newsletter">
                    <h4>Follow Us</h4>
                    <ul class="social-nav">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                    </ul>

                    <h4>Newsletter</h4>
                    <p>Subscribe to get updates on our latest dishes and events!</p>
                    <form action="#" method="post" class="newsletter-form">
                        <input class="input" type="email" name="email" placeholder="Your Email" required>
                        <button type="submit" class="main-button">Subscribe</button>
                    </form>
                </div>

            </div>

            <hr>

            <div class="footer-bottom">
                <div class="copyright" style="display: none;">
                    &copy; 2025 All rights reserved | Made with <i class="fa fa-heart-o"></i> by
                    <a href="https://colorlib.com" target="_blank">Colorlib</a>
                </div>
                <div class="copyright">
                    &copy; 2025 All rights reserved | Inspired by
                    <a href="https://colorlib.com" target="_blank">Colorlib</a>
                </div>
                <nav class="footer-nav">
                    <a href="terms.html">Terms and conditions</a>
                    <a href="faq.html">FAQ</a>
                    <a href="privacy.html">Privacy Policies</a>
                </nav>
            </div>

        </div>

    </footer>

    <!-- /Footer -->

    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <!-- /Preloader -->

    <!-- jQuery Plugins -->
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/owl.carousel.min.js"></script>
    <script type="text/javascript" src="js/main.js"></script>

    <script>
        $(document).ready(function() {

            const today = new Date().toISOString().split('T')[0];
            $('#reservation-date').attr('min', today);

            $('#reservation-form-js').on('submit', function(e) {
                e.preventDefault();

                const submitBtn = $('.main-button');
                const originalBtnText = submitBtn.html();

                // Show loading state
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

                const name = $('input[name="name"]').val().trim();
                const email = $('input[name="email"]').val().trim();
                const phone = $('input[name="phone"]').val().trim();
                const date = $('input[name="date"]').val();
                const time = $('input[name="time"]').val();
                const guests = parseInt($('input[name="guests"]').val());
                const message = $('textarea[name="message"]').val().trim();

                const fail = (msg) => {
                    $('#reservation-error-text').text(msg);
                    $('#reservationErrorModal').modal('show');

                    // Restore button
                    submitBtn.prop('disabled', false).html(originalBtnText);
                };

                if (!name || !email || !phone || !date || !time || !guests) {
                    return fail('Please fill in all required fields.');
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) return fail('Invalid email address.');

                const phoneRegex = /^[0-9]{8,15}$/;
                if (!phoneRegex.test(phone)) return fail('Invalid phone number.');

                if (guests < 1) return fail('Guests must be at least 1.');

                const now = new Date();
                const reservationDateTime = new Date(date + " " + time);

                const diffHours = (reservationDateTime - now) / (1000 * 60 * 60);

                if (guests < 8 && diffHours < 2)
                    return fail('Reservations for fewer than 8 guests must be made at least 2 hours in advance.');

                if (guests >= 8 && diffHours < 24)
                    return fail('Reservations for 8 or more guests must be made at least 24 hours in advance.');

                $.ajax({
                    url: 'include/reservation.php',
                    type: 'POST',
                    data: { name, email, phone, date, time, guests, message },
                    dataType: 'json',
                    success: function(r) {
                        if (r.status === 'success') {
                            $('#reservation-success-text').text(r.message);
                            $('#reservationSuccessModal').modal('show');
                            $('#reservation-form-js')[0].reset();
                        } else {
                            fail(r.message);
                            return;
                        }

                        // Restore button after success modal opens
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    },
                    error: function() {
                        fail('Something went wrong. Please try again.');
                    }
                });
            });
        });
    </script>

</body>

</html>