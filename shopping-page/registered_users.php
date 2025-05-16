<?php
$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$query = "SELECT id, fullname, email, phone, gender, created_at FROM users";
$result = $conn->query($query);
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
      
      /* Table Styles */
      .table-container {
        margin-top: 30px;
        overflow-x: auto;
      }
      
      .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }
      
      .users-table th {
        background-color: var(--primary-color);
        color: white;
        padding: 12px 15px;
        text-align: left;
        font-weight: 500;
      }
      
      .users-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-color);
      }
      
      .users-table tr:nth-child(even) {
        background-color: rgba(0, 0, 0, 0.02);
      }
      
      .users-table tr:hover {
        background-color: rgba(0, 0, 0, 0.05);
      }
      
      .action-icons {
        display: flex;
        gap: 10px;
      }
      
      .action-icons a {
        color: var(--text-color);
        transition: var(--tran-03);
      }
      
      .action-icons a:hover {
        color: var(--primary-color);
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
      
      .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
      }
      
      .status-active {
        background-color: #e3f7e8;
        color: #28a745;
      }
      
      .status-inactive {
        background-color: #fde8e8;
        color: #dc3545;
      }
      
      /* Responsive Table */
      @media (max-width: 768px) {
        .users-table {
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
      @media (max-width: 780px) {
        .dash-content .boxes .box {
          width: calc(100% / 2 - 15px);
          margin-top: 15px;
        }
      }
      @media (max-width: 560px) {
        .dash-content .boxes .box {
          width: 100%;
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
      }
    </style>
    <title>Admin Dashboard - Registered Users</title>
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
            <a href="registered_users.php" class="active">
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
            <a href="customer_support.php">
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
          <input type="text" placeholder="Search users..." />
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
          <i class="uil uil-users-alt"></i>
          <span class="text">Registered Users</span>
        </div>
        
        <div class="table-container">
          <?php if ($result->num_rows > 0): ?>
            <table class="users-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Contact</th>
                  <th>Gender</th>
                  <th>Registered</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                  $initials = '';
                  $nameParts = explode(' ', $row['fullname']);
                  foreach ($nameParts as $part) {
                    $initials .= strtoupper(substr($part, 0, 1));
                  }
                ?>
                <tr>
                  <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                      <div class="user-avatar"><?= $initials ?></div>
                      <div>
                        <div style="font-weight: 500;"><?= htmlspecialchars($row['fullname']) ?></div>
                        <div style="font-size: 12px; color: var(--black-light-color);">ID: <?= htmlspecialchars($row['id']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div><?= htmlspecialchars($row['email']) ?></div>
                    <div style="font-size: 13px; color: var(--black-light-color);"><?= htmlspecialchars($row['phone']) ?></div>
                  </td>
                  <td>
                    <?php 
                      $genderIcon = '';
                      if ($row['gender'] == 'Male') {
                        $genderIcon = '<i class="uil uil-mars" style="color: #0e4bf1;"></i>';
                      } elseif ($row['gender'] == 'Female') {
                        $genderIcon = '<i class="uil uil-venus" style="color: #ff4081;"></i>';
                      } else {
                        $genderIcon = '<i class="uil uil-genderless" style="color: #666;"></i>';
                      }
                      echo $genderIcon . ' ' . htmlspecialchars($row['gender']);
                    ?>
                  </td>
                  <td>
                    <i class="uil uil-calendar-alt" style="margin-right: 5px;"></i>
                    <?= date('M j, Y', strtotime($row['created_at'])) ?>
                  </td>
                  <td><span class="status-badge status-active"><i class="uil uil-check-circle" style="margin-right: 5px;"></i>Active</span></td>
                  <td>
                    <div class="action-icons">
                      <a href="#" title="View"><i class="uil uil-eye"></i></a>
                      <a href="#" title="Edit"><i class="uil uil-edit"></i></a>
                      <a href="#" title="Delete"><i class="uil uil-trash-alt"></i></a>
                      <a href="#" title="Message"><i class="uil uil-envelope"></i></a>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div style="text-align: center; padding: 40px; color: var(--text-color);">
              <i class="uil uil-user-times" style="font-size: 50px; margin-bottom: 20px;"></i>
              <h3>No registered users found</h3>
              <p>When users register, they will appear here</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <script>
      const body = document.querySelector("body"),
        modeToggle = body.querySelector(".mode-toggle");
      sidebar = body.querySelector("nav");
      sidebarToggle = body.querySelector(".sidebar-toggle");
      
      let getMode = localStorage.getItem("mode");
      if (getMode && getMode === "dark") {
        body.classList.toggle("dark");
      }
      
      let getStatus = localStorage.getItem("status");
      if (getStatus && getStatus === "close") {
        sidebar.classList.toggle("close");
      }
      
      modeToggle.addEventListener("click", () => {
        body.classList.toggle("dark");
        if (body.classList.contains("dark")) {
          localStorage.setItem("mode", "dark");
        } else {
          localStorage.setItem("mode", "light");
        }
      });
      
      sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
        if (sidebar.classList.contains("close")) {
          localStorage.setItem("status", "close");
        } else {
          localStorage.setItem("status", "open");
        }
      });
      
      // Add search functionality
      document.querySelector('.search-box input').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.users-table tbody tr');
        
        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
      });
    </script>
  </body>
</html>
<?php $conn->close(); ?>