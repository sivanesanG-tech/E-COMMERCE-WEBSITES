<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all brands from the database
$brands_query = "SELECT id, name FROM brands";
$brands_result = $conn->query($brands_query);

// Handle adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $old_price = $_POST['old_price'];
    $new_price = $_POST['new_price'];
    $details = $_POST['details'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $brand_name = $_POST['brand_name'] ?? '';
    $image = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path;
        } else {
            $_SESSION['error'] = "Error uploading image.";
            header("Location: manage&updateproduct.php");
            exit();
        }
    }

    $query = "INSERT INTO products (name, old_price, new_price, details, image, category, stock, brand_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sddsssii', $name, $old_price, $new_price, $details, $image, $category, $stock, $brand_name);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Error adding product: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: manage&updateproduct.php");
    exit();
}

// Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $old_price = $_POST['old_price'];
    $new_price = $_POST['new_price'];
    $details = $_POST['details'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $brand_name = $_POST['brand_name'] ?? '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path;
            // Delete old image if it exists
            if (!empty($_POST['existing_image']) && file_exists($_POST['existing_image'])) {
                unlink($_POST['existing_image']);
            }
        }
    } else {
        $image = $_POST['existing_image'];
    }

    $query = "UPDATE products SET name = ?, old_price = ?, new_price = ?, details = ?, image = ?, category = ?, stock = ?, brand_name = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sddsssisi', $name, $old_price, $new_price, $details, $image, $category, $stock, $brand_name, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating product: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: manage&updateproduct.php");
    exit();
}

// Handle deleting a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = $_POST['id'];

    // First get the image path to delete the file
    $get_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $get_image->bind_param('i', $id);
    $get_image->execute();
    $get_image->bind_result($image_path);
    $get_image->fetch();
    $get_image->close();

    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists
        if (!empty($image_path)) {
            unlink($image_path);
        }
        $_SESSION['success'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting product: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: manage&updateproduct.php");
    exit();
}

// Fetch all products with brand name
$query = "SELECT products.*, brands.name AS brand_name FROM products LEFT JOIN brands ON products.brand_name = brands.id";
$result = $conn->query($query);

