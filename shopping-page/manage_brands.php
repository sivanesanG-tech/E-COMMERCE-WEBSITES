<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle adding a new brand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brand'])) {
    $name = $_POST['name'];

    $query = "INSERT INTO brands (name) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $name);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Brand added successfully!";
    } else {
        $_SESSION['error'] = "Error adding brand: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: manage_brands.php");
    exit();
}

// Handle updating a brand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_brand'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];

    $query = "UPDATE brands SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $name, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Brand updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating brand: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: manage_brands.php");
    exit();
}

// Handle deleting a brand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_brand'])) {
    $id = $_POST['id'];

    // Check if brand is used in products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE brand_name = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('i', $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $brand_usage = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($brand_usage['count'] > 0) {
        $_SESSION['error'] = "Cannot delete brand - it is being used by products!";
    } else {
        $query = "DELETE FROM brands WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Brand deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting brand: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    header("Location: manage_brands.php");
    exit();
}

// Fetch all brands
$query = "SELECT id, name AS brand_name FROM brands ORDER BY name";
$result = $conn->query($query);

// Get brand count for dashboard
$brand_count = $conn->query("SELECT COUNT(*) as count FROM brands")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Brand Management</title>
    <!-- Unicons CSS -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
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
            background-color: var(--primary-color); /* Blue background */
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--panel-color);
            color: var(--text-color);
            font-size: 15px;
            transition: var(--tran-03);
        }
        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(14, 75, 241, 0.2);
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
        .brands-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .brands-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        .brands-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
            vertical-align: middle;
        }
        .brands-table tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        .brands-table tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .brand-actions {
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
            .brands-table {
                display: block;
                overflow-x: auto;
            }
            .brand-actions {
                flex-direction: column;
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
            .form-actions, .brand-actions {
                flex-direction: column;
            }
            .btn, .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
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
                    <a href="manage&updateproduct.php">
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
                    <a href="manage_brands.php" class="active">
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
                        <span class="link-name">Delivery Partners</span>an>
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
                <input type="text" placeholder="Search brands..." />
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
                <i class="uil uil-tag-alt"></i>
                <span class="text">Brand Management</span>
            </div>
            
            <!-- Dashboard Stats -->
            <div class="boxes">
                <div class="box box1">
                    <i class="uil uil-tag-alt"></i>
                    <span class="text">Total Brands</span>
                    <span class="number"><?php echo $brand_count; ?></span>
                </div>
                <div class="box box2">
                    <i class="uil uil-package"></i>
                    <span class="text">Total Products</span>
                    <span class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count']; ?></span>
                </div>
                <div class="box box3">
                    <i class="uil uil-users-alt"></i>
                    <span class="text">Total Users</span>
                    <span class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count']; ?></span>
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
            
            <!-- Add Brand Form -->
            <div class="form-container">
                <h2><i class="uil uil-plus-circle"></i> Add New Brand</h2>
                <form method="post" action="manage_brands.php">
                    <div class="form-group">
                        <label for="name">Brand Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_brand" class="btn btn-primary">
                            <i class="uil uil-save"></i> Add Brand
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Brand List -->
            <div class="table-container">
                <h2><i class="uil uil-list-ul"></i> Brand List</h2>
                
                <?php if ($result->num_rows > 0): ?>
                    <table class="brands-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Brand Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                                    <td>
                                        <div class="brand-actions">
                                            <button class="action-btn btn-primary" onclick="toggleEditForm(<?php echo $row['id']; ?>)">
                                                <i class="uil uil-edit"></i> Edit
                                            </button>
                                            <form method="post" action="manage_brands.php" onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete_brand" class="action-btn btn-danger">
                                                    <i class="uil uil-trash-alt"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <div class="edit-form-container" id="edit-form-<?php echo $row['id']; ?>">
                                            <form method="post" action="manage_brands.php">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <div class="form-group">
                                                    <label for="edit-name-<?php echo $row['id']; ?>">Brand Name</label>
                                                    <input type="text" id="edit-name-<?php echo $row['id']; ?>" name="name" value="<?php echo htmlspecialchars($row['brand_name']); ?>" required>
                                                </div>
                                                <div class="form-actions">
                                                    <button type="button" class="btn btn-secondary" onclick="toggleEditForm(<?php echo $row['id']; ?>)">
                                                        <i class="uil uil-times"></i> Cancel
                                                    </button>
                                                    <button type="submit" name="update_brand" class="btn btn-primary">
                                                        <i class="uil uil-save"></i> Update Brand
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
                        <h3>No Brands Found</h3>
                        <p>There are no brands in the database. Add your first brand using the form above.</p>
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

        // Search functionality
        document.querySelector('.search-box input').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.brands-table tbody tr');
            
            rows.forEach(row => {
                // Skip the edit form rows
                if (row.querySelector('.edit-form-container')) return;
                
                const brandName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (brandName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>