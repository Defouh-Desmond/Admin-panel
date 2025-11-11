<?php
require_once '../classes/connection.php';

// Fetch active categories
$categoryQuery = $mysqli->query("SELECT category_id, name, slug FROM categories WHERE status='active' ORDER BY name ASC");
if (!$categoryQuery) {
    die("Category Query Error: " . $mysqli->error);
}

// Product Query
$productQuery = $mysqli->query("
    SELECT 
        p.product_id, p.name, p.description, p.price, p.old_price, c.slug,
        (
            SELECT image_path 
            FROM product_images 
            WHERE product_id = p.product_id 
            AND is_main = 1 
            LIMIT 1
        ) AS main_image
    FROM products p
    INNER JOIN categories c ON p.category_id = c.category_id
    WHERE p.status = 'active'
    ORDER BY p.product_id DESC
");

if (!$productQuery) {
    die("Product Query Error: " . $mysqli->error);
}


$products = [];
while ($row = $productQuery->fetch_assoc()) {
    $products[$row['slug']][] = $row;
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
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                    <!-- /nav -->

                    <!-- button nav -->
                    <ul class="cta-nav">
                        <li><a href="#cart-section" class="main-button cart-button">Cart</a></li>
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
                    <div class="image">
                        <h2 class="white-text title">Menu</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MENU SECTION -->
    <div id="menu-section" class="section">

        <!-- container -->
        <div class="container">

            <!-- row -->
            <div class="row">

                <!-- section header -->
                <div class="section-header text-center">
                    <div class="image">
                        <h4 class="sub-title">Our Menu</h4>
                    </div>
                    <h2 class="title">Taste the Best at Licrestor</h2>
                    <p>Explore our delicious selection of meals, drinks, and desserts — freshly prepared for you.</p>
                </div>

                <!-- MENU SECTION -->
                <div class="col-md-8 menu-section">

                    <!-- CATEGORY TABS -->
                    <ul class="nav nav-tabs" role="tablist">
                        <?php 
                        $activeTab = true;
                        $categoryQuery->data_seek(0);
                        while ($cat = $categoryQuery->fetch_assoc()): ?>
                            <li class="<?php if ($activeTab){echo 'active'; $activeTab=false;} ?>">
                                <a href="#<?php echo $cat['slug']; ?>" role="tab" data-toggle="tab">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <div class="tab-content" style="margin-top:20px;">

                        <?php
                        $activePane = true;
                        $categoryQuery->data_seek(0);
                        while ($cat = $categoryQuery->fetch_assoc()):
                            $slug = $cat['slug'];
                        ?>
                        <div class="tab-pane fade in <?php if($activePane){echo 'active'; $activePane=false;} ?>" id="<?php echo $slug; ?>">
                            <div class="row">
                                
                                <?php if(isset($products[$slug])): ?>
                                    <?php foreach($products[$slug] as $meal): ?>

                                        <?php 
                                            $image = $meal['main_image']; 
                                        ?>

                                        <div class="col-sm-6">
                                            <div class="meal" data-name="<?php echo htmlspecialchars($meal['name']); ?>" data-price="<?php echo $meal['price']; ?>">
                                                <img src="<?php echo $image ? "../uploads/Products/$image" : "img/image01.jpg"; ?>" 
                                                alt="<?php echo htmlspecialchars($meal['name']); ?>" 
                                                style="width:100%; height:200px; object-fit:cover;">

                                                
                                                <div class="details">
                                                    <h4 class="name"><?php echo htmlspecialchars($meal['name']); ?></h4>
                                                    <div class="price">
                                                        <p><?php echo $meal['price']; ?> FCFA</p>
                                                        <?php if(!empty($meal['old_price']) && $meal['old_price'] > $meal['price']): ?>
                                                            <strike><?php echo $meal['old_price']; ?> FCFA</strike>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <p><?php echo htmlspecialchars($meal['description']); ?></p>

                                                <button class="btn btn-block main-button add-to-cart">
                                                    Add to Cart
                                                </button>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="padding:10px;">No meals available in this category.</p>
                                <?php endif; ?>

                            </div>
                        </div>
                        <?php endwhile; ?>

                    </div>
                </div>
                <!-- /MENU SECTION -->

                <!-- CART SECTION -->
                <div id="cart-section" class="col-md-4 cart-section">
                    <div class="cart">
                        <h4><i class="fa fa-shopping-cart"></i> Your Cart</h4>
                        <ul id="cart-items" class="list-group"></ul>
                        <p><strong>Total:</strong> $<span id="cart-total">0.00</span></p>
                        <button class="btn btn-primary btn-block checkout" data-toggle="modal"
                            data-target="#orderSummaryModal">
                            <i class="fa fa-credit-card"></i> Checkout
                        </button>
                    </div>
                </div>
                <!-- /CART SECTION -->

            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /section -->

    <!-- ORDER SUMMARY MODAL -->
    <div class="modal fade" id="orderSummaryModal" tabindex="-1" role="dialog" aria-labelledby="orderSummaryLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-list-alt"></i> Order Summary</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered summary-items">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody id="summary-items"></tbody>
                    </table>

                    <p class="text-right"><strong>Total:</strong> $<span id="summary-total">0.00</span></p>
                    <hr>

                    <h5><i class="fa fa-user"></i> Continue as:</h5>
                    <a href="checkout.html" class="btn checkout btn-default btn-block"><i class="fa fa-user-o"></i>
                        Continue as Guest</a>
                    <a href="login.php" class="btn main-button btn-block"><i class="fa fa-sign-in"></i> Login</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Reviews & Testimonials -->
    <div id="reviews" class="section">

        <!-- container -->
        <div class="container">

            <!-- row -->
            <div class="row">

                <!-- section header -->
                <div class="section-header text-center">
                    <div class="image">
                        <h4 class="sub-title">Customer Reviews</h4>
                    </div>
                    <h2 class="title">What Our Guests Say</h2>
                    <p>Hear directly from our happy customers and their experiences at Licrestor.</p>
                </div>
                <!-- /section header -->

                <!-- Testimonials Carousel -->
                <div class="owl-carousel owl-theme" style="padding: 10px;">

                    <!-- Testimonial 1 -->
                    <div class="testimonial text-center">
                        <div class="image">
                            <img src="img/background01.jpg" class="img-responsive" alt="Customer 1">
                        </div>
                        <h5 class="name"><strong>John Doe</strong></h5>
                        <p><strong>"</strong>The grilled chicken was absolutely delicious, and the service was
                            top-notch!<strong>"</strong></p>
                        <div class="ratting">
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star-half-o ratting-star"></i>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="testimonial text-center">
                        <div class="image">
                            <img src="img/background02.jpg" class="img-responsive" alt="Customer 2">
                        </div>
                        <h5 class="name"><strong>Jane Smith</strong></h5>
                        <p><strong>"</strong>Amazing ambiance and the desserts were heavenly! Highly recommend
                            Licrestor.<strong>"</strong></p>
                        <div class="ratting">
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="testimonial text-center">
                        <div class="image">
                            <img src="img/background03.jpg" class="img-responsive" alt="Customer 3">
                        </div>
                        <h5 class="name"><strong>Michael Lee</strong></h5>
                        <p><strong>"</strong>Quick delivery and every meal tasted fresh and amazing. Will order
                            again!<strong>"</strong></p>
                        <div class="ratting">
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star-half-o ratting-star"></i>
                            <i class="fa fa-star-o ratting-star"></i>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="testimonial text-center">
                        <div class="image">
                            <img src="img/blog-post.jpg" class="img-responsive" alt="Customer 3">
                        </div>
                        <h5 class="name"><strong>Johny English</strong></h5>
                        <p><strong>"</strong>Quick delivery and every meal tasted fresh and amazing. Will order
                            again!<strong>"</strong></p>
                        <div class="ratting">
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star ratting-star"></i>
                            <i class="fa fa-star-half-o ratting-star"></i>
                            <i class="fa fa-star-o ratting-star"></i>
                        </div>
                    </div>

                </div>
                <!-- /Testimonials Carousel -->

            </div>
            <!-- /row -->

        </div>
        <!-- /container -->

    </div>
    <!-- /Reviews & Testimonials -->

    <!-- Why Choose Us Section -->
    <div class="section why-choose-us">
        <div class="container">
            <div class="row">
                <div class="section-header text-center mb-5">
                    <h4 class="sub-title">What Makes Us Special</h4>
                    <h2 class="title">Why Choose Us</h2>
                </div>
            </div>
            <div class="row requests-row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="request-card text-center">
                        <div class="request-icon">
                            <i class="fa fa-cutlery"></i>
                        </div>
                        <h4>Authentic Cameroonian Taste</h4>
                        <p>We use local ingredients and traditional recipes to deliver the true flavors of Cameroon in
                            every meal.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="request-card text-center">
                        <div class="request-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <h4>Friendly Atmosphere</h4>
                        <p>Enjoy your meal in a cozy and welcoming space where our team treats you like family.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="request-card text-center">
                        <div class="request-icon">
                            <i class="fa fa-leaf"></i>
                        </div>
                        <h4>Fresh & Local Ingredients</h4>
                        <p>We partner with local farmers and suppliers to ensure every dish is fresh, healthy, and full
                            of flavor.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="request-card text-center">
                        <div class="request-icon">
                            <i class="fa fa-heart"></i>
                        </div>
                        <h4>Passion for Excellence</h4>
                        <p>Every dish is crafted with love, care, and a commitment to giving our guests a truly
                            memorable dining experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Why Choose Us Section -->

    <!-- Footer -->
    <footer id="footer">

        <div class="container">

            <div class="footer-top">

                <!-- About -->
                <div class="footer-col about">
                    <h4 class="name">About LIC Restaurant</h4>
                    <p>Welcome to <strong>LIC Restaurant</strong> – where authentic Cameroonian meals are cooked with
                        love and tradition. Experience the flavors of Cameroon in every bite.</p>
                </div>

                <!-- Contact Info -->
                <div class="footer-col contact">
                    <h4 class="name">Contact Us</h4>
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
                    <h4 class="name">Follow Us</h4>
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
        $(function () {

            // Function to update total
            function updateTotal() {
                var total = 0;
                $('#cart-items li').each(function () {
                    var qty = parseInt($(this).find('.qty-input').val());
                    var price = parseFloat($(this).find('.price').data('price'));
                    var itemTotal = qty * price;
                    $(this).find('.price').text(itemTotal + ' FCFA');
                    total += itemTotal;
                });
                $('#cart-total').text(total);
            }

            // Add to cart
            $('.add-to-cart').click(function () {
                var meal = $(this).closest('.meal');
                var name = meal.data('name');
                var price = parseFloat(meal.data('price'));
                var existing = $('#cart-items li[data-name="' + name + '"]');

                if (existing.length > 0) {
                    var qtyInput = existing.find('.qty-input');
                    qtyInput.val(parseInt(qtyInput.val()) + 1);
                } else {
                    $('#cart-items').append(
                        '<li class="cart-item" data-name="' + name + '">' + '<p>' + name + '</p>' +
                        '<div class="item-controls">' +
                        '<button class="qty-minus"><i class="fa fa-minus"></i></button>' +
                        '<input type="text" class="qty-input" value="1">' +
                        '<button class="qty-plus"><i class="fa fa-plus"></i></button>' +
                        '<span class="price" data-price="' + price + '">' + price + 'FCFA </span>' +
                        '<button class="remove-item"><i class="fa fa-trash"></i></button>' +
                        '</div>' +
                        '</li>'
                    );

                }
                updateTotal();
            });

            // Quantity +/-
            $(document).on('click', '.qty-plus', function () {
                var input = $(this).siblings('.qty-input');
                input.val(parseInt(input.val()) + 1);
                updateTotal();
            });

            $(document).on('click', '.qty-minus', function () {
                var input = $(this).siblings('.qty-input');
                var val = parseInt(input.val());
                if (val > 1) input.val(val - 1);
                updateTotal();
            });

            // Remove item
            $(document).on('click', '.remove-item', function () {
                $(this).closest('li').remove();
                updateTotal();
            });

            // Update summary modal
            $('#orderSummaryModal').on('show.bs.modal', function () {
                var summary = $('#summary-items');
                summary.empty();
                $('#cart-items li').each(function () {
                    var name = $(this).data('name');
                    var qty = $(this).find('.qty-input').val();
                    var price = $(this).find('.price').text();
                    summary.append(
                        '<tr>' +
                        '<td>' + name + '</td>' +
                        '<td>' + qty + '</td>' +
                        '<td>' + price + '</td>' +
                        '</tr>'
                    );
                });
                $('#summary-total').text($('#cart-total').text());
            });

        });
    </script>


</body>

</html>