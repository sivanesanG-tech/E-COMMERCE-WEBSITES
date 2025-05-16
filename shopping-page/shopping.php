<?php
session_start();

// Initialize cart and wishlist if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database with optional brand name filter
$brand_filter = '';
if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $brand_name = $conn->real_escape_string($_GET['brand']);
    $brand_filter = "WHERE brand = '$brand_name'";
}
$query = "SELECT products.*, brands.name AS brand_name FROM products LEFT JOIN brands ON products.brand_name = brands.id";
$result = $conn->query($query);

// Handle adding a product to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['product_quantity'];

    // Check if the product is already in the cart
    $product_exists = false;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id'] == $product_id) {
            $product_exists = true;
            break;
        }
    }

    // If the product is not in the cart, add it
    if (!$product_exists) {
        $_SESSION['cart'][] = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image,
            'quantity' => $product_quantity
        ];
        $_SESSION['message'] = "Product added to cart successfully!";
    } else {
        $_SESSION['message'] = "Product is already in the cart!";
    }

    header('Location: shopping.php');
    exit();
}

// Handle adding a product to the wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_wishlist'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];

    // Check if the product is already in the wishlist
    $product_exists = false;
    foreach ($_SESSION['wishlist'] as $item) {
        if ($item['id'] == $product_id) {
            $product_exists = true;
            break;
        }
    }

    // If the product is not in the wishlist, add it
    if (!$product_exists) {
        $_SESSION['wishlist'][] = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image
        ];
        $_SESSION['message'] = "Product added to wishlist successfully!";
    } else {
        $_SESSION['message'] = "Product is already in the wishlist!";
    }

    header('Location: shopping.php');
    exit();
}

// Handle successful order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_success'])) {
    $cart = $_SESSION['cart'];

    foreach ($cart as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];

        // Update the stock in the database
        $query = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $quantity, $product_id, $quantity);
        $stmt->execute();

        // Check if stock update was successful
        if ($stmt->affected_rows === 0) {
            $_SESSION['message'] = "Failed to update stock for product ID: $product_id. Insufficient stock.";
        }
        $stmt->close();
    }

    // Clear the cart after successful order
    $_SESSION['cart'] = [];
    $_SESSION['message'] = "Order placed successfully!";
    header('Location: shopping.php');
    exit();
}

// Example product list with stock (replace with database or other source in production)
$products = [
    ['id' => 1, 'name' => 'Product A', 'price' => 100, 'image' => 'C:/uploads/product_a.jpg', 'stock' => $_SESSION['product_stock'][1] ?? 10],
    ['id' => 2, 'name' => 'Product B', 'price' => 200, 'image' => 'C:/uploads/product_b.jpg', 'stock' => $_SESSION['product_stock'][2] ?? 5],
    // ...other products...
];

