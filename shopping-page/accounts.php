<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'shopping';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$username = $_SESSION['username']; // Ensure updated username is used throughout

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    // Update the specific order status to "Cancelled"
    $update_query = "UPDATE orders SET delivery_status = 'Cancelled' WHERE order_id = ? AND user_name = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("is", $order_id, $username);
    $stmt->execute();
    $stmt->close();

    // Redirect to refresh the page
    header('Location: accounts.php');
    exit();
}

// Handle single product cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_product'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $cancel_reason = $_POST['cancel_reason'];

    // Update the specific product in the order to "Cancelled"
    $update_query = "UPDATE orders SET delivery_status = 'Cancelled', status = 'Cancelled', cancel_reason = ? WHERE order_id = ? AND product_id = ?";
    $stmt = $conn->prepare($update_query);
    if ($stmt) {
        $stmt->bind_param("sii", $cancel_reason, $order_id, $product_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to refresh the page
    header('Location: accounts.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $new_username = $_POST['username']; // New username

    $conn->begin_transaction(); // Start transaction

    try {
        // Update the users table
        $update_user_query = "UPDATE users SET fullname = ?, email = ?, phone = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($update_user_query);
        $stmt->bind_param("ssssi", $fullname, $email, $phone, $new_username, $_SESSION['user_id']);
        $stmt->execute();

        // Update the orders table
        $update_orders_query = "UPDATE orders SET user_name = ? WHERE user_name = ?";
        $stmt = $conn->prepare($update_orders_query);
        $stmt->bind_param("ss", $new_username, $_SESSION['username']);
        $stmt->execute();

        // Update the addresses table
        $update_addresses_query = "UPDATE addresses SET username = ? WHERE username = ?";
        $stmt = $conn->prepare($update_addresses_query);
        $stmt->bind_param("ss", $new_username, $_SESSION['username']);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Update session data
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        $_SESSION['username'] = $new_username; // Update session username

        echo "<div class='message' id='success-message'>Profile updated successfully.</div>";
        echo "<script>
                setTimeout(function() {
                    const message = document.getElementById('success-message');
                    if (message) {
                        message.style.display = 'none';
                    }
                }, 3000);
              </script>";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        echo "<div class='message error'>Error updating profile: " . $e->getMessage() . "</div>";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current password hash from the database
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (!password_verify($current_password, $hashed_password)) {
        echo "<div class='message error'>Current password is incorrect.</div>";
    } elseif ($new_password !== $confirm_password) {
        echo "<div class='message error'>New password and confirm password do not match.</div>";
    } else {
        // Hash the new password and update it in the database
        $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_hashed_password, $_SESSION['user_id']);
        if ($stmt->execute()) {
            echo "<div class='message' id='success-message'>Password changed successfully.</div>";
            echo "<script>
                    setTimeout(function() {
                        const message = document.getElementById('success-message');
                        if (message) {
                            message.style.display = 'none';
                        }
                    }, 3000);
                  </script>";
        } else {
            echo "<div class='message error'>Error changing password: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// Fetch orders for the logged-in user
$query = "SELECT order_id, product_id, product_name, product_image, quantity, size, final_amount, payment_method, created_at, user_name, delivery_status, status, cancel_reason 
          FROM orders 
          WHERE user_name = ? 
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['username']); // Use updated session username
$stmt->execute();
$result = $stmt->get_result();

// Fetch addresses for the logged-in user
$addressQuery = "SELECT * FROM addresses WHERE username = ?";
$addressStmt = $conn->prepare($addressQuery);
$addressStmt->bind_param("s", $_SESSION['username']); // Use updated session username
$addressStmt->execute();
$addressResult = $addressStmt->get_result();

// Handle logout
if (isset($_GET['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: login.php'); // Redirect to the home page
    exit();
}

// Fetch user details from the session
$userDetails = [
    'fullname' => $_SESSION['fullname'] ?? 'Guest',
    'email' => $_SESSION['email'] ?? 'Not Available',
    'phone' => $_SESSION['phone'] ?? 'Not Available',
    'gender' => $_SESSION['gender'] ?? 'Not Available',
];

// Fetch wishlist items from the session
$wishlistItems = $_SESSION['wishlist'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!--=============== FLATICON ===============-->
    <link
      rel="stylesheet"
      href="https://cdn-uicons.flaticon.com/2.0.0/uicons-regular-straight/css/uicons-regular-straight.css"
    />

    <!--=============== SWIPER CSS ===============-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />

    <!--=============== CSS ===============-->
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
  text-align: left; /* Align content to the left */
}

.grid {
  display: grid;
  gap: 1.5rem;
}

.section {
  padding-block: 2rem;
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
  width: 100px;
  height: auto;
  object-fit: contain;
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

/* Removed empty ruleset for .home__img */

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
  border-radius: 1.25rem;
  -webkit-border-radius: 1.25rem;
  -moz-border-radius: 1.25rem;
  -ms-border-radius: 1.25rem;
  -o-border-radius: 1.25rem;
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
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  -webkit-transform: translateX(-50%);
  -moz-transform: translateX(-50%);
  -ms-transform: translateX(-50%);
  -o-transform: translateX(-50%);
}

.action__btn::before {
  content: "";
  top: -2px;
  border: 0.5rem solid transparent;
  border-top-color: var(--first-color);
}

.action__btn::after {
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
  opacity: 0;
}

.product__item:hover {
  box-shadow: 0 0 10px hsl(0, 0%, 0%, 0.1);
}

.product__item:hover .product__img.hover,
.product__item:hover .product__actions,
.action__btn:hover::before,
.action__btn:hover::after {
  opacity: 1;
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



/*=============== NEW ARRIVALS ===============*/
.new__arrivals {
  overflow: hidden;
}

/*=============== SHOWCASE ===============*/
.showcase__container {
  grid-template-columns: repeat(4, 1fr);
}

.showcase__wrapper .section__title {
  font-size: var(--normal-font-size);
  border-bottom: 1px solid var(--border-color-alt);
  padding-bottom: 0.75rem;
  margin-bottom: 2rem;
  position: relative;
}

.showcase__wrapper .section__title::before {
  content: "";
  position: absolute;
  width: 50px;
  height: 2px;
  left: 0;
  bottom: -1.5px;
  background-color: var(--first-color);
}

.showcase__item {
  display: flex;
  align-items: center;
  column-gap: 1.5rem;
}

.showcase__item:not(:last-child) {
  margin-bottom: 1.5rem;
}

.showcase__img {
  width: 86px;
}

.showcase__content {
  width: calc(100% - 110px);
}

.showcase__title {
  font-size: var(--small-font-size);
  font-weight: var(--weight-500);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 0.5rem;
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

/*=============== SHOP ===============*/
.total__products {
  margin-bottom: 2.5rem;
}

.total__products span {
  color: var(--first-color);
  font-weight: var(--weight-600);
}

.pagination {
  display: flex;
  justify-content: center;
  column-gap: 0.625rem;
  margin-top: 2.75rem;
}

.pagination__link {
  display: inline-block;
  width: 34px;
  height: 34px;
  line-height: 34px;
  text-align: center;
  color: var(--text-color);
  font-size: var(--small-font-size);
  font-weight: var(--weight-700);
  border-radius: 0.25rem;
  transition: all 0.15s var(--transition);
  -webkit-border-radius: 0.25rem;
  -moz-border-radius: 0.25rem;
  -ms-border-radius: 0.25rem;
  -o-border-radius: 0.25rem;
  -webkit-transition: all 0.15s var(--transition);
  -moz-transition: all 0.15s var(--transition);
  -ms-transition: all 0.15s var(--transition);
  -o-transition: all 0.15s var(--transition);
}

.pagination__link.active,
.pagination__link:hover {
  background-color: var(--first-color);
  color: var(--body-color);
}

.pagination__link.icon {
  border-top-right-radius: 50%;
  border-bottom-right-radius: 50%;
}

/*=============== DETAILS ===============*/
.details__container {
  grid-template-columns: 5.5fr 6.5fr;
}

.details__img {
  margin-bottom: 0.5rem;
}

.details__small-images {
  grid-template-columns: repeat(4, 1fr);
  column-gap: 0.625rem;
}

.details__small-img {
  cursor: pointer;
}

.details__title {
  font-size: var(--h2-font-size);
}

.details__brand {
  font-size: var(--small-font-size);
  margin-block: 1rem;
}

.details__brand span {
  color: var(--first-color);
}

.details__price {
  border-top: 1px solid var(--border-color-alt);
  border-bottom: 1px solid var(--border-color-alt);
  padding-block: 1rem;
  column-gap: 1rem;
}

.details__price .new__price {
  font-size: var(--h2-font-size);
}
.details__price .old__price {
  font-size: var(--normal-font-size);
  font-weight: var(--weight-500);
}

.short__description {
  margin-block: 1rem 2rem;
}

.list__item,
.meta__list {
  font-size: var(--small-font-size);
  margin-bottom: 0.75rem;
}

.details__color,
.details__size {
  column-gap: 0.75rem;
}

.details__color {
  margin-block: 2rem 1.5rem;
}

.details__size {
  margin-bottom: 2.5rem;
}

.details__color-title,
.details__size-title {
  font-size: var(--small-font-size);
  font-weight: var(--weight-600);
}

.color__list,
.size__list {
  display: flex;
  column-gap: 0.25rem;
}

.color__link {
  display: inline-block;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  -ms-border-radius: 50%;
  -o-border-radius: 50%;
}

.size__link {
  border: 1px solid var(--border-color-alt);
  padding: 0.375rem 0.75rem 0.5rem;
  color: var(--text-color);
  font-size: var(--small-font-size);
}

.size-active {
  background-color: var(--first-color);
  color: var(--body-color);
}

.details__action {
  display: flex;
  column-gap: 0.375rem;
  margin-bottom: 3.25rem;
}

.quantity,
.details__action-btn {
  border: 1px solid var(--border-color-alt);
  font-size: var(--small-font-size);
}

.quantity {
  max-width: 80px;
  padding-block: 0.5rem;
  padding-inline: 1rem 0.5rem;
  border-radius: 0.25rem;
  -webkit-border-radius: 0.25rem;
  -moz-border-radius: 0.25rem;
  -ms-border-radius: 0.25rem;
  -o-border-radius: 0.25rem;
}

.details__action-btn {
  color: var(--text-color);
  line-height: 40px;
  padding-inline: 0.75rem;
}

.details__meta {
  border-top: 1px solid var(--border-color-alt);
  padding-top: 1rem;
}

/*=============== DETAILS INFO & REVIEWS ===============*/
.detail__tabs {
  display: flex;
  column-gap: 1.75rem;
  margin-bottom: 3rem;
}

.detail__tab {
  font-family: var(--second-font);
  font-size: var(--large-font-size);
  font-weight: var(--weight-600);
  cursor: pointer;
}

.detail__tab.active-tab {
  color: var(--first-color);
}

.details__tab-content:not(.active-tab) {
  display: none;
}

.info__table tr th,
.info__table tr td {
  border: 1px solid var(--border-color-alt);
  padding: 0.625rem 1.25rem;
}

.info__table tr th {
  font-weight: var(--weight-500);
  text-align: left;
}
.info__table tr td {
  color: var(--text-color-light);
}

.reviews__container {
  padding-bottom: 3rem;
  row-gap: 1.25rem;
}

.review__single {
  border-bottom: 1px solid var(--border-color-alt);
  padding-bottom: 1.25rem;
  display: flex;
  align-items: center;
  column-gap: 1.5rem;
}

.review__single:last-child {
  padding-bottom: 3rem;
}

.review__img {
  width: 70px;
  margin-bottom: 0.5rem;
  border-radius: 50%;
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  -ms-border-radius: 50%;
  -o-border-radius: 50%;
}

.review__title {
  font-size: var(--tiny-font-size);
}

.review__data {
  width: calc(100% - 94px);
}

.review__rating {
  color: hsl(24, 100%, 50%);
  margin-bottom: 0.25rem;
}

.review__description {
  margin-bottom: 0.5rem;
}

.review__rating,
.review__date {
  font-size: var(--small-font-size);
}

.review__form-title {
  font-size: var(--large-font-size);
  margin-bottom: 1rem;
}

.rate__product {
  margin-bottom: 2rem;
}

/*=============== CART ===============*/
.table__container {
  overflow-x: auto;
}

.table {
  table-layout: fixed;
  margin-bottom: 2rem;
}

.table tbody tr {
  border-top: 1px solid var(--border-color-alt);
}

.table tr:last-child {
  border-bottom: 1px solid var(--border-color-alt);
}

.table thead tr th:nth-child(1),
.table tbody tr td:nth-child(1) {
  width: 216px;
}

.table thead tr th:nth-child(2),
.table tbody tr td:nth-child(2) {
  width: 400px;
}

.table thead tr th:nth-child(3),
.table tbody tr td:nth-child(3) {
  width: 108px;
}

.table thead tr th:nth-child(4),
.table tbody tr td:nth-child(4) {
  width: 220px;
}

.table thead tr th:nth-child(5),
.table tbody tr td:nth-child(5) {
  width: 200px;
}

.table thead tr th:nth-child(6),
.table tbody tr td:nth-child(6) {
  width: 152px;
}

.table__img {
  width: 80px;
}

.table thead tr th,
.table tbody tr td {
  padding: 0.7rem 0.5rem;
  text-align: center;
}

.table__title,
.table__description,
.table__price,
.table__subtotal,
.table__trash,
.table__stock {
  font-size: var(--small-font-size);
}

.table__title,
.table__stock {
  color: var(--first-color);
}

.table__description {
  max-width: 250px;
  margin-inline: auto;
}

.table__trash {
  color: var(--text-color-light);
  cursor: pointer;
}

.table__trash:hover {
  color: red;
}

.cart__actions {
  display: flex;
  justify-content: flex-end;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.divider {
  position: relative;
  text-align: center;
  margin-block: 3rem;
}

.divider::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  border-top: 2px solid var(--border-color-alt);
  z-index: -1;
}

.divider i {
  color: var(--text-color-light);
  background-color: var(--body-color);
  font-size: 1.5rem;
  padding-inline: 1.25rem;
}

.cart__group {
  grid-template-columns: repeat(2, 1fr);
  align-items: flex-start;
}

.cart__shipping .section__title,
.cart__coupon .section__title,
.cart__total .section__title {
  font-size: var(--large-font-size);
  margin-bottom: 1rem;
}

.cart__coupon {
  margin-top: 3rem;
}

.coupon__form .form__group {
  align-items: center;
}

.cart__total {
  border: 1px solid var(--border-color-alt);
  padding: 1.75rem;
  border-radius: 0.25rem;
  -webkit-border-radius: 0.25rem;
  -moz-border-radius: 0.25rem;
  -ms-border-radius: 0.25rem;
  -o-border-radius: 0.25rem;
}

.cart__total-table {
  margin-bottom: 1rem;
}

.cart__total-table tr td {
  border: 1px solid var(--border-color-alt);
  padding: 0.75rem 0.5rem;
  width: 50%;
}

.cart__total-title {
  font-size: var(--small-font-size);
}

.cart__total-price {
  color: var(--first-color);
  font-weight: var(--weight-700);
}

.cart__total .btn {
  display: inline-flex;
  /* width: fit-content; */
}

/*=============== CART OTHERS ===============*/

/*=============== WISHLIST ===============*/

/*=============== CHECKOUT ===============*/
.checkout__container {
  grid-template-columns: repeat(2, 1fr);
}

.checkout__group:nth-child(2) {
  border: 1px solid var(--border-color-alt);
  padding: 2rem;
  border-radius: 0.5rem;
  -webkit-border-radius: 0.5rem;
  -moz-border-radius: 0.5rem;
  -ms-border-radius: 0.5rem;
  -o-border-radius: 0.5rem;
}

.checkout__group .section__title {
  font-size: var(--large-font-size);
}

.checkout__title {
  font-size: var(--small-font-size);
}

.order__table thead tr th,
.order__table tbody tr td {
  border: 1px solid var(--border-color-alt);
  padding: 0.5rem;
  text-align: center;
}

.order__table thead tr th {
  color: var(--title-color);
  font-size: var(--small-font-size);
}

.order__img {
  width: 80px;
}

.table__quantity,
.order__subtitle {
  font-size: var(--small-font-size);
}

.order__grand-total {
  color: var(--first-color);
  font-size: var(--large-font-size);
  font-weight: var(--weight-700);
}

.payment__methods {
  margin-block: 2.5rem 2.75rem;
}

.payment__title {
  margin-bottom: 1.5rem;
}

.payment__option {
  margin-bottom: 1rem;
}

.payment__input {
  accent-color: var(--first-color);
}

.payment__label {
  font-size: var(--small-font-size);
  user-select: none;
}

/*=============== COMPARE ===============*/
.compare__table tr th,
.compare__table tr td {
  padding: 0.5rem;
  border: 1px solid var(--border-color-alt);
}

.compare__table tr th {
  color: var(--text-color-light);
  font-size: var(--small-font-size);
}

.compare__table tr td {
  text-align: center;
}

.compare__colors {
  justify-content: center;
}

.table__weight,
.table__dimension {
  font-size: var(--small-font-size);
}

/*=============== LOGIN & REGISTER ===============*/
.login-register__container {
  grid-template-columns: repeat(2, 1fr);
  align-items: flex-start;
}

.login,
.register {
  border: 1px solid var(--border-color-alt);
  padding: 2rem;
  border-radius: 0.5rem;
  -webkit-border-radius: 0.5rem;
  -moz-border-radius: 0.5rem;
  -ms-border-radius: 0.5rem;
  -o-border-radius: 0.5rem;
}

/*=============== ACCOUNTS ===============*/
.accounts__container {
  grid-template-columns: 4fr 8fr;
}

.account__tabs {
  border: 1px solid var(--border-color-alt);
  border-radius: 0.25rem;
  -webkit-border-radius: 0.25rem;
  -moz-border-radius: 0.25rem;
  -ms-border-radius: 0.25rem;
  -o-border-radius: 0.25rem;
}

.account__tab {
  padding: 1rem 2rem;
  color: var(--title-color);
  font-size: var(--small-font-size);
  display: flex;
  column-gap: 0.625rem;
  cursor: pointer;
  transition: all 0.3s var(--transition);
  -webkit-transition: all 0.3s var(--transition);
  -moz-transition: all 0.3s var(--transition);
  -ms-transition: all 0.3s var(--transition);
  -o-transition: all 0.3s var(--transition);
}

.account__tab.active-tab {
  background-color: var(--first-color);
  color: var(--body-color);
}

.account__tab:not(:last-child) {
  border-bottom: 1px solid var(--border-color-alt);
}

.tab__content:not(.active-tab) {
  display: none;
}

.tab__content {
  border: 1px solid var(--border-color-alt);
}

.tab__header {
  background-color: var(--container-color);
  border-bottom: 1px solid var(--border-color-alt);
  padding: 1rem;
  font-size: var(--small-font-size);
}

.tab__body {
  padding: 1rem;
}

.placed__order-table thead tr th {
  color: var(--title-color);
  text-align: left;
}

.placed__order-table thead tr th,
.placed__order-table tbody tr td {
  border: 1px solid var(--border-color-alt);
  padding: 0.5rem;
  font-size: var(--small-font-size);
}

.view__order,
.edit {
  color: var(--first-color);
}

.address {
  font-style: normal;
  font-size: var(--small-font-size);
  line-height: 1.5;
}

.city {
  margin-bottom: 0.25rem;
}

.edit {
  font-size: var(--small-font-size);
}

/*=============== BREAKPOINTS ===============*/
/* For large devices */
@media screen and (max-width: 1400px) {
  .container {
    max-width: 1140px;
  }

  .products__container,
  .showcase__container {
    grid-template-columns: repeat(3, 1fr);
  }

  .accounts__container {
    grid-template-columns: 3fr 9fr;
  }
}

@media screen and (max-width: 1200px) {
  .container {
    max-width: 960px;
  }

  .header__top {
    display: none;
  }

  .nav {
    height: calc(var(--header-height) + 1.5rem);
  }

  .nav__logo-img {
    width: 116px;
  }

  .nav__menu {
    position: fixed;
    right: -100%;
    top: 0;
    max-width: 400px;
    width: 100%;
    height: 100%;
    padding: 1.25rem 2rem;
    background-color: var(--body-color);
    z-index: 100;
    flex-direction: column;
    align-items: flex-start;
    row-gap: 2rem;
    box-shadow: 0 0 15px hsl(0, 0%, 0%, 0.1);
    transition: right 0.5s ease-in-out;
    -webkit-transition: right 0.5s ease-in-out;
    -moz-transition: right 0.5s ease-in-out;
    -ms-transition: right 0.5s ease-in-out;
    -o-transition: right 0.5s ease-in-out;
  }

  .nav__menu.show-menu {
    right: 0;
  }

  .nav__list {
    order: 1;
    flex-direction: column;
    align-items: flex-start;
    row-gap: 1.5rem;
  }

  .nav__link {
    font-size: var(--large-font-size);
  }

  .header__search .form__input {
    border-color: var(--first-color);
  }

  .nav__menu-top,
  .nav__toggle {
    display: flex;
  }

  .nav__toggle,
  .nav__close {
    cursor: pointer;
  }

  .nav__menu-top {
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 1.25rem;
  }

  .nav__menu-logo img {
    width: 100px;
  }

  .header__action-btn {
    width: 21px;
  }

  .home__container {
    grid-template-columns: 5.5fr 6.5fr;
  }

  .countdown {
    column-gap: 1rem;
  }

  .countdown__period {
    width: 36px;
    height: 36px;
    line-height: 36px;
  }

  .countdown__amount::after {
    right: -25%;
    top: 12%;
  }

  .swiper-button-next,
  .swiper-button-prev {
    top: -28px;
    width: 26px;
    height: 26px;
  }

  .swiper-button-prev {
    right: 36px;
  }

  .account__tab {
    padding: 0.75rem 1rem;
  }

  .checkout__group:nth-child(2) {
    padding: 1.5rem;
  }

  .details__brand {
    margin-block: 0.75rem;
  }

  .details__price {
    padding-block: 0.75rem;
  }

  .short__description {
    margin-bottom: 1.5rem;
  }

  .details__color {
    margin-block: 1.75rem;
  }

  .details__size {
    margin-bottom: 2.25rem;
  }

  .color__link {
    width: 22px;
    height: 22px;
  }

  .size__link {
    padding: 0.375rem 0.625rem;
  }

  .details__action {
    margin-bottom: 2.75rem;
  }
}

/* For medium devices */
@media screen and (max-width: 992px) {
  .container {
    max-width: 776px;
  }

  .home__container,
  .deals__container,
  .checkout__container,
  .newsletter__container,
  .accounts__container,
  .cart__group {
    grid-template-columns: 1fr;
  }

  .cart__group {
    row-gap: 2.75rem;
  }

  .home__img {
    justify-self: center;
  }

  .btn {
    height: 45px;
    line-height: 39px;
  }

  .btn--md {
    height: 42px;
    line-height: 35px;
  }
  .btn--sm {
    height: 38px;
    line-height: 35px;
  }

  .newsletter__description {
    display: none;
  }

  .products__container,
  .showcase__container,
  .footer__container,
  .details__container {
    grid-template-columns: repeat(2, 1fr);
  }

  .login,
  .register {
    padding: 1.25rem;
  }

  .table thead tr th:nth-child(1),
  .table tbody tr td:nth-child(1) {
    width: 140px;
  }

  .table thead tr th:nth-child(2),
  .table tbody tr td:nth-child(2) {
    width: 330px;
  }

  .table thead tr th:nth-child(3),
  .table tbody tr td:nth-child(3) {
    width: 80px;
  }

  .table thead tr th:nth-child(4),
  .table tbody tr td:nth-child(4) {
    width: 220px;
  }

  .table thead tr th:nth-child(5),
  .table tbody tr td:nth-child(5) {
    width: 160px;
  }

  .table thead tr th:nth-child(6),
  .table tbody tr td:nth-child(6) {
    width: 100px;
  }
}

@media screen and (max-width: 768px) {
  .container {
    max-width: 570px;
  }

  .products__container,
  .showcase__container,
  .footer__container,
  .login-register__container,
  .details__container {
    grid-template-columns: 100%;
  }

  .tab__header,
  .tab__body {
    padding: 0.75rem;
  }

  .compare__table tr td {
    display: block;
  }

  .home__img {
    max-width: 300px;
  }
}

/* For small devices */
@media screen and (max-width: 576px) {
  .category__item {
    padding-bottom: 1rem;
  }

  .category__img {
    margin-bottom: 1rem;
  }
  .deals__item,
  .checkout__group:nth-child(2) {
    padding: 1.25rem;
  }

  .pagination {
    column-gap: 0.5rem;
    margin-top: 2.5rem;
  }

  .pagination__link {
    width: 30px;
    height: 30px;
    line-height: 30px;
  }

  .placed__order-table th,
  .order__table tr th {
    display: none;
  }

  .placed__order-table tr td,
  .order__table tr td,
  .info__table tr th,
  .info__table tr td {
    display: block;
  }

  .form__group {
    grid-template-columns: 1fr;
  }

  .cart__total {
    padding: 1.25rem;
  }

  .payment__methods {
    margin-block: 2.25rem 2.5rem;
  }

  .detail__tabs {
    column-gap: 1.25rem;
    margin-bottom: 2rem;
  }

  .review__single {
    column-gap: 1rem;
  }

  .footer__title {
    margin-top: 0.25rem;
  }

  .footer__bottom {
    flex-direction: column;
    align-items: center;
  }

  .product__actions {
    display: none;
  }

  .header__search {
    max-width: 300px;
  }
}

@media screen and (max-width: 350px) {
  .action__btn {
    width: 34px;
    height: 34px;
    line-height: 36px;
  }

  .cart__btn {
    bottom: 1.4rem;
    right: 1.25rem;
  }

  .showcase__item {
    column-gap: 1rem;
  }

  .showcase__img {
    width: 70%;
  }

  .showcase__content {
    width: calc(100% - 86px);
  }

  .swiper-button-next,
  .swiper-button-prev {
    display: none;
  }

  .compare__table tr th,
  .cart__total-table tr td {
    display: block;
    width: 100%;
  }

  .cart__actions {
    justify-content: center;
  }

  .home__img {
    display: none;
  }

  .header__search {
    max-width: 270px;
  }
}
/*=============== Blog ===============*/


#page-header.blog-header {
  background-image: url(b19.jpg);
}

#blog {
  padding: 150px 150px 0 150px;
}

#blog .blog-box {
  display: flex;
  align-items: center;
  width: 100%;
  position: relative;
  padding-bottom: 90px;
}

#blog .blog-img {
  width: 30%;
  margin-right: 20px;
  
}

#blog img {
  width: 100%;
  height: 300px;
  object-fit: cover;
}

#blog .blog-details {
  width: 50%;
}

#blog .blog-details a {
  text-decoration: none;
  font-size: 11px;
  color: #000;
  font-weight: 700;
  position: relative;
  transition: 0.3s;
}

