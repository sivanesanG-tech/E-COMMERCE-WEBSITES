<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get all order details
$query = "
    SELECT 
        o.order_id, 
        o.product_name, 
        o.quantity, 
        o.size, 
        o.final_amount, 
        o.user_name, 
        o.payment_method, 
        o.product_image, 
        o.address,
        o.order_date,
        o.status
    FROM orders o
    ORDER BY o.order_date DESC
";

$result = $conn->query($query);

// Get order statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(final_amount) as total FROM orders")->fetch_assoc()['total'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];

// Check if there are any orders
$orders = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - Order Management</title>
    <!-- Unicons CSS -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap");
        
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        
        :root {
            /* Color Variables */
            --primary-color: #2ecc43;
            --panel-color: #fff;
            --text-color: #000;
            --black-light-color: #707070;
            --border-color: #e6e5e5;
            --toggle-color: #ddd;
            --box1-color: #4da3ff;
            --box2-color: #ffe6ac;
            --box3-color: #e7d1fc;
            --title-icon-color: #fff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --sidebar-icon-color: #5a5a5a;
            
            /* Transition Variables */
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
        
        /* Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 12px;
            transition: var(--tran-03);
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #0b3cc1;
        }
        
        body.dark::-webkit-scrollbar-thumb:hover {
            background: #3a3b3c;
        }
        
        /* Navigation Styles */
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
            z-index: 100;
        }
        
        nav.close {
            width: 73px;
        }
        
        .logo-name {
            display: flex;
            align-items: center;
            padding: 10px 0;
        }
        
        .logo-image {
            display: flex;
            justify-content: center;
            min-width: 45px;
        }
        
        .logo-image i {
            font-size: 30px;
            color: var(--primary-color);
        }
        
        .logo_name {
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
        
        .menu-items {
            margin-top: 40px;
            height: calc(100% - 90px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .nav-links {
            list-style: none;
        }
        
        .nav-links li a {
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
        
        body.dark .nav-links li a:hover:before {
            background-color: var(--text-color);
        }
        
        .nav-links li a i {
            font-size: 22px;
            min-width: 45px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sidebar-icon-color);
        }
        
        .nav-links li a .link-name {
            font-size: 16px;
            font-weight: 400;
            color: var(--black-light-color);
            transition: var(--tran-05);
        }
        
        nav.close .nav-links li a .link-name {
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
        
        .nav-links li a.active {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .logout-mode {
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }
        
        .mode {
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        
        .mode-toggle {
            position: absolute;
            right: 14px;
            height: 50px;
            min-width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .switch {
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
        
        /* Dashboard Styles */
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
        
        .top {
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        nav.close ~ .dashboard .top {
            left: 73px;
            width: calc(100% - 73px);
        }
        
        .sidebar-toggle {
            font-size: 26px;
            color: var(--text-color);
            cursor: pointer;
        }
        
        .search-box {
            position: relative;
            height: 45px;
            max-width: 600px;
            width: 100%;
            margin: 0 30px;
        }
        
        .search-box input {
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
        
        .search-box i {
            position: absolute;
            left: 15px;
            font-size: 22px;
            z-index: 10;
            top: 50%;
            transform: translateY(-50%);
            color: var(--black-light-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .notification-icon,
        .message-icon {
            font-size: 22px;
            color: var(--text-color);
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .username {
            color: var(--text-color);
        }
        
        .dash-content {
            padding-top: 70px;
        }
        
        .title {
            display: flex;
            align-items: center;
            margin: 30px 0;
        }
        
        .title i {
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
        
        .title .text {
            font-size: 24px;
            font-weight: 500;
            color: var(--text-color);
            margin-left: 10px;
        }
        
        /* Stats Container */
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background: var(--panel-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
            transition: var(--tran-03);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card h3 {
            font-size: 16px;
            color: var(--black-light-color);
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .stat-card.total-revenue {
            border-left-color: var(--success-color);
        }
        
        .stat-card.pending-orders {
            border-left-color: var(--warning-color);
        }
        
        /* Order Container */
        .order-container {
            background-color: var(--panel-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .order-container h2 {
            font-size: 20px;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        /* Order Table */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .order-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
            position: sticky;
            top: 70px;
        }
        
        .order-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
            vertical-align: middle;
        }
        
        .order-table tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .order-table tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .product-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid var(--border-color);
        }
        
        .product-name {
            font-weight: 500;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: var(--warning-color);
        }
        
        .status-completed {
            background-color: rgba(40, 167, 69, 0.2);
            color: var(--success-color);
        }
        
        .status-cancelled {
            background-color: rgba(220, 53, 69, 0.2);
            color: var(--danger-color);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: var(--tran-03);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            white-space: nowrap;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-info {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: #000;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* Order Details */
        .order-details {
            display: none;
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .order-details.active {
            display: block;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 500;
            width: 150px;
            color: var(--black-light-color);
        }
        
        .detail-value {
            flex: 1;
        }
        
        /* No Orders Message */
        .no-orders {
            text-align: center;
            padding: 40px;
            color: var(--black-light-color);
        }
        
        .no-orders i {
            font-size: 50px;
            margin-bottom: 15px;
            color: var(--black-light-color);
        }
        
        .no-orders p {
            font-size: 18px;
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .stats-container {
                flex-direction: column;
            }
            
            .stat-card {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .order-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 1000px) {
            nav {
                width: 73px;
            }
            
            nav.close {
                width: 250px;
            }
            
            nav .logo_name,
            nav.close .logo_name,
            nav li a .link-name,
            nav.close li a .link-name {
                opacity: 0;
                pointer-events: none;
            }
            
            nav.close .logo_name,
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
            
            nav ~ .dashboard .top,
            nav.close ~ .dashboard .top {
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
            
            nav ~ .dashboard {
                left: 0;
                width: 100%;
            }
            
            nav.close ~ .dashboard {
                left: 73px;
                width: calc(100% - 73px);
            }
            
            nav ~ .dashboard .top,
            nav.close ~ .dashboard .top {
                left: 0;
                width: 100%;
            }
            
            .search-box {
                margin: 0 10px;
            }
            
            .user-info {
                gap: 10px;
            }
            
            .notification-icon,
            .message-icon {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav container">
        <div class="logo-name">
            <div class="logo-image">
                <i class="uil uil-user-md"></i>
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
                    <a href="order_dashboard.php" class="active">
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
    
    <!-- Main Dashboard Content -->
    <section class="dashboard">
        <!-- Top Bar -->
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            
            <div class="search-box">
                <i class="uil uil-search"></i>
                <input type="text" placeholder="Search orders..." id="orderSearch" />
            </div>
            
            <div class="user-info">
                <i class="uil uil-bell notification-icon"></i>
                <i class="uil uil-envelope message-icon"></i>
                <div class="user-avatar">AD</div>
                <span class="username">Admin</span>
            </div>
        </div>
        
        <!-- Dashboard Content -->
        <div class="dash-content">
            <!-- Page Title -->
            <div class="title">
                <i class="uil uil-receipt"></i>
                <span class="text">Order Management</span>
            </div>
            
            <!-- Order Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                </div>
                <div class="stat-card total-revenue">
                    <h3>Total Revenue</h3>
                    <div class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></div>
                </div>
                <div class="stat-card pending-orders">
                    <h3>Pending Orders</h3>
                    <div class="stat-value"><?php echo $pending_orders; ?></div>
                </div>
            </div>
            
            <!-- Order List -->
            <div class="order-container">
                <h2>Recent Orders</h2>
                
                <?php if (!empty($orders)): ?>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td class="product-cell">
                                    <img src="<?php echo htmlspecialchars($order['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($order['product_name']); ?>" 
                                         class="product-image">
                                    <div class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></div>
                                </td>
                                <td>₹<?php echo number_format($order['final_amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn btn-info" onclick="toggleOrderDetails('details-<?php echo $order['order_id']; ?>')">
                                            <i class="uil uil-eye"></i> View
                                        </button>
                                        <?php if ($order['status'] == 'pending'): ?>
                                        <button class="action-btn btn-success" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'completed')">
                                            <i class="uil uil-check-circle"></i> Complete
                                        </button>
                                        <button class="action-btn btn-danger" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'cancelled')">
                                            <i class="uil uil-times-circle"></i> Cancel
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7">
                                    <div class="order-details" id="details-<?php echo $order['order_id']; ?>">
                                        <div class="detail-row">
                                            <div class="detail-label">Order ID:</div>
                                            <div class="detail-value">#<?php echo htmlspecialchars($order['order_id']); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Customer:</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($order['user_name']); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Product:</div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($order['product_name']); ?>
                                                (Qty: <?php echo htmlspecialchars($order['quantity']); ?>, 
                                                Size: <?php echo htmlspecialchars($order['size']); ?>)
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Amount:</div>
                                            <div class="detail-value">₹<?php echo number_format($order['final_amount'], 2); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Payment Method:</div>
                                            <div class="detail-value"><?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Order Date:</div>
                                            <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Status:</div>
                                            <div class="detail-value">
                                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Shipping Address:</div>
                                            <div class="detail-value"><?php echo nl2br(htmlspecialchars($order['address'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="uil uil-exclamation-triangle"></i>
                        <p>No orders found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        // Toggle dark/light mode
        const body = document.querySelector("body");
        const modeToggle = document.querySelector(".mode-toggle");
        const sidebar = document.querySelector("nav");
        const sidebarToggle = document.querySelector(".sidebar-toggle");

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

        // Toggle sidebar
        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            
            // Save sidebar state
            if (sidebar.classList.contains("close")) {
                localStorage.setItem("sidebar", "closed");
            } else {
                localStorage.setItem("sidebar", "open");
            }
        });

        // Check for saved sidebar state
        let getSidebar = localStorage.getItem("sidebar");
        if (getSidebar && getSidebar === "closed") {
            sidebar.classList.add("close");
        }

        // Toggle order details
        function toggleOrderDetails(id) {
            const details = document.getElementById(id);
            details.classList.toggle('active');
            
            // Scroll to the details if opening
            if (details.classList.contains('active')) {
                details.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        // Search functionality
        document.getElementById('orderSearch').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.order-table tbody tr');
            
            rows.forEach(row => {
                // Skip the details rows
                if (row.querySelector('.order-details')) return;
                
                const orderId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const customer = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const product = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const status = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                
                if (orderId.includes(searchTerm) || 
                    customer.includes(searchTerm) || 
                    product.includes(searchTerm) ||
                    status.includes(searchTerm)) {
                    row.style.display = '';
                    // Show the corresponding details row if it exists
                    const nextRow = row.nextElementSibling;
                    if (nextRow && nextRow.querySelector('.order-details')) {
                        nextRow.style.display = '';
                    }
                } else {
                    row.style.display = 'none';
                    // Hide the corresponding details row if it exists
                    const nextRow = row.nextElementSibling;
                    if (nextRow && nextRow.querySelector('.order-details')) {
                        nextRow.style.display = 'none';
                    }
                }
            });
        });

        // Update order status (simulated - would need AJAX in real implementation)
        function updateOrderStatus(orderId, status) {
            if (confirm(`Are you sure you want to mark order #${orderId} as ${status}?`)) {
                // In a real application, you would make an AJAX call here
                // For now, we'll just show an alert and simulate the change
                alert(`Order #${orderId} status updated to ${status}. In a real application, this would update the database.`);
                
                // Simulate the status change in the UI
                const statusBadge = document.querySelector(`tr:has(td:first-child:contains("#${orderId}")) .status-badge`);
                if (statusBadge) {
                    // Remove all status classes
                    statusBadge.classList.remove('status-pending', 'status-completed', 'status-cancelled');
                    
                    // Add the new status class
                    statusBadge.classList.add(`status-${status}`);
                    
                    // Update the text
                    statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    
                    // If status is completed or cancelled, remove the action buttons
                    if (status === 'completed' || status === 'cancelled') {
                        const actionButtons = statusBadge.closest('tr').querySelector('.action-buttons');
                        if (actionButtons) {
                            actionButtons.innerHTML = `
                                <button class="action-btn btn-info" onclick="toggleOrderDetails('details-${orderId}')">
                                    <i class="uil uil-eye"></i> View
                                </button>
                            `;
                        }
                    }
                }
            }
        }

        // Highlight current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links li a');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>