// Fetch updated products from the database
$query = "SELECT products.*, brands.name AS brand_name FROM products LEFT JOIN brands ON products.brand_name = brands.id";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!--=============== FLATICON ===============-->
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.0.0/uicons-regular-straight/css/uicons-regular-straight.css" />

    <!--=============== SWIPER CSS ===============-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!--=============== EMBEDDED CSS ===============-->
    <style>
      /*=============== GOOGLE FONTS ===============*/
        @import url("https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Spartan:wght@400;500;600;700&display=swap");

        /*=============== VARIABLES CSS ===============*/
        :root {
          --header-height: 4rem;

          /*========== Colors ==========*/
          --first-color: hsl(184, 100%, 47%);
          --first-color-alt: hsl(129, 44%, 94%);
          --second-color: hsl(34, 94%, 87%);
          --title-color: hsl(0, 0%, 13%);
          --text-color: hsl(154, 13%, 32%);
          --text-color-light: hsl(60, 1%, 56%);
          --body-color: hsl(0, 19%, 93%);
          --container-color: hsl(0, 0%, 93%);
          --border-color: hsl(129, 36%, 85%);
          --border-color-alt: hsl(113, 15%, 90%);

          /*========== Font and typography ==========*/
          --body-font: "Lato", sans-serif;
          --second-font: "League Spartan" sans-serif;
          --big-font-size: 3.5rem;
          --h1-font-size: 2.75rem;
          --h2-font-size: 2rem;
          --h3-font-size: 1.75rem;
          --h4-font-size: 1.375rem;
          --large-font-size: 1.125rem;
          --normal-font-size: 1rem;
          --small-font-size: 0.875rem;
          --smaller-font-size: 0.75rem;
          --tiny-font-size: 0.6875rem;

          /*========== Font weight ==========*/
          --weight-400: 400;
          --weight-500: 500;
          --weight-600: 600;
          --weight-700: 700;

          /*========== Transition ==========*/
          --transition: cubic-bezier(0, 0, 0.05, 1);
        }

        /* Responsive typography */
        @media screen and (max-width: 1200px) {
          :root {
            --big-font-size: 2.25rem;
            --h1-font-size: 2rem;
            --h2-font-size: 1.375rem;
            --h3-font-size: 1.25rem;
            --h4-font-size: 1.125rem;
            --large-font-size: 1rem;
            --normal-font-size: 0.9375rem;
            --small-font-size: 0.8125rem;
            --smaller-font-size: 0.6875rem;
            --tiny-font-size: 0.625rem;
          }
        }

        /*=============== BASE ===============*/
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        html {
          scroll-behavior: smooth;
        }

        input,
        textarea,
        body {
          color: var(--text-color);
          font-family: var(--body-font);
          font-size: var(--normal-font-size);
          font-weight: var(--weight-400);
        }

        body {
          background-color: var(--body-color);
        }

        h1,
        h2,
        h3,
        h4 {
          font-family: var(--second-font);
          color: var(--title-color);
          font-weight: var(--weight-600);
        }

        ul {
          list-style: none;
        }

        a {
          text-decoration: none;
        }

        p {
          line-height: 1.5rem;
        }

        img {
          max-width: 100%;
        }

        button,
        textarea,
        input {
          background-color: transparent;
          border: none;
          outline: none;
        }

        table {
          width: 100%;
          border-collapse: collapse;
        }

        /*=============== REUSABLE CSS CLASSES ===============*/
        .container {
          max-width: 1320px;
          margin-inline: auto;
          padding-inline: 0.75rem;
        }

        .grid {
          display: grid;
          gap: 1.5rem;
        }

        .section {
          padding-block: 2rem;
        }

        .section--lg {
          padding-block: 4rem;
        }

        .section__title {
          font-size: var(--h4-font-size);
          margin-bottom: 1.5rem;
        }

        .section__title span {
          color: var(--first-color);
        }

        .form__input {
          border: 1px solid var(--border-color-alt);
          padding-inline: 1rem;
          height: 45px;
          font-size: var(--small-font-size);
          border-radius: 0.25rem;
          -webkit-border-radius: 0.25rem;
          -moz-border-radius: 0.25rem;
          -ms-border-radius: 0.25rem;
          -o-border-radius: 0.25rem;
        }

        .new__price {
          color: var(--first-color);
          font-weight: var(--weight-600);
        }

        .old__price {
          color: var(--text-color-light);
          font-size: var(--small-font-size);
          text-decoration: line-through;
        }

        .form {
          row-gap: 1rem;
        }

        .form__group {
          grid-template-columns: repeat(2, 1fr);
          gap: 1rem;
        }

        .textarea {
          height: 200px;
          padding-block: 1rem;
          resize: none;
        }

        /*=============== HEADER & NAV ===============*/
        .header__top {
          background-color: var(--first-color-alt);
          border-bottom: 1px solid var(--first-color);
          padding-block: 0.875rem;
        }

        .header__container {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .header__contact span:first-child {
          margin-right: 2rem;
        }

        .header__contact span,
        .header__alert-news,
        .header__alert-top-action {
          font-size: var(--small-font-size);
        }

        .header__alert-news {
          color: var(--text-color-light);
          font-weight: var(--weight-600);
        }

        .header__top-action {
          color: var(--text-color);
        }

        .nav,
        .nav__menu,
        .nav__list,
        .header__user-actions {
          display: flex;
          align-items: center;
        }

        .nav {
          height: calc(var(--header-height) + 2.5rem);
          justify-content: space-between;
          column-gap: 1rem;
        }

        .nav__logo-img {
          width: 120px;
        }

        .nav__menu {
          flex-grow: 1;
          margin-left: 2.5rem;
        }

        .nav__list {
          column-gap: 2.5rem;
          margin-right: auto;
        }

        .nav__link {
          color: var(--title-color);
          font-weight: var(--weight-700);
          transform: all 0.3s var(--transition);
          -webkit-transform: all 0.3s var(--transition);
          -moz-transform: all 0.3s var(--transition);
          -ms-transform: all 0.3s var(--transition);
          -o-transform: all 0.3s var(--transition);
        }

        .header__search {
          width: 340px;
          position: relative;
        }

        .header__search .form__input {
          width: 100%;
        }

        .search__btn {
          position: absolute;
          top: 24%;
          right: 1.25rem;
          cursor: pointer;
        }

        .header__user-actions {
          column-gap: 1.25rem;
        }

        .header__action-btn {
          position: relative;
        }

        .header__action-btn img {
          width: 24px;
        }

        .header__action-btn span.count {
          position: absolute;
          top: -0.625rem;
          right: -0.625rem;
          background-color: var(--first-color);
          color: var(--body-color);
          height: 18px;
          width: 18px;
          text-align: center;
          font-size: var(--tiny-font-size);
          line-height: 18px;
          border-radius: 50%;
          -webkit-border-radius: 50%;
          -moz-border-radius: 50%;
          -ms-border-radius: 50%;
          -o-border-radius: 50%;
        }

        .nav__menu-top,
        .nav__toggle {
          display: none;
        }

        /* Active link */
        .active-link,
        .nav__link:hover {
          color: var(--first-color);
        }

        /*=============== HOME ===============*/
        .home__container {
          grid-template-columns: 5fr 7fr;
          align-items: center;
        }

        .home__subtitle,
        .home__description {
          font-size: var(--large-font-size);
        }

        .home__subtitle {
          font-family: var(--second-font);
          font-weight: var(--weight-600);
          margin-bottom: 1rem;
          display: block;
        }

        .home__title {
          font-size: var(--h1-font-size);
          font-weight: var(--weight-700);
          line-height: 1.4;
        }

        .home__title span {
          color: var(--first-color);
          font-size: var(--big-font-size);
        }

        .home__description {
          margin-block: 0.5rem 2rem;
        }

        .home__img {
          justify-self: flex-end;
        }

        /*=============== BUTTONS ===============*/
        .btn {
          display: inline-block;
          background-color: var(--first-color);
          border: 2px solid var(--first-color);
          color: var(--body-color);
          padding-inline: 1.75rem;
          /* padding: 0.75rem 1.75rem; */
          height: 49px;
          line-height: 43px;
          font-family: var(--second-font);
          font-size: var(--small-font-size);
          font-weight: var(--weight-700);
          border-radius: 0.25rem;
          -webkit-border-radius: 0.25rem;
          -moz-border-radius: 0.25rem;
          -ms-border-radius: 0.25rem;
          -o-border-radius: 0.25rem;
          transition: all 0.4s var(--transition);
          -webkit-transition: all 0.4s var(--transition);
          -moz-transition: all 0.4s var(--transition);
          -ms-transition: all 0.4s var(--transition);
          -o-transition: all 0.4s var(--transition);
        }

        .btn:hover {
          background-color: transparent;
          color: var(--first-color);
        }

        .btn--md,
        .btn--sm {
          font-family: var(--body-font);
        }

        .btn--md {
          height: 45px;
          line-height: 40px;
        }

        .btn--sm {
          height: 40px;
          line-height: 35px;
        }

        /*=============== PRODUCTS ===============*/
        .tab__btns {
          display: flex;
          flex-wrap: wrap;
          gap: 0.75rem;
          margin-bottom: 2rem;
        }

        .tab__btn {
          background-color: var(--container-color);
          color: var(--title-color);
          padding: 1rem 1.25rem 0.875rem;
          font-family: var(--second-font);
          font-size: var(--small-font-size);
          font-weight: var(--weight-600);
          cursor: pointer;
          border-radius: 0.25rem;
          -webkit-border-radius: 0.25rem;
          -moz-border-radius: 0.25rem;
          -ms-border-radius: 0.25rem;
          -o-border-radius: 0.25rem;
        }

        .products__container {
          grid-template-columns: repeat(4, 1fr);
        }

        .product__item {
          border: 1px solid var(--border-color);
          border-radius: 1.5rem;
          transition: all 0.2s var(--transition);
          -webkit-border-radius: 1.5rem;
          -moz-border-radius: 1.5rem;
          -ms-border-radius: 1.5rem;
          -o-border-radius: 1.5rem;
          -webkit-transition: all 0.2s var(--transition);
          -moz-transition: all 0.2s var(--transition);
          -ms-transition: all 0.2s var(--transition);
          -o-transition: all 0.2s var(--transition);
        }

        .product__banner {
          padding: 0.625rem 0.75rem 0.75rem;
        }

        .product__banner,
        .product__images {
          position: relative;
        }

        .product__images {
          display: block;
          overflow: hidden;
        }

        .product__img {
          vertical-align: middle;
          transition: all 1.5s var(--transition);
          -webkit-transition: all 1.5s var(--transition);
          -moz-transition: all 1.5s var(--transition);
          -ms-transition: all 1.5s var(--transition);
          -o-transition: all 1.5s var(--transition);
        }

        .product__item:hover .product__img {
          transform: scale(1.1);
          -webkit-transform: scale(1.1);
          -moz-transform: scale(1.1);
          -ms-transform: scale(1.1);
          -o-transform: scale(1.1);
        }

        .product__img.hover {
          position: absolute;
          top: 0;
          left: 0;
          display: none; /* Hide the second image */
        }

        .product__item:hover .product__img.default {
          opacity: 1; /* Ensure the default image remains visible */
        }

        .product__item:active {
          opacity: 1; /* Prevent invisibility on click */
          transform: none; /* Prevent any transformation on click */
        }

        .product__actions {
          position: absolute;
          top: 50%;
          left: 50%;
          display: flex;
          column-gap: 0.5rem;
          transform: translate(-50%, -50%);
          transition: all 0.2s var(--transition);
          -webkit-transform: translate(-50%, -50%);
          -moz-transform: translate(-50%, -50%);
          -ms-transform: translate(-50%, -50%);
          -o-transform: translate(-50%, -50%);
          -webkit-transition: all 0.2s var(--transition);
          -moz-transition: all 0.2s var(--transition);
          -ms-transition: all 0.2s var(--transition);
          -o-transition: all 0.2s var(--transition);
        }

        .action__btn {
          position: relative;
          width: 40px;
          height: 40px;
          line-height: 42px;
          text-align: center;
          background-color: var(--first-color-alt);
          border: 1px solid var(--border-color);
          color: var(--text-color);
          font-size: var(--small-font-size);
          border-radius: 50%;
          -webkit-border-radius: 50%;
          -moz-border-radius: 50%;
          -ms-border-radius: 50%;
          -o-border-radius: 50%;
        }

        .action__btn::before,
        .action__btn::after {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .action__btn:hover::before,
        .action__btn:hover::after {
            opacity: 1;
            visibility: visible;
        }

        .action__btn::before {
          position: absolute;
          left: 50%;
          transform: translateX(-50%);
          content: "";
          top: -2px;
          border: 0.5rem solid transparent;
          border-top-color: var(--first-color);
        }

        .action__btn::after {
          position: absolute;
          left: 50%;
          transform: translateX(-50%);
          content: attr(aria-label);
          bottom: 100%;
          background-color: var(--first-color);
          color: var(--body-color);
          font-size: var(--tiny-font-size);
          white-space: nowrap;
          padding-inline: 0.625rem;
          line-height: 2.58;
          border-radius: 0.25rem;
          -webkit-border-radius: 0.25rem;
          -moz-border-radius: 0.25rem;
          -ms-border-radius: 0.25rem;
          -o-border-radius: 0.25rem;
          opacity: 0;
          visibility: hidden;
          transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .action__btn:hover::after {
          opacity: 1;
          visibility: visible;
        }

        .product__badge {
          position: absolute;
          left: 1.25rem;
          top: 1.25rem;
          background-color: var(--first-color);
          color: var(--body-color);
          padding: 0.25rem 0.625rem;
          font-size: var(--tiny-font-size);
          border-radius: 2.5rem;
          -webkit-border-radius: 2.5rem;
          -moz-border-radius: 2.5rem;
          -ms-border-radius: 2.5rem;
          -o-border-radius: 2.5rem;
        }

        .product__badge.light-pink {
          background-color: hsl(341, 100%, 73%);
        }

        .product__badge.light-green {
          background-color: hsl(155, 20%, 67%);
        }

        .product__badge.light-orange {
          background-color: hsl(24, 100%, 73%);
        }

        .product__badge.light-blue {
          background-color: hsl(202, 53%, 76%);
        }

        .product__content {
          padding: 0 1.25rem 1.125rem;
          position: relative;
        }

        .product__category {
          color: var(--text-color-light);
          font-size: var(--smaller-font-size);
        }

        .product__title {
          font-size: var(--normal-font-size);
          margin-block: 0.75rem 0.5rem;
        }

        .product__rating {
          color: hsl(42, 100%, 50%);
          font-size: var(--smaller-font-size);
          margin-bottom: 0.75rem;
        }

        .product__price .new__price {
          font-size: var(--large-font-size);
        }

        .cart__btn {
          position: absolute;
          bottom: 1.6rem;
          right: 1.25rem;
        }

        /* Active Tab */
        .tab__btn.active-tab {
            color: var(--first-color);
            display: flex;
            align-items: center;
            justify-content: center;
          background-color: var(--second-color);
        }

        .tab__item:not(.active-tab) {
          display: none;
        }

        /* Product Hover */
        .product__img.hover,
        .product__actions,
        .action__btn::before,
        .action__btn::after,
        .product__item:hover .product__img.default {
          opacity: 1; /* Ensure visibility */
        }

        /* Hide product actions by default */
        .product__actions {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        /* Show product actions on hover */
        .product__item:hover .product__actions {
        opacity: 1; 
       visibility: visible;
       }

        .product__item:hover {
          box-shadow: 0 0 10px hsl(0, 0%, 0%, 0.1);
        }

        .action__btn:hover::before,
        .action__btn:hover::after {
          transform: translateX(-50%) translateY(-0.5rem);
          transition: all 0.3s cubic-bezier(0.71, 1.7, 0.77, 1.24);
          -webkit-transition: all 0.3s cubic-bezier(0.71, 1.7, 0.77, 1.24);
          -moz-transition: all 0.3s cubic-bezier(0.71, 1.7, 0.77, 1.24);
          -ms-transition: all 0.3s cubic-bezier(0.71, 1.7, 0.77, 1.24);
          -o-transition: all 0.3s cubic-bezier(0.71, 1.7, 0.77, 1.24);
          -webkit-transform: translateX(-50%) translateY(-0.5rem);
          -moz-transform: translateX(-50%) translateY(-0.5rem);
          -ms-transform: translateX(-50%) translateY(-0.5rem);
          -o-transform: translateX(-50%) translateY(-0.5rem);
        }

        .action__btn:hover {
          background-color: var(--first-color);
          border-color: var(--first-color);
          color: var(--body-color);
        }

        /*=============== NEWSLETTER ===============*/
.newsletter {
  background-color: hsl(166, 23%, 74%);
}

.home__newsletter {
  margin-top: 2rem;
}

.newsletter__container {
  grid-template-columns: repeat(2, 3.5fr) 5fr;
  align-items: center;
  column-gap: 3rem;
}

.newsletter__title {
  column-gap: 1rem;
  font-size: var(--large-font-size);
}

.newsletter__icon {
  width: 40px;
}

.newsletter__description {
  color: var(--title-color);
  font-family: var(--second-font);
  font-size: var(--small-font-size);
  font-weight: var(--weight-600);
  text-align: center;
}

.newsletter__form {
  display: flex;
}

.newsletter__input,
.newsletter__btn {
  font-size: var(--small-font-size);
}

.newsletter__input {
  background-color: var(--body-color);
  padding-inline: 1.23rem;
  width: 100%;
  height: 48px;
  border-radius: 0.25rem 0 0 0.25rem;
  -webkit-border-radius: 0.25rem 0 0 0.25rem;
  -moz-border-radius: 0.25rem 0 0 0.25rem;
  -ms-border-radius: 0.25rem 0 0 0.25rem;
  -o-border-radius: 0.25rem 0 0 0.25rem;
}

.newsletter__btn {
  background-color: var(--title-color);
  color: var(--body-color);
  padding-inline: 2rem;
  font-family: var(--second-font);
  font-weight: 500;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.3s var(--transition);
  border-radius: 0 0.25rem 0.25rem 0;
  -webkit-border-radius: 0 0.25rem 0.25rem 0;
  -moz-border-radius: 0 0.25rem 0.25rem 0;
  -ms-border-radius: 0 0.25rem 0.25rem 0;
  -o-border-radius: 0 0.25rem 0.25rem 0;
  -webkit-transition: all 0.3s var(--transition);
  -moz-transition: all 0.3s var(--transition);
  -ms-transition: all 0.3s var(--transition);
  -o-transition: all 0.3s var(--transition);
}

.newsletter__btn:hover {
  background-color: var(--first-color);
}

/*=============== FOOTER ===============*/
.footer__container {
  grid-template-columns: 4.5fr repeat(2, 2fr) 3.5fr;
  padding-block: 2.5rem;
}

.footer__logo-img {
  width: 120px;
}

.footer__subtitle {
  color: var(--text-color-light);
  font-size: var(--small-font-size);
  margin-block: 1.25rem 0.625rem;
}

.footer__description {
  margin-bottom: 0.25rem;
}

.footer__description span {
  font-weight: var(--weight-600);
}

.footer__social .footer__subtitle {
  margin-top: 1.875rem;
}

.footer__social-links {
  column-gap: 0.25rem;
}

.footer__social-icon {
  width: 20px;
  opacity: 0.7;
}

.footer__title {
  font-size: var(--large-font-size);
  margin-block: 1rem 1.25rem;
}

.footer__link {
  color: var(--title-color);
  font-size: var(--small-font-size);
  margin-bottom: 1rem;
  display: block;
  transition: all 0.3s var(--transition);
  -webkit-transition: all 0.3s var(--transition);
  -moz-transition: all 0.3s var(--transition);
  -ms-transition: all 0.3s var(--transition);
  -o-transition: all 0.3s var(--transition);
}

.footer__link:hover {
  color: var(--first-color);
  margin-left: 0.25rem;
}

.footer__bottom {
  display: flex;
  justify-content: space-between;
  padding-block: 1.25rem;
  border-top: 1px solid var(--border-color-alt);
}

.copyright,
.designer {
  color: var(--text-color-light);
  font-size: var(--small-font-size);
}

/*=============== BREADCRUMBS ===============*/
.breadcrumb {
  background-color: var(--container-color);
  padding-block: 1.5rem;
}

.breadcrumb__list {
  column-gap: 0.75rem;
}

.breadcrumb__link {
  color: var(--text-color);
  font-size: var(--small-font-size);
}
   
        /* Add styles for the message container */
        #message-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }

        #message {
            background-color: #4CAF50; /* Green background */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>

    <title>Ecommerce Website</title>
</head>
<body>
    <!-- Message Container -->
    <div id="message-container">
        <div id="message"></div>
    </div>
    <!-- Quick View, Wishlist, Add to Cart, Compare -->
    <div class="product__actions">
      <a href="#" class="action__btn" aria-label="Quick View">
        <i class="fi fi-rs-eye"></i>
      </a>
      <form method="post" action="shopping.php" style="display:inline;">
        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
        <input type="hidden" name="product_price" value="<?php echo $row['new_price']; ?>">
        <input type="hidden" name="product_image" value="<?php echo $row['image']; ?>">
        <button type="submit" name="add_to_wishlist" class="action__btn" aria-label="Add to Wishlist">
          <i class="fi fi-rs-heart"></i>
        </button>
      </form>
      <a href="#" class="action__btn" aria-label="Compare">
        <i class="fi fi-rs-shuffle"></i>
      </a>
    </div>
    <!-- Pass the message to JavaScript -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            var message = "<?php echo $_SESSION['message']; ?>";
            var messageContainer = document.getElementById('message-container');
            var messageElement = document.getElementById('message');

            // Display the message
            messageElement.textContent = message;
            messageContainer.style.display = 'block';

            // Hide the message after 3 seconds
            setTimeout(function() {
                messageContainer.style.display = 'none';
            }, 3000);
        </script>
        <?php unset($_SESSION['message']); // Clear the message after displaying ?>
    <?php endif; ?>

    <!--=============== HEADER ===============-->
    <header class="header">
        <div class="header__top">
            <div class="header__container container">
                <div class="header__contact">
                    <span>(+01) - 2345 - 6789</span>
                    <span>Our location</span>
                </div>
                <p class="header__alert-news">Super Values Deals - Save more coupons</p>
                <?php if (isset($_SESSION['username'])): ?>
                    <div class="header__user-info">
                       <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>

                        <a href="logout.php" class="header__top-action">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="header__top-action">Log In / Sign Up</a>
                <?php endif; ?>
            </div>
        </div>

        <nav class="nav container">
        <a href="index.html" class="nav__logo" style="margin-top: 20px; display: inline-block;">
          <img
            class="nav__logo-img"
            src="_Blue & Black Simple Company Logo.png"
            alt="website logo"
          />
        </a>
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item"><a href="shop.html" class="nav__link">Shop</a></li>
                    <li class="nav__item"><a href="accounts.php" class="nav__link">My Account</a></li>
                    <li class="nav__item"><a href="compare.html" class="nav__link">Compare</a></li>
                    <li class="nav__item"><a href="categories.php" class="nav__link">Categories</a></li>
                </ul>
                <div class="header__search">
                    <form method="get" action="search.php">
                        <input type="text" name="query" placeholder="Search For Items..." class="form__input" required />
                        <button type="submit" class="search__btn">
                            <i class="fi fi-rs-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="header__user-actions">
                <a href="wishlist.php" class="header__action-btn" title="Wishlist">
                    <img src="icon-heart.svg" alt="" />
                    <span class="count"><?php echo count($_SESSION['wishlist']); ?></span>
                </a>
                <a href="cart.php" class="header__action-btn" title="Cart">
                    <img src="icon-cart.svg" alt="" />
                    <span class="count"><?php echo count($_SESSION['cart']); ?></span>
                </a>
                <a href="payment.php" class="header__action-btn" title="Payment">
                    <img src="icon-payment.svg" alt="" />
                </a>
            </div>
        </nav>
    </header>

    <!--=============== MAIN ===============-->
    <main class="main">
        <!--=============== BREADCRUMB ===============-->
        <section class="breadcrumb">
            <ul class="breadcrumb__list flex container">
                <li><a href="index.html" class="breadcrumb__link">Home</a></li>
                <li><span class="breadcrumb__link"></span>></li>
                <li><span class="breadcrumb__link">Shop</span></li>
            </ul>
        </section>

        <!--=============== PRODUCTS ===============-->
        <section class="products container section--lg">
            <form method="get" action="shopping.php">
                <label for="brand">Filter by Brand:</label>
                <select id="brand" name="brand">
                    <option value="">All Brands</option>
                    <?php
                    $brands = $conn->query("SELECT * FROM brands");
                    while ($brand = $brands->fetch_assoc()) {
                        echo '<option value="' . $brand['id'] . '">' . $brand['name'] . '</option>';
                    }
                    ?>
                </select>
                <button type="submit">Filter</button>
            </form>
            <div class="products__container grid">
                <?php
                // Loop through each product
                while ($row = $result->fetch_assoc()) {
                    // Convert local path to web-accessible URL
                    $local_path = $row['image']; // e.g., "C:/uploads/funco.jpg"
                    $web_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $local_path);

                    // Calculate discount percentage
                    $discount = round((($row['old_price'] - $row['new_price']) / $row['old_price']) * 100);
                ?>
                    <div class="product__item">
                        <div class="product__banner">
                            <a href="details.html" class="product__images">
                                <img src="<?php echo $web_path; ?>" alt="" class="product__img default" />
                            </a>
                            <div class="product__actions">
                                <a href="#" class="action__btn" aria-label="Quick View">
                                    <i class="fi fi-rs-eye"></i>
                                </a>
                                <!-- Wishlist Button -->
                                <form method="post" action="shopping.php" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $row['new_price']; ?>">
                                    <input type="hidden" name="product_image" value="<?php echo $row['image']; ?>">
                                    <button type="submit" name="add_to_wishlist" class="action__btn" aria-label="Add to Wishlist">
                                        <i class="fi fi-rs-heart"></i>
                                    </button>
                                </form>
                                <a href="#" class="action__btn" aria-label="Compare">
                                    <i class="fi fi-rs-shuffle" aria-label="Compare"></i>
                                </a>
                            </div>
                            <div class="product__badge light-pink">Hot</div>
                        </div>
                        <div class="product__content">
                            <span class="product__category">Clothing</span>
                            <a href="details.html">
                                <h3 class="product__title"><?php echo $row['name']; ?></h3>
                            </a>
                            <div class="product__rating">
                                <i class="fi fi-rs-star"></i>
                                <i class="fi fi-rs-star"></i>
                                <i class="fi fi-rs-star"></i>
                                <i class="fi fi-rs-star"></i>
                                <i class="fi fi-rs-star"></i>
                            </div>
                            <div class="product__price flex">
                                <span class="new__price">₹<?php echo $row['new_price']; ?></span>
                                <span class="old__price">₹<?php echo $row['old_price']; ?></span>
                            </div>
                            <p>Category: <?php echo $row['category']; ?></p>
                            <p>Stock: <?php echo $row['stock']; ?> available</p>
                            <!-- Add to Cart Form -->
                            <form method="post" action="shopping.php">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                                <input type="hidden" name="product_price" value="<?php echo $row['new_price']; ?>">
                                <input type="hidden" name="product_image" value="<?php echo $row['image']; ?>">
                                <input type="hidden" name="product_quantity" value="1">
                                <?php if ($row['stock'] > 0): ?>
                                    <button type="submit" name="add_to_cart" class="action__btn cart__btn" aria-label="Add To Cart">
                                        <i class="fi fi-rs-shopping-bag-add"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="action__btn cart__btn" aria-label="Out of Stock" disabled style="color: red;">
                                        Out of Stock
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

 <!--=============== NEWSLETTER ===============-->
      <section class="newsletter section home__newsletter">
        <div class="newsletter__container container grid">
          <h3 class="newsletter__title flex">
            <img src="icon-email.svg" alt="" class="newsletter__icon" />
            Sign in to Newsletter
          </h3>
          <p class="newsletter__description">
            ...and receive 25% coupon for first shopping.
          </p>
          <form action="shopping-page/login.php" method="POST" class="newsletter__form">
            <input type="email" name="email" placeholder="Enter Your Email" class="newsletter__input" required />
            <button type="submit" name="subscribe" class="newsletter__btn">Subscribe</button>
          </form>
        </div>
      </section>
    </main>

    <!--=============== FOOTER ===============-->
    <footer class="footer container">
      <div class="footer__container grid">
        <div class="footer__content">
          <a href="index.html" class="footer__logo" style="margin-top: 2px; display: inline-block;">
            <img src="_Blue & Black Simple Company Logo.png" alt="" class="footer__logo-img" />
          </a>
          <h4 class="footer__subtitle">Contact</h4>
          <p class="footer__description">
            <span>Address:</span> 153,Gangayamman Kovil Street,Thandlam,Thiruporur
          </p>
          <p class="footer__description">
            <span>Phone:</span>+91-6374609706
          </p>
          <p class="footer__description">
            <span>Hours:</span> 10:00 - 18:00, Mon - Sat
          </p>
          <div class="footer__social">
            <h4 class="footer__subtitle">Follow Me</h4>
            <div class="footer__links flex">
              <a href="#">
                <img
                  src="icon-facebook.svg"
                  alt=""
                  class="footer__social-icon"
                />
              </a>
              <a href="#">
                <img
                  src="icon-twitter.svg"
                  alt=""
                  class="footer__social-icon"
                />
              </a>
              <a href="#">
                <img
                  src="icon-instagram.svg"
                  alt=""
                  class="footer__social-icon"
                />
              </a>
              <a href="#">
                <img
                  src="icon-pinterest.svg"
                  alt=""
                  class="footer__social-icon"
                />
              </a>
              <a href="#">
                <img
                  src="icon-youtube.svg"
                  alt=""
                  class="footer__social-icon"
                />
              </a>
            </div>
          </div>
        </div>
        <div class="footer__content">
          <h3 class="footer__title"></h3>
          <ul class="footer__links">
            <li><a href="shopping-page\accounts.php" class="footer__link">About Us</a></li>
            <li><a href="privacy-policy.html" class="footer__link">Privacy Policy</a></li>
            <li><a href="terms-and-conditions.html" class="footer__link">Terms & Conditions</a></li>
          </ul>
        </div>
        <div class="footer__content">
          <h3 class="footer__title"></h3>
          <ul class="footer__links">
            <li><a href="shopping-page\login.php" class="footer__link">Sign In</a></li>
                <li><a href="contact-us.html" class="footer__link">Contact Us</a></li>
            <li><a href="support-us.php" class="footer__link">Support Center</a></li>
          </ul>
        </div>
        <div class="footer__content">
          <h3 class="footer__title">Secured Payed Gateways</h3>
          <img
            src="payment-method.png"
            alt=""
            class="payment__img"
          />
        </div>
      </div>
      <div class="footer__bottom">
        <p class="copyright">&copy; 2025 SKYFALL. All right reserved</p>
        <span class="designer">Designer by Crypticalcoder</span>
      </div>
    </footer>
    <!--=============== SWIPER JS ===============-->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!--=============== MAIN JS ===============-->
    <script src="main.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
<?php if (isset($address['fullname'])): ?>
    <p><strong>Full Name:</strong> <?php echo $address['fullname']; ?></p>
<?php else: ?>
    <p><strong>Full Name:</strong> Not provided</p>
<?php endif; ?>