#blog .blog-details a::after {
  content: "";
  height: 1px;
  width: 50px;
  background-color: #000;
  position: absolute;
  top: 4px;
  right: -60px;
}

#blog .blog-details a:hover {
  color: #088178;
}

#blog .blog-details a:hover::after {
  background-color: #088178;
}

#blog .blog-box h1 {
  position: absolute;
  font-style: italic;
  top: -40px;
  left: 0;
  font-size: 70px;
  font-weight: 700;
  color: #c9cbce;
  z-index: -1;
}



/*=============== videos ===============*/

.mp4
 {
    display: flex;
    justify-content: left;
    align-items: center;
    height: 120vh;
    background-color: #f4f4f4;
}

video {
    max-width: 30%;
    border-radius: 90px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}


/* cart */
.cart__item, .wishlist__item {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.cart__item-img, .wishlist__item-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  margin-right: 1rem;
}

.cart__item-details, .wishlist__item-details {
  flex: 1;
}

.cart__item-title, .wishlist__item-title {
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
}

.cart__item-price, .wishlist__item-price {
  font-size: 1rem;
  color: #666;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}

.container {
    width: 90%;
    margin: auto;
    padding: 20px;
}

h1, h2 {
    color: #343a40;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #dee2e6;
}

th, td {
    padding: 10px;
    text-align: left;
}

th {
    background: #e9ecef;
}

.cart-table img {
    width: 50px;
    height: auto;
}

.btn {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 5px 0;
}

.btn:hover {
    background: #0056b3;
}

.message {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border: 1px solid #c3e6cb;
    margin-bottom: 20px;
    border-radius: 5px;
}

.cart-total-amount {
    font-weight: bold;
    color: #28a745;
    text-align: right;
}

.address-form {
    max-width: 500px;
    margin: auto;
    padding: 20px;
    border: 1px solid #ddd;
}

.address-form h2 {
    text-align: center;
}

.address-form label {
    display: block;
    margin-bottom: 10px;
}

.address-form input {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
}

/* Profile Photo Section */
.profile-photo-container {
  position: absolute;
  top: 10px;
  right: 20px;
  display: flex;
  align-items: center;
  cursor: pointer;
}

.profile-photo-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
}

