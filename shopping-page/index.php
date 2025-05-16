<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add to wishlist
if (isset($_POST['add_to_wishlist'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id']; // Assuming user is logged in and user_id is stored in session

    $query = "INSERT INTO wishlist (user_id, product_id) VALUES ('$user_id', '$product_id')";
    if ($conn->query($query) === TRUE) {
        $_SESSION['message'] = "Product added to wishlist!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
    header("Location: index.php");
    exit();
}

// Remove from wishlist
if (isset($_POST['remove_from_wishlist'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];

    $query = "DELETE FROM wishlist WHERE user_id='$user_id' AND product_id='$product_id'";
    if ($conn->query($query) === TRUE) {
        $_SESSION['message'] = "Product removed from wishlist!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
    header("Location: index.php");
    exit();
}

// Fetch products from the database
$query = "SELECT * FROM products";
$result = $conn->query($query);

// Fetch wishlist products
$user_id = $_SESSION['user_id'];
$wishlist_query = "SELECT products.*, wishlist.product_id FROM products JOIN wishlist ON products.id = wishlist.product_id WHERE wishlist.user_id = '$user_id'";
$wishlist_result = $conn->query($wishlist_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Page</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 90%; margin: auto; }
        .product-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .product { border: 1px solid #ddd; padding: 15px; width: 250px; text-align: center; position: relative; }
        .product img { width: 100%; height: 200px; object-fit: cover; }
        .product button { background: #ff6600; color: white; padding: 10px; border: none; cursor: pointer; }
        .discount { color: red; font-weight: bold; }
        .wishlist-btn { position: absolute; top: 10px; right: 10px; background: none; border: none; cursor: pointer; font-size: 24px; }
        .message { background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin-bottom: 20px; }
    </style>
</head>
<body>
    <!--=============== HEADER ===============-->
    <header class="header">
        <div class="header__top">
            <div class="header__container container">
                <div class="header__contact">
                    <span>(+01) - 2345 - 6789</span>
                    <span>Our location</span>
                </div>
                <p class="header__alert-news">
                    Super Values Deals - Save more coupons
                </p>
                <a href="login-register.html" class="header__top-action">
                    Log In / Sign Up
                </a>
            </div>
        </div>

        <nav class="nav container">
            <a href="index.php" class="nav__logo">
                <img class="nav__logo-img" src="logo.svg" alt="website logo" />
            </a>
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="shop.html" class="nav__link">Shop</a>
                    </li>
                    <li class="nav__item">
                        <a href="accounts.php" class="nav__link">My Account</a>
                    </li>
                    <li class="nav__item">
                        <a href="compare.html" class="nav__link">Compare</a>
                    </li>
                    <li class="nav__item">
                        <a href="Categories.html" class="nav__link">Categories</a>
                    </li>
                </ul>
                <div class="header__search">
                    <input type="text" placeholder="Search For Items..." class="form__input" />
                    <button class="search__btn">
                        <img src="search.png" alt="search icon" />
                    </button>
                </div>
            </div>
            <div class="header__user-actions">
                <a href="wishlist.php" class="header__action-btn" title="Wishlist">
                    <img src="icon-heart.svg" alt="" />
                    <span class="count">3</span>
                </a>
                <a href="cart.html" class="header__action-btn" title="Cart">
                    <img src="icon-cart.svg" alt="" />
                    <span class="count">3</span>
                </a>
            </div>
        </nav>
    </header>

    <!--=============== MAIN ===============-->
    <main class="main">
        <!--=============== BREADCRUMB ===============-->
        <section class="breadcrumb">
            <ul class="breadcrumb__list flex container">
                <li><a href="index.php" class="breadcrumb__link">Home</a></li>
                <li><span class="breadcrumb__link"></span>></li>
                <li><span class="breadcrumb__link">Shop</span></li>
            </ul>
        </section>

        <!--=============== PRODUCTS ===============-->
        <section class="products container section--lg">
            <div class="products__container grid">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message">
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        ?>
                    </div>
                <?php endif; ?>
                <?php
                // Loop through each product
                while ($row = $result->fetch_assoc()) {
                    // Convert local path to web-accessible URL
                    $local_path = $row['image']; // e.g., "C:/uploads/funco.jpg"
                    $web_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $local_path);
                    
                    // Calculate discount percentage
                    $discount = round((($row['old_price'] - $row['new_price']) / $row['old_price']) * 100);
                ?>
                    <div class="product">
                        <!-- Wishlist Button -->
                        <form method="post" action="index.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['new_price']; ?>">
                            <input type="hidden" name="product_image" value="<?php echo $row['image']; ?>">
                            <button type="submit" name="add_to_wishlist" class="wishlist-btn">&#9829;</button>
                        </form>
                        <!-- Display the product image using the web-accessible URL -->
                        <img src="<?php echo $web_path; ?>" alt="Product Image">
                        <h2><?php echo $row['name']; ?></h2>
                        <p>
                            <s>$<?php echo $row['old_price']; ?></s>
                            <strong>$<?php echo $row['new_price']; ?></strong>
                        </p>
                        <p class="discount"><?php echo $discount; ?>% OFF</p>
                        <p><?php echo $row['details']; ?></p>
                        <p>Category: <?php echo $row['category']; ?></p>
                        <p>Stock: <?php echo $row['stock']; ?> available</p>
                        <!-- Add to Cart Form -->
                        <form method="post" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['new_price']; ?>">
                            <input type="hidden" name="product_image" value="<?php echo $row['image']; ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </section>

        <!--=============== WISHLIST ===============-->
        <section class="wishlist container section--lg">
            <h1>My Wishlist</h1>
            <div class="product-grid">
                <?php
                // Loop through each product in the wishlist
                while ($row = $wishlist_result->fetch_assoc()) {
                    // Convert local path to web-accessible URL
                    $local_path = $row['image'];
                    $web_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $local_path);
                ?>
                    <div class="product">
                        <img src="<?php echo $web_path; ?>" alt="Product Image">
                        <h2><?php echo $row['name']; ?></h2>
                        <p>
                            <s>$<?php echo $row['old_price']; ?></s>
                            <strong>$<?php echo $row['new_price']; ?></strong>
                        </p>
                        <p><?php echo $row['details']; ?></p>
                        <!-- Remove from Wishlist Form -->
                        <form method="post" action="index.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="remove_from_wishlist">Remove from Wishlist</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </section>

        <!--=============== NEWSLETTER ===============-->
        <section class="newsletter section">
            <div class="newsletter__container container grid">
                <h3 class="newsletter__title flex">
                    <img src="icon-email.svg" alt="" class="newsletter__icon" />
                    Sign in to Newsletter
                </h3>
                <p class="newsletter__description">
                    ...and receive $25 coupon for first shopping.
                </p>
                <form action="" class="newsletter__form">
                    <input type="text" placeholder="Enter Your Email" class="newsletter__input" />
                    <button type="submit" class="newsletter__btn">Subscribe</button>
                </form>
            </div>
        </section>
    </main>

    <!--=============== FOOTER ===============-->
    <footer class="footer container">
        <div class="footer__container grid">
            <div class="footer__content">
                <a href="index.php" class="footer__logo">
                    <img src="logo.svg" alt="" class="footer__logo-img" />
                </a>
                <h4 class="footer__subtitle">Contact</h4>
                <p class="footer__description">
                    <span>Address:</span> 13 Tlemcen Road, Street 32, Beb-Wahren
                </p>
                <p class="footer__description">
                    <span>Phone:</span> +01 2222 365 /(+91) 01 2345 6789
                </p>
                <p class="footer__description">
                    <span>Hours:</span> 10:00 - 18:00, Mon - Sat
                </p>
                <div class="footer__social">
                    <h4 class="footer__subtitle">Follow Me</h4>
                    <div class="footer__links flex">
                        <a href="#">
                            <img src="icon-facebook.svg" alt="" class="footer__social-icon" />
                        </a>
                        <a href="#">
                            <img src="icon-twitter.svg" alt="" class="footer__social-icon" />
                        </a>
                        <a href="#">
                            <img src="icon-instagram.svg" alt="" class="footer__social-icon" />
                        </a>
                        <a href="#">
                            <img src="icon-pinterest.svg" alt="" class="footer__social-icon" />
                        </a>
                        <a href="#">
                            <img src="icon-youtube.svg" alt="" class="footer__social-icon" />
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer__content">
                <h3 class="footer__title">Address</h3>
                <ul class="footer__links">
                    <li><a href="#" class="footer__link">About Us</a></li>
                    <li><a href="#" class="footer__link">Delivery Information</a></li>
                    <li><a href="#" class="footer__link">Privacy Policy</a></li>
                    <li><a href="#" class="footer__link">Terms & Conditions</a></li>
                </ul>
                <ul>
                    <li><a href="manage_products.php">Manage Products</a></li>
                    <li><a href="manage_brands.php">Manage Brands</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>