<?php
$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch gender counts
$query = "SELECT gender, COUNT(*) as count FROM users GROUP BY gender";
$result = $conn->query($query);

$maleCount = 0;
$femaleCount = 0;
$othersCount = 0;

while ($row = $result->fetch_assoc()) {
    if (strtolower($row['gender']) === 'male') {
        $maleCount = $row['count'];
    } elseif (strtolower($row['gender']) === 'female') {
        $femaleCount = $row['count'];
    } else {
        $othersCount += $row['count'];
    }
}

// Fetch registration data by month
$registrationQuery = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    SUM(CASE WHEN LOWER(gender) = 'male' THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN LOWER(gender) = 'female' THEN 1 ELSE 0 END) as female_count,
    SUM(CASE WHEN LOWER(gender) NOT IN ('male', 'female') OR gender IS NULL THEN 1 ELSE 0 END) as other_count,
    COUNT(*) as total
FROM users 
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month ASC";
$registrationResult = $conn->query($registrationQuery);

$registrationData = [];
$labels = [];
$maleData = [];
$femaleData = [];
$otherData = [];
$totalData = [];

while ($row = $registrationResult->fetch_assoc()) {
    $labels[] = date('M Y', strtotime($row['month']));
    $maleData[] = $row['male_count'];
    $femaleData[] = $row['female_count'];
    $otherData[] = $row['other_count'];
    $totalData[] = $row['total'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --male-color: #4e73df;
            --female-color: #f6c23e;
            --other-color: #1cc88a;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
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
            --male-color: #3498db;
            --female-color: #e84393;
            --other-color: #00b894;
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
        nav .logo-name {
            display: flex;
            align-items: center;
        }
        nav .logo-image {
            display: flex;
            justify-content: center;
            min-width: 45px;
        }
        nav .logo-image i {
            font-size: 30px;
            color: var(--primary-color);
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
            color: var(--black-light-color);
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
        
        /* === Custom Scroll Bar CSS === */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--panel-color); /* Match panel color */
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color); /* Use primary color */
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0b3cc1; /* Slightly darker shade of primary color */
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        .top .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
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
        .dashboard .dash-content {
            padding-top: 70px;
        }
        .dash-content .title {
            display: flex;
            align-items: center;
            margin: 30px 0;
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
        
        /* Analytics Dashboard Styles */
        .analytics-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .analytics-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--panel-color);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
            color: white;
        }
        
        .male-icon {
            background-color: var(--male-color);
        }
        
        .female-icon {
            background-color: var(--female-color);
        }
        
        .other-icon {
            background-color: var(--other-color);
        }
        
        .total-icon {
            background-color: var(--primary-color);
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-title {
            font-size: 14px;
            color: var(--black-light-color);
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .stat-change {
            font-size: 12px;
            display: flex;
            align-items: center;
        }
        
        .change-up {
            color: var(--success-color);
        }
        
        .change-down {
            color: var(--danger-color);
        }
        
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .chart-card {
            background: var(--panel-color);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title-container {
            display: flex;
            align-items: center;
        }
        
        .chart-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: white;
        }
        
        .chart-title {
            font-size: 18px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .chart-actions {
            display: flex;
            gap: 10px;
        }
        
        .chart-action-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .chart-action-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }
        
        .chart-wrapper {
            width: 100%;
            height: 300px;
            position: relative;
        }
        
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }
        
        .legend-male {
            background-color: var(--male-color);
        }
        
        .legend-female {
            background-color: var(--female-color);
        }
        
        .legend-other {
            background-color: var(--other-color);
        }
        
        .legend-text {
            font-size: 12px;
            color: var(--black-light-color);
        }
        
        /* Responsive Styles */
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
        
        @media (max-width: 768px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .chart-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
        
        @media (max-width: 480px) {
            .analytics-title {
                font-size: 22px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            
            .stat-value {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
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
                    <a href="analytics_dashboard.php" class="active">
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
                <input type="text" placeholder="Search analytics..." />
            </div>
            <div class="user-info">
                <i class="uil uil-bell" style="font-size: 22px; color: var(--text-color); cursor: pointer;"></i>
                <i class="uil uil-envelope" style="font-size: 22px; color: var(--text-color); cursor: pointer;"></i>
                <div class="user-avatar">AD</div>
                <span style="color: var(--text-color);">Admin</span>
            </div>
        </div>
        
        <div class="dash-content">
            <div class="analytics-container">
                <div class="analytics-header">
                    <h1 class="analytics-title">User Analytics Dashboard</h1>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon male-icon">
                            <i class="uil uil-mars"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Male Users</div>
                            <div class="stat-value"><?= number_format($maleCount) ?></div>
                            <div class="stat-change">
                                <i class="uil uil-arrow-up"></i>
                                <span class="change-up">12% from last month</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon female-icon">
                            <i class="uil uil-venus"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Female Users</div>
                            <div class="stat-value"><?= number_format($femaleCount) ?></div>
                            <div class="stat-change">
                                <i class="uil uil-arrow-up"></i>
                                <span class="change-up">8% from last month</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon other-icon">
                            <i class="uil uil-transgender"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Other Users</div>
                            <div class="stat-value"><?= number_format($othersCount) ?></div>
                            <div class="stat-change">
                                <i class="uil uil-arrow-up"></i>
                                <span class="change-up">5% from last month</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon total-icon">
                            <i class="uil uil-users-alt"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-title">Total Users</div>
                            <div class="stat-value"><?= number_format($maleCount + $femaleCount + $othersCount) ?></div>
                            <div class="stat-change">
                                <i class="uil uil-arrow-up"></i>
                                <span class="change-up">10% from last month</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Grid -->
                <div class="chart-grid">
                    <!-- Gender Distribution Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title-container">
                                <div class="chart-icon male-icon">
                                    <i class="uil uil-chart-pie"></i>
                                </div>
                                <h3 class="chart-title">Gender Distribution</h3>
                            </div>
                            <div class="chart-actions">
                                <button class="chart-action-btn"><i class="uil uil-ellipsis-h"></i></button>
                            </div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="genderDistributionChart"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color legend-male"></div>
                                <span class="legend-text">Male (<?= $maleCount ?>)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color legend-female"></div>
                                <span class="legend-text">Female (<?= $femaleCount ?>)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color legend-other"></div>
                                <span class="legend-text">Other (<?= $othersCount ?>)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Registration Trends Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title-container">
                                <div class="chart-icon female-icon">
                                    <i class="uil uil-chart-bar"></i>
                                </div>
                                <h3 class="chart-title">Registration Trends</h3>
                            </div>
                            <div class="chart-actions">
                                <button class="chart-action-btn"><i class="uil uil-ellipsis-h"></i></button>
                            </div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="registrationTrendsChart"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color legend-male"></div>
                                <span class="legend-text">Male</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color legend-female"></div>
                                <span class="legend-text">Female</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color legend-other"></div>
                                <span class="legend-text">Other</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Sidebar toggle functionality
        const body = document.querySelector('body'),
              sidebar = body.querySelector('nav'),
              toggle = body.querySelector(".toggle"),
              searchBtn = body.querySelector(".search-box"),
              modeSwitch = body.querySelector(".toggle-switch"),
              modeText = body.querySelector(".mode-text");

        const sidebarToggle = document.querySelector('.sidebar-toggle');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('close');
        });

        // Dark mode toggle
        const modeToggle = body.querySelector(".mode-toggle");
        modeToggle.addEventListener("click", () => {
            body.classList.toggle("dark");
            
            // Update charts when theme changes
            updateChartsForTheme();
        });

        // Function to update charts when theme changes
        function updateChartsForTheme() {
            const isDarkMode = body.classList.contains('dark');
            
            // Update text colors based on theme
            const textColor = isDarkMode ? '#ccc' : '#666';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            
            // Update all charts
            if (window.genderDistributionChart) {
                window.genderDistributionChart.options.plugins.legend.labels.color = textColor;
                window.genderDistributionChart.update();
            }
            
            if (window.registrationTrendsChart) {
                window.registrationTrendsChart.options.scales.x.grid.color = gridColor;
                window.registrationTrendsChart.options.scales.y.grid.color = gridColor;
                window.registrationTrendsChart.options.scales.x.ticks.color = textColor;
                window.registrationTrendsChart.options.scales.y.ticks.color = textColor;
                window.registrationTrendsChart.update();
            }
        }

        // Gender Distribution Chart (Doughnut)
        const genderCtx = document.getElementById('genderDistributionChart').getContext('2d');
        window.genderDistributionChart = new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female', 'Other'],
                datasets: [{
                    data: [<?= $maleCount ?>, <?= $femaleCount ?>, <?= $othersCount ?>],
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(28, 200, 138, 0.8)'
                    ],
                    borderColor: [
                        'rgba(78, 115, 223, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(28, 200, 138, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: body.classList.contains('dark') ? '#ccc' : '#666',
                            font: {
                                size: 12
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '70%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Registration Trends Chart (Line)
        const trendsCtx = document.getElementById('registrationTrendsChart').getContext('2d');
        window.registrationTrendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Male',
                        data: <?= json_encode($maleData) ?>,
                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Female',
                        data: <?= json_encode($femaleData) ?>,
                        backgroundColor: 'rgba(246, 194, 62, 0.2)',
                        borderColor: 'rgba(246, 194, 62, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Other',
                        data: <?= json_encode($otherData) ?>,
                        backgroundColor: 'rgba(28, 200, 138, 0.2)',
                        borderColor: 'rgba(28, 200, 138, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: body.classList.contains('dark') ? '#ccc' : '#666'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: body.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            color: body.classList.contains('dark') ? '#ccc' : '#666'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: body.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            color: body.classList.contains('dark') ? '#ccc' : '#666'
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    </script>
</body>
</html>