.profile-photo {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--first-color);
}

.upload-text {
  font-size: var(--tiny-font-size);
  color: var(--text-color);
  margin-top: 5px;
}
      /* Add this CSS for the wishlist heart icon */
      .wishlist-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 24px;
        color: #ccc;
        cursor: pointer;
        transition: color 0.3s ease;
      }

      .wishlist-icon.active {
        color: red;
      }

      .wishlist-icon:hover {
        color: red;
      }
      .remove-btn {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 3px;
      }

      .remove-btn:hover {
        background-color: #c82333;
      }

      .status-cancelled {
        color: #dc3545;
        font-weight: bold;
      }

      .status-delivered {
        color: #28a745; /* Green color */
        font-weight: bold;
      }
    </style>

   
    <title>Ecommerce Website</title>
  </head>
  <body>
    <header class="header">
      <nav class="nav container">
          
          <!-- Profile Photo Section -->
          <div class="profile-photo-container">
            <label for="profile-photo-upload" class="profile-photo-label">
              <img src="pngwing.com.png" alt="Profile Photo" class="profile-photo" id="profile-photo">
              <span class="upload-text">Upload Photo</span>
            </label>
            <input type="file" id="profile-photo-upload" accept="image/*" style="display: none;">
          </div>
        </a>
          <!-- Removed the close icon -->
        </div>
      </nav>
    </header>
    <main class="main">
      <!--=============== ACCOUNTS ===============-->
      <section class="accounts section--lg">
        <div class="accounts__container container grid">
          <div class="account__tabs">
            <button class="account__tab active-tab" data-target="#dashboard">
              <i class="fi fi-rs-settings-sliders"></i> Dashboard
            </button>
            <button class="account__tab" data-target="#orders">
              <i class="fi fi-rs-shopping-bag"></i> Orders
            </button>
            <button class="account__tab" data-target="#update-profile">
              <i class="fi fi-rs-user"></i> Edit Profile
            </button>
            <button class="account__tab" data-target="#wishlist">
              <i class="fi fi-rs-heart"></i> Wishlist
            </button>
            <button class="account__tab" data-target="#address">
              <i class="fi fi-rs-marker"></i> My Address
            </button>
            <button class="account__tab" data-target="#change-password">
              <i class="fi fi-rs-settings-sliders"></i> Change Password
            </button>
            <button class="account__tab" data-target="#shipping-details">
              <i class="fi fi-rs-truck"></i> Shipping Details
            </button>
            <button class="account__tab">
              <a href="?logout=1" class="logout-link" style="color: inherit;">
                <i class="fi fi-rs-exit"></i> Logout
              </a>
            </button>
          </div>
          <div class="tabs__content">
            <div class="tab__content active-tab" id="dashboard">
              <h3 class="tab__header">Profile Page</h3>
              <div class="tab__body">
                <div class="user__info">
                  <h3 class="user__name"><?php echo htmlspecialchars($userDetails['fullname']); ?></h3>
                  <p class="user__email">Email: <?php echo htmlspecialchars($userDetails['email']); ?></p>
                  <p class="user__phone">Phone: <?php echo htmlspecialchars($userDetails['phone']); ?></p>
                  <p class="user__gender">Gender: <?php echo htmlspecialchars($userDetails['gender']); ?></p>
                </div>
              </div>
            </div>
            <div class="tab__content" id="orders">
              <h3 class="tab__header">Order History</h3>
              <div class="tab__body">
                <div class="container">
                  <h2>Your Orders</h2>
                  <?php if ($result->num_rows > 0): ?>
                      <table>
                          <thead>
                              <tr>
                                  <th>Order ID</th>
                                  <th>Product Image</th>
                                  <th>Product Name</th>
                                  <th>Quantity</th>
                                  <th>Size</th>
                                  <th>Total Amount</th>
                                  <th>Payment Method</th>
                                  <th>Order Date</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php while ($row = $result->fetch_assoc()): ?>
                                  <tr>
                                      <td>#<?php echo htmlspecialchars($row['order_id']); ?></td>
                                      <td>
                                          <img src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image" style="width: 50px; height: auto;">
                                      </td>
                                      <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                      <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                      <td><?php echo htmlspecialchars($row['size']); ?></td>
                                      <td>₹<?php echo number_format($row['final_amount'], 2); ?></td>
                                      <td><?php echo ucfirst(htmlspecialchars($row['payment_method'])); ?></td>
                                      <td><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['created_at']))); ?></td>
                                      <td>
                                          <?php if (!in_array($row['delivery_status'], ['Delivered', 'Cancelled'])): ?>
                                              <form method="post" action="accounts.php">
                                                  <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                  <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                                  <button type="submit" name="cancel_product" class="remove-btn">Cancel</button>
                                              </form>
                                          <?php else: ?>
                                              <span class="<?php echo $row['delivery_status'] === 'Delivered' ? 'status-delivered' : 'status-cancelled'; ?>">
                                                  <?php echo htmlspecialchars($row['delivery_status']); ?>
                                              </span>
                                          <?php endif; ?>
                                      </td>
                                  </tr>
                              <?php endwhile; ?>
                          </tbody>
                      </table>
                  <?php else: ?>
                      <p class="no-orders">You have no orders yet.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="tab__content" id="update-profile">
              <h3 class="tab__header">Update Profile</h3>
              <div class="tab__body">
                <form method="POST" class="form grid">
                  <input
                    type="text"
                    name="fullname"
                    placeholder="Full Name"
                    class="form__input"
                    value="<?php echo htmlspecialchars($_SESSION['fullname']); ?>"
                    required
                  />
                  <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    class="form__input"
                    value="<?php echo htmlspecialchars($_SESSION['email']); ?>"
                    required
                  />
                  <input
                    type="text"
                    name="phone"
                    placeholder="Phone Number"
                    class="form__input"
                    value="<?php echo htmlspecialchars($_SESSION['phone']); ?>"
                    required
                  />
                  <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    class="form__input"
                    value="<?php echo htmlspecialchars($_SESSION['username']); ?>"
                    required
                  />
                  <div class="form__btn">
                    <button type="submit" name="update_profile" class="btn btn--md">Save</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="tab__content" id="wishlist">
              <h3 class="tab__header">Wishlist</h3>
              <div class="tab__body">
                <?php if (!empty($wishlistItems)): ?>
                  <div class="wishlist__items">
                    <?php foreach ($wishlistItems as $item): ?>
                      <div class="wishlist__item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="wishlist__item-img">
                        <div class="wishlist__item-details">
                          <h4 class="wishlist__item-title"><?php echo htmlspecialchars($item['name']); ?></h4>
                          <p class="wishlist__item-price">$<?php echo htmlspecialchars($item['price']); ?></p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p>Your wishlist is empty.</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="tab__content" id="address">
              <h3 class="tab__header">Shipping Address</h3>
              <div class="tab__body">
                <div class="address__list">
                  <?php if ($addressResult->num_rows > 0): ?>
                    <?php while ($addressRow = $addressResult->fetch_assoc()): ?>
                      <div class="address__item">
                        <address class="address">
                          <?php echo htmlspecialchars($addressRow['house_no'] . ', ' . $addressRow['building_name'] . ', ' . $addressRow['road_name'] . ', ' . $addressRow['area_name']); ?><br />
                          <?php echo htmlspecialchars($addressRow['city']); ?><br />
                          <?php echo htmlspecialchars($addressRow['state']); ?><br />
                          <?php echo htmlspecialchars($addressRow['pincode']); ?>
                        </address>
                        <p class="city"><?php echo htmlspecialchars($addressRow['city']); ?></p>
                        <a href="address2.php?action=edit&id=<?php echo $addressRow['id']; ?>" class="edit">Edit Address</a>
                      </div>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <p>No addresses found.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="tab__content" id="change-password">
              <h3 class="tab__header">Change Password</h3>
              <div class="tab__body">
                <form method="POST" class="form grid">
                  <input
                    type="password"
                    name="current_password"
                    placeholder="Current Password"
                    class="form__input"
                    required
                  />
                  <input
                    type="password"
                    name="new_password"
                    placeholder="New Password"
                    class="form__input"
                    required
                  />
                  <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm Password"
                    class="form__input"
                    required
                  />
                  <div class="form__btn">
                    <button type="submit" name="change_password" class="btn btn--md">Save</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="tab__content" id="shipping-details">
              <h3 class="tab__header">Shipping Details</h3>
              <div class="tab__body">
                <?php
                $shippingQuery = "SELECT order_id, address, delivery_status, delivery_notes 
                                  FROM orders 
                                  WHERE user_name = ? AND delivery_status IN ('In Transit', 'Cannot Reach Customer')";
                $shippingStmt = $conn->prepare($shippingQuery);
                $shippingStmt->bind_param("s", $_SESSION['username']);
                $shippingStmt->execute();
                $shippingResult = $shippingStmt->get_result();
                ?>
                <?php if ($shippingResult->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Shipping Address</th>
                                <th>Delivery Status</th>
                                <th>Delivery Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $shippingResult->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['address'])); ?></td>
                                    <td>
                                        <span class="<?php echo $row['delivery_status'] === 'In Transit' ? 'status-transit' : 'status-cannot-reach'; ?>">
                                            <?php echo htmlspecialchars($row['delivery_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars($row['delivery_notes'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No shipping details available for "In Transit" or "Cannot Reach Customer" orders.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </section>

    <!--=============== SWIPER JS ===============-->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!--=============== MAIN JS ===============-->
    <script>
      // Handle Profile Photo Upload
      document.getElementById('profile-photo-upload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            document.getElementById('profile-photo').src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });

      // Handle Wishlist Toggle
      document.querySelectorAll('.wishlist-icon').forEach(icon => {
        icon.addEventListener('click', function() {
          this.classList.toggle('active');
        });
      });

      // Handle tab switching
      document.querySelectorAll('.account__tab').forEach(tab => {
        tab.addEventListener('click', function () {
          // Remove active class from all tabs and contents
          document.querySelectorAll('.account__tab').forEach(t => t.classList.remove('active-tab'));
          document.querySelectorAll('.tab__content').forEach(content => content.classList.remove('active-tab'));

          // Add active class to the clicked tab and its content
          this.classList.add('active-tab');
          const target = document.querySelector(this.getAttribute('data-target'));
          if (target) target.classList.add('active-tab');
        });
      });
    </script>
  </body>
</html>