// Fetch counts for dashboard
$product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$brand_count = $conn->query("SELECT COUNT(*) as count FROM brands")->fetch_assoc()['count'];
$user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Unicons CSS -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap");
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }
      :root {
        /* ===== Colors ===== */
        --primary-color: #0e4bf1;
        --panel-color: #fff;
        --text-color: #000;
        --black-light-color: #707070;
        --border-color: #e6e5e5;
        --toggle-color: #ddd;
        --box1-color: #4da3ff;
        --box2-color: #ffe6ac;
        --box3-color: #e7d1fc;
        --title-icon-color: #fff;
        --sidebar-icon-color: #5a5a5a;
        --success-color: #28a745;
        --error-color: #dc3545;
        --warning-color: #ffc107;

        /* ====== Transition ====== */
        --tran-05: all 0.5s ease;
        --tran-03: all 0.3s ease;
        --tran-02: all 0.2s ease;
      }
      body {
        min-height: 100vh;
        background-color: var(--primary-color);
      }
      body.dark {
        --primary-color: #3a3b3c;
        --panel-color: #242526;
        --text-color: #ccc;
        --black-light-color: #ccc;
        --border-color: #4d4c4c;
        --toggle-color: #fff;
        --box1-color: #3a3b3c;
        --box2-color: #3a3b3c;
        --box3-color: #3a3b3c;
        --title-icon-color: #ccc;
        --sidebar-icon-color: #a0a0a0;
      }
      /* === Custom Scroll Bar CSS === */
      ::-webkit-scrollbar {
        width: 8px;
      }
      ::-webkit-scrollbar-track {
        background: #f1f1f1;
      }
      ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 12px;
        transition: all 0.3s ease;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: #0b3cc1;
      }
      body.dark::-webkit-scrollbar-thumb:hover,
      body.dark .activity-data::-webkit-scrollbar-thumb:hover {
        background: #3a3b3c;
      }
      nav {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 250px;
        padding: 10px 14px;
        background-color: var(--panel-color);
        border-right: 1px solid var(--border-color);
        transition: var(--tran-05);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--primary-color) #f1f1f1;
      }
      nav::-webkit-scrollbar {
        width: 8px;
      }
      nav::-webkit-scrollbar-track {
        background: #f1f1f1;
      }
      nav::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 12px;
      }
      nav::-webkit-scrollbar-thumb:hover {
        background: #0b3cc1;
      }
      nav.close {
        width: 73px;
      }
      nav .logo-name {
        display: flex;
        align-items: center;
      }
      nav .logo-image {
        display: flex;
        justify-content: center;
        min-width: 45px;
      }
      nav .logo-image img {
        width: 40px;
        object-fit: cover;
        border-radius: 50%;
      }
      nav .logo-name .logo_name {
        font-size: 22px;
        font-weight: 600;
        color: var(--text-color);
        margin-left: 14px;
        transition: var(--tran-05);
      }
      nav.close .logo_name {
        opacity: 0;
        pointer-events: none;
      }
      nav .menu-items {
        margin-top: 40px;
        height: calc(100% - 90px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }
      .menu-items li {
        list-style: none;
      }
      .menu-items li a {
        display: flex;
        align-items: center;
        height: 50px;
        text-decoration: none;
        position: relative;
      }
      .nav-links li a:hover:before {
        content: "";
        position: absolute;
        left: -7px;
        height: 5px;
        width: 5px;
        border-radius: 50%;
        background-color: var(--primary-color);
      }
      body.dark li a:hover:before {
        background-color: var(--text-color);
      }
      .menu-items li a i {
        font-size: 22px;
        min-width: 45px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--sidebar-icon-color);
      }
      .menu-items li a .link-name {
        font-size: 16px;
        font-weight: 400;
        color: var(--black-light-color);
        transition: var(--tran-05);
      }
      nav.close li a .link-name {
        opacity: 0;
        pointer-events: none;
      }
      .nav-links li a:hover i,
      .nav-links li a:hover .link-name {
        color: var(--primary-color);
      }
      body.dark .nav-links li a:hover i,
      body.dark .nav-links li a:hover .link-name {
        color: var(--text-color);
      }
      .menu-items .logout-mode {
        padding-top: 10px;
        border-top: 1px solid var(--border-color);
      }
      .menu-items .mode {
        display: flex;
        align-items: center;
        white-space: nowrap;
      }
      .menu-items .mode-toggle {
        position: absolute;
        right: 14px;
        height: 50px;
        min-width: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
      }
      .mode-toggle .switch {
        position: relative;
        display: inline-block;
        height: 22px;
        width: 40px;
        border-radius: 25px;
        background-color: var(--toggle-color);
      }
      .switch:before {
        content: "";
        position: absolute;
        left: 5px;
        top: 50%;
        transform: translateY(-50%);
        height: 15px;
        width: 15px;
        background-color: var(--panel-color);
        border-radius: 50%;
        transition: var(--tran-03);
      }
      body.dark .switch:before {
        left: 20px;
      }
      .dashboard {
        position: relative;
        left: 250px;
        background-color: var(--panel-color);
        min-height: 100vh;
        width: calc(100% - 250px);
        padding: 10px 14px;
        transition: var(--tran-05);
      }
      nav.close ~ .dashboard {
        left: 73px;
        width: calc(100% - 73px);
      }
      .dashboard .top {
        position: fixed;
        top: 0;
        left: 250px;
        display: flex;
        width: calc(100% - 250px);
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        background-color: var(--panel-color);
        transition: var(--tran-05);
        z-index: 10;
      }
      nav.close ~ .dashboard .top {
        left: 73px;
        width: calc(100% - 73px);
      }
      .dashboard .top .sidebar-toggle {
        font-size: 26px;
        color: var(--text-color);
        cursor: pointer;
      }
      .dashboard .top .search-box {
        position: relative;
        height: 45px;
        max-width: 600px;
        width: 100%;
        margin: 0 30px;
      }
      .top .search-box input {
        position: absolute;
        border: 1px solid var(--border-color);
        background-color: var(--panel-color);
        padding: 0 25px 0 50px;
        border-radius: 5px;
        height: 100%;
        width: 100%;
        color: var(--text-color);
        font-size: 15px;
        font-weight: 400;
        outline: none;
      }
      .top .search-box i {
        position: absolute;
        left: 15px;
        font-size: 22px;
        z-index: 10;
        top: 50%;
        transform: translateY(-50%);
        color: var(--black-light-color);
      }
      .top img {
        width: 40px;
        border-radius: 50%;
      }
      .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-color); /* Ensure blue background */
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px; /* Adjust font size for better visibility */
      }
      .dashboard .dash-content {
        padding-top: 50px;
      }
      .dash-content .title {
        display: flex;
        align-items: center;
        margin: 60px 0 30px 0;
      }
      .dash-content .title i {
        position: relative;
        height: 35px;
        width: 35px;
        background-color: var(--primary-color);
        border-radius: 6px;
        color: var(--title-icon-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
      }
      .dash-content .title .text {
        font-size: 24px;
        font-weight: 500;
        color: var(--text-color);
        margin-left: 10px;
      }
      
      /* Dashboard Boxes */
      .dash-content .boxes {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
      }
      .dash-content .boxes .box {
        display: flex;
        flex-direction: column;
        align-items: center;
        border-radius: 12px;
        width: calc(100% / 3 - 15px);
        padding: 20px;
        background-color: var(--box1-color);
        transition: var(--tran-05);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      .boxes .box i {
        font-size: 35px;
        color: var(--text-color);
        margin-bottom: 10px;
      }
      .boxes .box .text {
        font-size: 16px;
        font-weight: 500;
        color: var(--text-color);
        margin-bottom: 5px;
      }
      .boxes .box .number {
        font-size: 28px;
        font-weight: 600;
        color: var(--text-color);
      }
      .boxes .box.box2 {
        background-color: var(--box2-color);
      }
      .boxes .box.box3 {
        background-color: var(--box3-color);
      }
      
      /* Alert Messages */
      .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      .alert-success {
        background-color: rgba(40, 167, 69, 0.2);
        color: var(--success-color);
        border-left: 4px solid var(--success-color);
      }
      .alert-error {
        background-color: rgba(220, 53, 69, 0.2);
        color: var(--error-color);
        border-left: 4px solid var(--error-color);
      }
      .close-alert {
        background: none;
        border: none;
        color: inherit;
        font-size: 20px;
        cursor: pointer;
      }
      
      /* Form Styles */
      .form-container {
        background-color: var(--panel-color);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      }
      .form-container h2 {
        margin-bottom: 20px;
        color: var(--text-color);
        font-size: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
      }
      .form-group {
        margin-bottom: 20px;
      }
      .form-group label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--text-color);
      }
      .form-group input,
      .form-group textarea,
      .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        background-color: var(--panel-color);
        color: var(--text-color);
        font-size: 15px;
        transition: var(--tran-03);
      }
      .form-group input:focus,
      .form-group textarea:focus,
      .form-group select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(14, 75, 241, 0.2);
      }
      .form-group textarea {
        min-height: 120px;
        resize: vertical;
      }
      .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
      }
      .btn {
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: 500;
        cursor: pointer;
        transition: var(--tran-03);
        display: inline-flex;
        align-items: center;
        gap: 8px;
      }
      .btn-primary {
        background-color: var(--primary-color);
        color: white;
        border: none;
      }
      .btn-primary:hover {
        background-color: #0b3cc1;
      }
      .btn-secondary {
        background-color: var(--border-color);
        color: var(--text-color);
        border: none;
      }
      .btn-secondary:hover {
        background-color: #d1d1d1;
      }
      .btn-danger {
        background-color: var(--error-color);
        color: white;
        border: none;
      }
      .btn-danger:hover {
        background-color: #c82333;
      }
      
      /* Table Styles */
      .table-container {
        background-color: var(--panel-color);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
      }
      .table-container h2 {
        margin-bottom: 20px;
        color: var(--text-color);
        font-size: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
      }
      .products-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      }
      .products-table th {
        background-color: var(--primary-color);
        color: white;
        padding: 12px 15px;
        text-align: left;
        font-weight: 500;
      }
      .products-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-color);
        vertical-align: middle;
      }
      .products-table tr:nth-child(even) {
        background-color: rgba(0, 0, 0, 0.02);
      }
      .products-table tr:hover {
        background-color: rgba(0, 0, 0, 0.05);
      }
      .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
      }
      .product-actions {
        display: flex;
        gap: 10px;
      }
      .action-btn {
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        transition: var(--tran-03);
        display: inline-flex;
        align-items: center;
        gap: 5px;
      }
      .action-btn i {
        font-size: 16px;
      }
      
      /* Edit Form Styles */
      .edit-form-container {
        display: none;
        background-color: rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        padding: 20px;
        margin-top: 10px;
      }
      .edit-form-container.active {
        display: block;
      }
      
      /* Responsive Styles */
      @media (max-width: 1200px) {
        .dash-content .boxes .box {
          width: calc(50% - 15px);
        }
      }
      @media (max-width: 768px) {
        .dash-content .boxes .box {
          width: 100%;
        }
        .products-table {
          display: block;
          overflow-x: auto;
        }
      }
      @media (max-width: 1000px) {
        nav {
          width: 73px;
        }
        nav.close {
          width: 250px;
        }
        nav .logo_name {
          opacity: 0;
          pointer-events: none;
        }
        nav.close .logo_name {
          opacity: 1;
          pointer-events: auto;
        }
        nav li a .link-name {
          opacity: 0;
          pointer-events: none;
        }
        nav.close li a .link-name {
          opacity: 1;
          pointer-events: auto;
        }
        nav ~ .dashboard {
          left: 73px;
          width: calc(100% - 73px);
        }
        nav.close ~ .dashboard {
          left: 250px;
          width: calc(100% - 250px);
        }
        nav ~ .dashboard .top {
          left: 73px;
          width: calc(100% - 73px);
        }
        nav.close ~ .dashboard .top {
          left: 250px;
          width: calc(100% - 250px);
        }
      }
      @media (max-width: 400px) {
        nav {
          width: 0px;
        }
        nav.close {
          width: 73px;
        }
        nav .logo_name {
          opacity: 0;
          pointer-events: none;
        }
        nav.close .logo_name {
          opacity: 0;
          pointer-events: none;
        }
        nav li a .link-name {
          opacity: 0;
          pointer-events: none;
        }
        nav.close li a .link-name {
          opacity: 0;
          pointer-events: none;
        }
        nav ~ .dashboard {
          left: 0;
          width: 100%;
        }
        nav.close ~ .dashboard {
          left: 73px;
          width: calc(100% - 73px);
        }
        nav ~ .dashboard .top {
          left: 0;
          width: 100%;
        }
        nav.close ~ .dashboard .top {
          left: 0;
          width: 100%;
        }
        .form-actions, .product-actions {
          flex-direction: column;
        }
        .btn, .action-btn {
          width: 100%;
          justify-content: center;
        }
      }
    </style>
    <title>Admin Dashboard - Product Management</title>
