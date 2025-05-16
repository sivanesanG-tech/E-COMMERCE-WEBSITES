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

// Fetch all support messages with user details if available
$query = "SELECT sm.*, u.fullname 
          FROM support_messages sm
          LEFT JOIN users u ON sm.user_email = u.email
          ORDER BY sm.created_at DESC";
$result = $conn->query($query);

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    // Validate input data
    if (!isset($_POST['id']) || !isset($_POST['reply']) || trim($_POST['id']) === '' || trim($_POST['reply']) === '') {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid input. Message ID and reply are required.'];
    } else {
        $id = $conn->real_escape_string($_POST['id']);
        $reply = $conn->real_escape_string($_POST['reply']);

        $update_query = "UPDATE support_messages SET reply='$reply', replied_at=NOW(), status='replied' WHERE id='$id'";
        if ($conn->query($update_query)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Reply sent successfully.'];
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: ' . $conn->error];
        }
    }
}

// Handle message status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $update_query = "UPDATE support_messages SET status='$status' WHERE id='$id'";
    if ($conn->query($update_query)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Status updated successfully.'];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: ' . $conn->error];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support</title>
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
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --pending-color: #f39c12;
            --replied-color: #3498db;
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
            --success-color: #27ae60;
            --error-color: #c0392b;
            --pending-color: #d35400;
            --replied-color: #2980b9;
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
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f1f1;
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
        
        /* Support Messages Styles */
        .support-container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            background-color: var(--panel-color);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .message-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border-radius: 20px;
            background-color: var(--panel-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active, .filter-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .message-card {
            border: 1px solid var(--border-color);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: var(--panel-color);
            transition: all 0.3s ease;
        }
        
        .message-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .message-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .message-meta {
            display: flex;
            flex-direction: column;
        }
        
        .message-name {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .message-email {
            font-size: 13px;
            color: var(--black-light-color);
        }
        
        .message-date {
            font-size: 13px;
            color: var(--black-light-color);
        }
        
        .message-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: rgba(243, 156, 18, 0.2);
            color: var(--pending-color);
        }
        
        .status-replied {
            background-color: rgba(52, 152, 219, 0.2);
            color: var(--replied-color);
        }
        
        .status-resolved {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
        }
        
        .message-content {
            margin: 15px 0;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 5px;
            color: var(--text-color);
        }
        
        .message-reply {
            margin-top: 20px;
            padding: 15px;
            background-color: rgba(74, 144, 226, 0.1);
            border-radius: 5px;
            border-left: 3px solid var(--primary-color);
        }
        
        .reply-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .reply-date {
            font-size: 13px;
            color: var(--black-light-color);
        }
        
        .reply-form {
            margin-top: 20px;
        }
        
        .reply-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            resize: none;
            min-height: 100px;
            margin-bottom: 10px;
            background-color: var(--panel-color);
            color: var(--text-color);
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0b3cc1;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }
        
        .btn-outline:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .status-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 15px;
        }
        
        .status-form select {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
            background-color: var(--panel-color);
            color: var(--text-color);
        }
        
        .alert {
            padding: 12px 15px;
            margin: 0 0 20px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .no-messages {
            text-align: center;
            padding: 40px;
            color: var(--black-light-color);
        }
        
        .no-messages i {
            font-size: 50px;
            margin-bottom: 15px;
            color: var(--border-color);
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
            .dashboard .top {
                flex-wrap: wrap;
                padding: 10px;
            }
            .dashboard .top .search-box {
                order: 3;
                margin: 10px 0 0 0;
                max-width: 100%;
            }
            .message-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        @media (max-width: 480px) {
            .support-container {
                padding: 15px;
            }
            .message-filters {
                justify-content: center;
            }
            .btn {
                padding: 8px 15px;
                font-size: 14px;
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
                    <a href="customer-support.php" class="active">
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
                <input type="text" placeholder="Search support tickets..." id="searchInput" />
            </div>
            <div class="user-info">
                <i class="uil uil-bell" style="font-size: 22px; color: var(--text-color); cursor: pointer;"></i>
                <i class="uil uil-envelope" style="font-size: 22px; color: var(--text-color); cursor: pointer;"></i>
                <div class="user-avatar">AD</div>
                <span style="color: var(--text-color);">Admin</span>
            </div>
        </div>
        <div class="container">
        <h2>Customer Support</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="message-card">
                    <p><strong>From:</strong> <?php echo $row['user_email']; ?></p>
                    <p><strong>Message:</strong> <?php echo $row['message']; ?></p>
                    <?php if ($row['reply']): ?>
                        <p><strong>Reply:</strong> <?php echo $row['reply']; ?></p>
                        <p><strong>Replied At:</strong> <?php echo $row['replied_at']; ?></p>
                    <?php else: ?>
                        <form method="POST">
                            <textarea name="reply" rows="3" required placeholder="Write your reply here..."></textarea><br>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="reply_message">Send Reply</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No messages found.</p>
        <?php endif; ?>
    </div>
    <script>
        // Hide success or error messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.style.display = 'none');
        }, 5000);
    </script>
</body>

            </div>
        </div>
    </section>
    
    <script>
        // Sidebar toggle functionality
        const body = document.querySelector("body");
        const modeToggle = body.querySelector(".mode-toggle");
        const sidebar = body.querySelector("nav");
        const sidebarToggle = body.querySelector(".sidebar-toggle");
        
        // Get mode and status from localStorage
        let getMode = localStorage.getItem("mode");
        if (getMode && getMode === "dark") {
            body.classList.add("dark");
        }
        
        let getStatus = localStorage.getItem("status");
        if (getStatus && getStatus === "close") {
            sidebar.classList.add("close");
        }
        
        // Mode toggle
        modeToggle.addEventListener("click", () => {
            body.classList.toggle("dark");
            if (body.classList.contains("dark")) {
                localStorage.setItem("mode", "dark");
            } else {
                localStorage.setItem("mode", "light");
            }
        });
        
        // Sidebar toggle
        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            if (sidebar.classList.contains("close")) {
                localStorage.setItem("status", "close");
            } else {
                localStorage.setItem("status", "open");
            }
        });
        
        // Message filtering
        const filterButtons = document.querySelectorAll(".filter-btn");
        const messageCards = document.querySelectorAll(".message-card");
        
        filterButtons.forEach(button => {
            button.addEventListener("click", () => {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove("active"));
                button.classList.add("active");
                
                const filter = button.dataset.filter;
                
                // Filter messages
                messageCards.forEach(card => {
                    if (filter === "all" || card.dataset.status === filter) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });
        
        // Search functionality
        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("input", (e) => {
            const searchTerm = e.target.value.toLowerCase();
            
            messageCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        });
        
        // Mark as resolved buttons
        const markResolvedButtons = document.querySelectorAll(".mark-resolved");
        markResolvedButtons.forEach(button => {
            button.addEventListener("click", () => {
                const id = button.dataset.id;
                if (confirm("Are you sure you want to mark this ticket as resolved without replying?")) {
                    // Create a form and submit it
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "";
                    
                    const idInput = document.createElement("input");
                    idInput.type = "hidden";
                    idInput.name = "id";
                    idInput.value = id;
                    
                    const statusInput = document.createElement("input");
                    statusInput.type = "hidden";
                    statusInput.name = "status";
                    statusInput.value = "resolved";
                    
                    const submitInput = document.createElement("input");
                    submitInput.type = "hidden";
                    submitInput.name = "change_status";
                    submitInput.value = "1";
                    
                    form.appendChild(idInput);
                    form.appendChild(statusInput);
                    form.appendChild(submitInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
        
        // Hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll(".alert");
            alerts.forEach(alert => {
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
<?php
$conn->close();
?>