<?php
    if (!isset($_SESSION['user_id'])) {
        echo "
            <script> window.location.href = 'login.php'; </script>
        ";
        exit;
    }
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
        <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700%7CCabin:400%7CDancing+Script" rel="stylesheet">

        <!-- Bootstrap -->
        <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>

        <!-- Owl Carousel -->
        <link type="text/css" rel="stylesheet" href="css/owl.carousel.css" />
        <link type="text/css" rel="stylesheet" href="css/owl.theme.default.css" />

        <!-- Font Awesome Icon -->
        <link rel="stylesheet" href="css/font-awesome.min.css">

        <!-- Custom stlylesheet -->
        <link type="text/css" rel="stylesheet" href="css/style.css"/>
    </head>
    <body>

        <!-- Header -->
        <header id="header">

            <!-- Top nav -->
            <div id="top-nav">
                <div class="container">

                <!-- logo -->
                <a href="index.html" class="logo">
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
                    <li><a href="menu.html#cart-section" class="user" title="User Cart"><i class="fa fa-shopping-cart"></i></a></li>
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
						<li><a href="index.html">Home</a></li>
						<li><a href="menu.html">Menu</a></li>
						<li><a href="reservation.html">Reservation</a></li>
						<li><a href="event.html">Events</a></li>
						<li><a href="about.html">About</a></li>
						<li><a href="contact.html">Contact</a></li>
					</ul>
                    <!-- /nav -->

                    <!-- button nav -->
                    <ul class="cta-nav">
                        <li><a href="#reward-points" class="main-button">My Points</a></li>
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
						<h2 class="white-text title">Contact Us</h2>
					</div>
				</div>
			</div>
		</div>
        
        <!-- User Info Page -->
        <div id="user-info-page" class="section">

            <div class="container">
                <div class="row">

                    <!-- Section Header -->
                    <div class="col-md-12 text-center">
                        <div class="section-header">
                            <h4 class="sub-title">Your Profile</h4>
                            <h2 class="title">Account Information</h2>
                            <p>Manage your profile, update your details, and view your account stats.</p>
                        </div>
                    </div>

                </div>

                <div class="row user-info-row">

                    <!-- Left Side: Profile Summary -->
                    <div class="col-md-4 col-sm-12 user-info-col">
                        <div class="profile-summary text-center">
                            <div class="profile-picture">
                                <img src="img/avatar.png" alt="User Profile" class="img-responsive">
                            </div>
                            <h3 class="user-name">John Doe</h3>
                            <p class="user-email">johndoe@email.com</p>

                            <ul class="account-stats list-unstyled">
                                <li><strong>Reservations:</strong> 12</li>
                                <li><strong>Points:</strong> 320</li>
                                <li><strong>Member Since:</strong> Jan 2023</li>
                            </ul>

                            <label class="btn main-button btn-block" for="profileUpload">Upload New Photo</label>
                            <input type="file" id="profileUpload" style="display: none;" accept="image/*">

                        </div>
                    </div>
                    <!-- /Profile Summary -->

                    <!-- Right Side: Edit Profile Form -->
                    <div class="col-md-8 col-sm-12 user-info-col">
                        <div class="edit-profile-form">
                            <h3>Edit Your Profile</h3>
                            <form action="#" method="post">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="full-name">Full Name</label>
                                            <input type="text" class="input" id="full-name" name="name" value="John Doe" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" class="input" id="email" name="email" value="johndoe@email.com" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="text" class="input" id="phone" name="phone" value="+237 699 123 456" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <input type="text" class="input" id="address" name="address" value="123 Main St, Yaounde" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group password-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="input" id="password" name="password" placeholder="Enter New Password">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group password-group">
                                            <label for="confirm-password">Confirm Password</label>
                                            <input type="password" class="input" id="confirm-password" name="confirm_password" placeholder="Confirm New Password">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="main-button btn btn-block" id="updateProfileBtn">Update Profile</button>

                            </form>
                        </div>
                    </div>
                    <!-- /Edit Profile Form -->

                </div>
                <!-- /User Info Row -->

            </div>
            <!-- /Container -->

        </div>
        <!-- /User Info Page -->

        <!-- Profile Updated Modal -->
        <div id="profileUpdatedModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content text-center">
                <div class="modal-header">
                    <h4 class="modal-title">Success</h4>
                </div>
                <div class="modal-body">
                    <p>Your profile has been updated successfully!</p>
                </div>
                <div class="modal-footer text-center">
                    <button type="button" class="btn main-button" data-dismiss="modal">OK</button>
                </div>
                </div>
            </div>
        </div>
        <!-- /Profile Updated Modal -->


        <!-- Reservation History Section -->
        <div id="reservation-history" class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-header text-center">
                            <h4 class="sub-title">Your Reservations</h4>
                            <h2 class="title">Reservation History</h2>
                            <p>View all your past and upcoming reservations at LIC Restaurant.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered reservation-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                        <th>Special Requests</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2025-10-01</td>
                                        <td>19:00</td>
                                        <td>4</td>
                                        <td><span class="status confirmed">Confirmed</span></td>
                                        <td>Birthday cake</td>
                                    </tr>
                                    <tr>
                                        <td>2025-09-20</td>
                                        <td>18:30</td>
                                        <td>2</td>
                                        <td><span class="status completed">Completed</span></td>
                                        <td>Window seat</td>
                                    </tr>
                                    <tr>
                                        <td>2025-09-15</td>
                                        <td>20:00</td>
                                        <td>6</td>
                                        <td><span class="status cancelled">Cancelled</span></td>
                                        <td>Vegan menu</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Reservation History Section -->

        <!-- How to Get Points -->
        <div id="how-to-get-points" class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div class="section-header">
                            <h4 class="sub-title">Earn More Rewards</h4>
                            <h2 class="title">How to Get Points</h2>
                            <p>Collect points with every visit and enjoy amazing rewards!</p>
                        </div>
                    </div>
                </div>

                <div class="row points-earning-cards">
                    <div class="col-md-4 col-sm-6">
                        <div class="points-card text-center" data-points="10 points per $1 spent">
                            <div class="points-icon">
                                <i class="fa fa-cutlery fa-2x"></i>
                            </div>
                            <h4>Dine at LIC Restaurant</h4>
                            <p>Earn points for every meal!</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="points-card text-center" data-points="50 bonus points per event">
                            <div class="points-icon">
                                <i class="fa fa-birthday-cake fa-2x"></i>
                            </div>
                            <h4>Special Events</h4>
                            <p>Participate in our special events!</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="points-card text-center" data-points="20 points per referral">
                            <div class="points-icon">
                                <i class="fa fa-share-alt fa-2x"></i>
                            </div>
                            <h4>Refer a Friend</h4>
                            <p>Invite friends to join LIC Restaurant rewards!</p>
                        </div>
                    </div>
                </div>

                <!-- Points Info Alert -->
                <div id="points-alert" class="alert alert-success text-center mt-3" style="display:none;"></div>
            </div>
        </div>
        <!-- How to Get Points -->

        <!-- Reward Points Section -->
        <div id="reward-points" class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-header text-center">
                            <h4 class="sub-title">Your Rewards</h4>
                            <h2 class="title">Loyalty Points</h2>
                            <p>Check your points and redeem them for exciting rewards!</p>
                        </div>
        
                        <div class="points-wrapper text-center mb-4">
                            <h3>Total Points: <span class="points-count">320</span></h3>
                        </div>
        
                        <div class="rewards-list row text-center">
                            <div class="col-md-4 col-sm-6 reward-item">
                                <div class="reward-card">
                                    <h4>Free Drink</h4>
                                    <p>Cost: 50 Points</p>
                                    <button class="btn main-button redeem-btn" data-cost="50">Redeem</button>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 reward-item">
                                <div class="reward-card">
                                    <h4>10% Discount</h4>
                                    <p>Cost: 100 Points</p>
                                    <button class="btn main-button redeem-btn" data-cost="100">Redeem</button>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 reward-item">
                                <div class="reward-card">
                                    <h4>Free Dessert</h4>
                                    <p>Cost: 150 Points</p>
                                    <button class="btn main-button redeem-btn" data-cost="150">Redeem</button>
                                </div>
                            </div>
                        </div>
        
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal -->
        <div id="redeemModal" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content text-center">
              <div class="modal-header">
                <h4 class="modal-title">Reward Status</h4>
              </div>
              <div class="modal-body">
                <p id="modal-message"></p>
              </div>
              <div class="modal-footer text-center">
                <button type="button" class="btn main-button" data-dismiss="modal">OK</button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- /Reward Points Section -->

		<!-- Footer -->
        <footer id="footer">

            <div class="container">
        
                <div class="footer-top">
        
                    <!-- About -->
                    <div class="footer-col about">
                        <h4>About LIC Restaurant</h4>
                        <p>Welcome to <strong>LIC Restaurant</strong> – where authentic Cameroonian meals are cooked with love and tradition. Experience the flavors of Cameroon in every bite.</p>
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
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
        <script type="text/javascript" src="js/google-map.js"></script>
        <script type="text/javascript" src="js/main.js"></script>  

        <script>
        $(document).ready(function() {

            // ----------------------------
            // Fetch user profile on load
            // ----------------------------
            function loadUserProfile() {
                $.ajax({
                    url: 'include/auth.php',
                    type: 'POST',
                    data: { action: 'get_profile' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const data = response.data;
                            $('#full-name').val(data.full_name);
                            $('#email').val(data.email);
                            $('#phone').val(data.phone);
                            $('#address').val(data.address);
                            if (data.profile_picture) {
                                $('.profile-picture img').attr('src', '../uploads/' + data.profile_picture);
                            }
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Failed to fetch profile data.');
                    }
                });
            }

            loadUserProfile();

            // ----------------------------
            // Profile picture upload instantly
            // ----------------------------
            $('#profileUpload').on('change', function() {
                const file = this.files[0];
                if (!file) return;

                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('.profile-picture img').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);

                // Upload the image immediately
                const formData = new FormData();
                formData.append('action', 'upload_profile_picture');
                formData.append('profile_picture', file);

                $.ajax({
                    url: 'include/auth.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Optional toast or small success message
                            console.log('Profile picture updated successfully.');
                        } else {
                            alert(response.message || 'Failed to upload profile picture.');
                            loadUserProfile(); // revert to old picture
                        }
                    },
                    error: function() {
                        alert('Error uploading profile picture.');
                    }
                });
            });

            // ----------------------------
            // Update profile via AJAX
            // ----------------------------
            $('#updateProfileBtn').on('click', function() {
                const formData = new FormData();
                formData.append('action', 'edit_profile');
                formData.append('name', $('#full-name').val());
                formData.append('email', $('#email').val());
                formData.append('phone', $('#phone').val());
                formData.append('address', $('#address').val());
                formData.append('password', $('#password').val());
                formData.append('confirm_password', $('#confirm-password').val());

                $.ajax({
                    url: 'include/auth.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#profileUpdatedModal').modal('show');
                            loadUserProfile();
                            $('#password').val('');
                            $('#confirm-password').val('');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Failed to update profile.');
                    }
                });
            });

        });
        </script>

    </body>
</html>