</head>
<body>
    <nav class="nav container">
      <div class="logo-name">
        <div class="logo-image">
          <i class="uil uil-user-md" style="font-size: 30px; color: var(--primary-color);"></i>
        </div>
        <span class="logo_name">Admin Panel</span>
      </div>
      <div class="menu-items">
        <ul class="nav-links">
          <li>
            <a href="adminindex.php">
              <i class="uil uil-estate"></i>
              <span class="link-name">Dashboard</span>
            </a>
          </li>
          <li>
            <a href="analytics_dashboard.php">
              <i class="uil uil-chart-line"></i>
              <span class="link-name">Analytics</span>
            </a>
          </li>
          <li>
            <a href="registered_users.php">
              <i class="uil uil-users-alt"></i>
              <span class="link-name">Users</span>
            </a>
          </li>
          <li>
            <a href="manage&updateproduct.php" class="active">
              <i class="uil uil-package"></i>
              <span class="link-name">Manage Products</span>
            </a>
          </li>
          <li>
            <a href="product_details.php">
              <i class="uil uil-shopping-basket"></i>
              <span class="link-name">Product Details</span>
            </a>
          </li>
          <li>
            <a href="order_dashboard.php">
              <i class="uil uil-receipt"></i>
              <span class="link-name">Orders</span>
            </a>
          </li>
          <li>
            <a href="customer-support.php">
              <i class="uil uil-headphones"></i>
              <span class="link-name">Support</span>
            </a>
          </li>
          <li>
            <a href="manage_brands.php">
              <i class="uil uil-tag-alt"></i>
              <span class="link-name">Brands</span>
            </a>
          </li>
          <li>
            <a href="delivery_dashboard.php">
              <i class="uil uil-truck"></i>
              <span class="link-name">Delivery Details</span>
            </a>
          </li>
          <li>
            <a href="delivery_partner_dashboard.php">
              <i class="uil uil-user-arrows"></i>
              <span class="link-name">Delivery Partners</span>
            </a>
          </li>
        </ul>
        <ul class="logout-mode">
          <li>
            <a href="#">
              <i class="uil uil-signout"></i>
              <span class="link-name">Logout</span>
            </a>
          </li>
          <li class="mode">
            <a href="#">
              <i class="uil uil-moon"></i>
              <span class="link-name">Dark Mode</span>
            </a>
            <div class="mode-toggle">
              <span class="switch"></span>
            </div>
          </li>
        </ul>
      </div>
    </nav>
    <section class="dashboard">
      <div class="top">
        <i class="uil uil-bars sidebar-toggle"></i>
        <div class="search-box">
          <i class="uil uil-search"></i>
          <input type="text" placeholder="Search products..." />
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
          <i class="uil uil-bell" style="font-size: 22px; color: var(--text-color); cursor: pointer;"></i>
          <i class="uil uil-envelope" style="font-size: 22px; color: var(--text-color); cursor: pointer;"></i>
          <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar">AD</div>
            <span style="color: var(--text-color);">Admin</span>
          </div>
        </div>
      </div>
      <div class="dash-content">
        <div class="title">
          <i class="uil uil-package"></i>
          <span class="text">Product Management</span>
        </div>
        
        <!-- Dashboard Stats -->
        <div class="boxes">
          <div class="box box1">
            <i class="uil uil-package"></i>
            <span class="text">Total Products</span>
            <span class="number"><?php echo $product_count; ?></span>
          </div>
          <div class="box box2">
            <i class="uil uil-tag-alt"></i>
            <span class="text">Total Brands</span>
            <span class="number"><?php echo $brand_count; ?></span>
          </div>
          <div class="box box3">
            <i class="uil uil-users-alt"></i>
            <span class="text">Total Users</span>
            <span class="number"><?php echo $user_count; ?></span>
          </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success">
            <?php echo $_SESSION['success']; ?>
            <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
          </div>
          <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-error">
            <?php echo $_SESSION['error']; ?>
            <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
          </div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Add Product Form -->
        <div class="form-container">
          <h2><i class="uil uil-plus-circle"></i> Add New Product</h2>
          <form method="post" action="manage&updateproduct.php" enctype="multipart/form-data">
            <div class="form-group">
              <label for="name">Product Name</label>
              <input type="text" id="name" name="name" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div class="form-group">
                <label for="old_price">Original Price (₹)</label>
                <input type="number" id="old_price" name="old_price" step="0.01" min="0" required>
              </div>
              <div class="form-group">
                <label for="new_price">Discounted Price (₹)</label>
                <input type="number" id="new_price" name="new_price" step="0.01" min="0" required>
              </div>
            </div>
            
            <div class="form-group">
              <label for="details">Product Details</label>
              <textarea id="details" name="details" required></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" required>
              </div>
              <div class="form-group">
                <label for="stock">Stock Quantity</label>
                <input type="number" id="stock" name="stock" min="0" required>
              </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div class="form-group">
                <label for="brand_name">Brand</label>
                <select id="brand_name" name="brand_name" required>
                  <option value="">Select a Brand</option>
                  <?php 
                  $brands_result->data_seek(0); // Reset pointer to the beginning
                  while ($brand = $brands_result->fetch_assoc()): ?>
                    <option value="<?php echo $brand['id']; ?>">
                      <?php echo htmlspecialchars($brand['name']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
              </div>
            </div>
            
            <div class="form-actions">
              <button type="reset" class="btn btn-secondary">
                <i class="uil uil-redo"></i> Reset
              </button>
              <button type="submit" name="add_product" class="btn btn-primary">
                <i class="uil uil-save"></i> Add Product
              </button>
            </div>
          </form>
        </div>
        
        <!-- Product List -->
        <div class="table-container">
          <h2><i class="uil uil-list-ul"></i> Product List</h2>
          
          <?php if ($result->num_rows > 0): ?>
            <table class="products-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th>Name</th>
                  <th>Price</th>
                  <th>Category</th>
                  <th>Stock</th>
                  <th>Brand</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $brands_result->data_seek(0); // Reset pointer to the beginning
                while ($row = $result->fetch_assoc()): 
                ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td>
                      <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image" class="product-image">
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                      <span style="text-decoration: line-through; color: var(--error-color);">
                        ₹<?php echo number_format($row['old_price'], 2); ?>
                      </span><br>
                      <span style="font-weight: bold; color: var(--success-color);">
                        ₹<?php echo number_format($row['new_price'], 2); ?>
                      </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>
                      <span style="font-weight: bold; color: <?php echo $row['stock'] > 0 ? 'var(--success-color)' : 'var(--error-color)'; ?>">
                        <?php echo htmlspecialchars($row['stock']); ?>
                      </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                    <td>
                      <div class="product-actions">
                        <button class="action-btn btn-primary" onclick="toggleEditForm(<?php echo $row['id']; ?>)">
                          <i class="uil uil-edit"></i> Edit
                        </button>
                        <form method="post" action="manage&updateproduct.php" onsubmit="return confirm('Are you sure you want to delete this product?');">
                          <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                          <button type="submit" name="delete_product" class="action-btn btn-danger">
                            <i class="uil uil-trash-alt"></i> Delete
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="8">
                      <div class="edit-form-container" id="edit-form-<?php echo $row['id']; ?>">
                        <form method="post" action="manage&updateproduct.php" enctype="multipart/form-data">
                          <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="existing_image" value="<?php echo $row['image']; ?>">
                          
                          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                              <label for="edit-name-<?php echo $row['id']; ?>">Product Name</label>
                              <input type="text" id="edit-name-<?php echo $row['id']; ?>" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                              <label>Current Image</label>
                              <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Current Image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                <label for="edit-image-<?php echo $row['id']; ?>" style="cursor: pointer; color: var(--primary-color);">
                                  <i class="uil uil-image-upload"></i> Change Image
                                  <input type="file" id="edit-image-<?php echo $row['id']; ?>" name="image" accept="image/*" style="display: none;">
                                </label>
                              </div>
                            </div>
                          </div>
                          
                          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                              <label for="edit-old_price-<?php echo $row['id']; ?>">Original Price (₹)</label>
                              <input type="number" id="edit-old_price-<?php echo $row['id']; ?>" name="old_price" value="<?php echo $row['old_price']; ?>" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                              <label for="edit-new_price-<?php echo $row['id']; ?>">Discounted Price (₹)</label>
                              <input type="number" id="edit-new_price-<?php echo $row['id']; ?>" name="new_price" value="<?php echo $row['new_price']; ?>" step="0.01" min="0" required>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label for="edit-details-<?php echo $row['id']; ?>">Product Details</label>
                            <textarea id="edit-details-<?php echo $row['id']; ?>" name="details" required><?php echo htmlspecialchars($row['details']); ?></textarea>
                          </div>
                          
                          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                              <label for="edit-category-<?php echo $row['id']; ?>">Category</label>
                              <input type="text" id="edit-category-<?php echo $row['id']; ?>" name="category" value="<?php echo htmlspecialchars($row['category']); ?>" required>
                            </div>
                            <div class="form-group">
                              <label for="edit-stock-<?php echo $row['id']; ?>">Stock Quantity</label>
                              <input type="number" id="edit-stock-<?php echo $row['id']; ?>" name="stock" value="<?php echo $row['stock']; ?>" min="0" required>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label for="edit-brand_name-<?php echo $row['id']; ?>">Brand</label>
                            <select id="edit-brand_name-<?php echo $row['id']; ?>" name="brand_name" required>
                              <option value="">Select a Brand</option>
                              <?php 
                              $brands_result->data_seek(0); // Reset pointer to the beginning
                              while ($brand = $brands_result->fetch_assoc()): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo ($brand['id'] == $row['brand_name']) ? 'selected' : ''; ?>>
                                  <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                              <?php endwhile; ?>
                            </select>
                          </div>
                          
                          <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="toggleEditForm(<?php echo $row['id']; ?>)">
                              <i class="uil uil-times"></i> Cancel
                            </button>
                            <button type="submit" name="update_product" class="btn btn-primary">
                              <i class="uil uil-save"></i> Update Product
                            </button>
                          </div>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div style="text-align: center; padding: 40px; color: var(--black-light-color);">
              <i class="uil uil-exclamation-triangle" style="font-size: 50px; margin-bottom: 15px;"></i>
              <h3>No Products Found</h3>
              <p>There are no products in the database. Add your first product using the form above.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <script>
      // Function to toggle the edit form
      function toggleEditForm(id) {
        const editForm = document.getElementById(`edit-form-${id}`);
        if (editForm) {
          editForm.classList.toggle('active');
          
          // Scroll to the edit form if opening
          if (editForm.classList.contains('active')) {
            editForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        }
      }

      // Dark/Light mode toggle
      const body = document.querySelector("body"),
            modeToggle = document.querySelector(".mode-toggle");

      // Check for saved mode preference
      let getMode = localStorage.getItem("mode");
      if (getMode && getMode === "dark") {
        body.classList.add("dark");
      }

      modeToggle.addEventListener("click", () => {
        body.classList.toggle("dark");
        if (body.classList.contains("dark")) {
          localStorage.setItem("mode", "dark");
        } else {
          localStorage.setItem("mode", "light");
        }
      });

      // Sidebar toggle
      const sidebar = document.querySelector("nav"),
            sidebarToggle = document.querySelector(".sidebar-toggle");

      sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
      });

      // Auto-close alerts after 5 seconds
      document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
          setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
              alert.style.display = 'none';
            }, 300);
          }, 5000);
        });
      });

      // Price validation - ensure discounted price is less than original price
      document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
          const oldPriceInput = form.querySelector('input[name="old_price"]');
          const newPriceInput = form.querySelector('input[name="new_price"]');
          
          if (oldPriceInput && newPriceInput) {
            form.addEventListener('submit', function(e) {
              const oldPrice = parseFloat(oldPriceInput.value);
              const newPrice = parseFloat(newPriceInput.value);
              
              if (newPrice > oldPrice) {
                e.preventDefault();
                alert('Discounted price must be less than original price!');
                newPriceInput.focus();
              }
            });
          }
        });
      });
    </script>
</body>
</html>

<?php
$conn->close();
?>