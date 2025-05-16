<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get delivery person's city
if (empty($_SESSION['location'])) {
    echo "<div class='no-orders'>
            <i class='fas fa-box-open'></i>
            <h3>Location Not Set</h3>
            <p>Please update your location in your profile to view delivery details.</p>
          </div>";
    $conn->close();
    exit();
}

$user_city = explode(',', $_SESSION['location'])[0] ?? '';

// Fetch orders for the delivery person's city
$query = "
    SELECT 
        id, 
        product_name, 
        quantity, 
        size, 
        final_amount, 
        user_name, 
        payment_method, 
        product_image, 
        address, 
        delivery_status,
        delivery_notes,
        DATE_FORMAT(order_date, '%d %b %Y %h:%i %p') as formatted_date
    FROM orders 
    WHERE address LIKE ? 
    ORDER BY 
        CASE 
            WHEN delivery_status = 'Pending' THEN 1
            WHEN delivery_status = 'In Transit' THEN 2
            WHEN delivery_status = 'Cannot Reach Customer' THEN 3
            WHEN delivery_status = 'Cancelled' THEN 4
            ELSE 5
        END,
        order_date DESC
";
$stmt = $conn->prepare($query);
$city_param = "%$user_city%";
$stmt->bind_param("s", $city_param);
$stmt->execute();
$result = $stmt->get_result();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['id']);
    $new_status = $_POST['delivery_status'];
    $delivery_notes = isset($_POST['delivery_notes']) ? trim($_POST['delivery_notes']) : '';

    // Get current status of the specific order
    $check_query = "SELECT delivery_status, delivery_notes FROM orders WHERE id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->bind_result($current_status, $existing_notes);
    $stmt->fetch();
    $stmt->close();

    // Define allowed status transitions
    $allowed_transitions = [
        'Pending' => ['In Transit', 'Cancelled'],
        'In Transit' => ['Delivered', 'Cannot Reach Customer'],
        'Cannot Reach Customer' => ['In Transit', 'Cancelled'],
        'Cancelled' => [], // Once cancelled, no further updates
        'Delivered' => [] // Once delivered, no further updates
    ];

    // Validate transition
    $is_valid = isset($allowed_transitions[$current_status]) && 
                in_array($new_status, $allowed_transitions[$current_status]);

    if ($is_valid) {
        // Prepare notes - combine with existing if needed
        $final_notes = $existing_notes;
        if (!empty($delivery_notes)) {
            $timestamp = date('Y-m-d H:i:s');
            $note_entry = "\n\n[$timestamp] - $delivery_notes";
            $final_notes .= $note_entry;
        }

        // Update the specific order's delivery status and notes
        $update_query = "UPDATE orders SET delivery_status = ?, status = ?, delivery_notes = ?";
        $status_value = $new_status === 'Cancelled' ? 'Cancelled' : $new_status;
        if ($new_status === 'Delivered') {
            $update_query .= ", delivery_time = NOW()";
        }
        $update_query .= " WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $new_status, $status_value, $final_notes, $order_id);

        if ($stmt->execute()) {
            $_SESSION['status_message'] = [
                'type' => 'success',
                'text' => "Order #$order_id has been successfully updated to $new_status."
            ];
        } else {
            $_SESSION['status_message'] = [
                'type' => 'error',
                'text' => "Error updating order #$order_id: " . $stmt->error
            ];
        }
        $stmt->close();
    } else {
        $_SESSION['status_message'] = [
            'type' => 'error',
            'text' => "Invalid status transition for order #$order_id from $current_status to $new_status."
        ];
    }

    header('Location: delivery.php');
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a1b9a;
            --primary-light: #9c4dcc;
            --primary-dark: #38006b;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --info: #2196f3;
            --light: #f5f5f5;
            --dark: #212121;
            --gray: #757575;
            --electric-purple: #8a2be2;
            --neon-blue: #1e90ff;
            --lightning-yellow: #f5a623;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1rem 2rem;
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, 
                var(--electric-purple), 
                var(--neon-blue), 
                var(--lightning-yellow),
                var(--electric-purple));
            background-size: 300% 100%;
            animation: lightning 3s linear infinite;
        }

        @keyframes lightning {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            position: relative;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .navbar a:hover {
            color: #ffeb3b;
            transform: translateY(-2px);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .navbar a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #ffeb3b;
            transition: width 0.3s ease;
        }

        .navbar a:hover::after {
            width: 100%;
        }

        .order-details-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .order-details-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, 
                var(--electric-purple), 
                var(--neon-blue), 
                var(--lightning-yellow));
            background-size: 200% 100%;
            animation: lightning 3s linear infinite;
        }

        .page-title {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 10px;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .order-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 2rem;
        }

        .order-table th {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .order-table th:first-child {
            border-top-left-radius: 8px;
        }

        .order-table th:last-child {
            border-top-right-radius: 8px;
        }

        .order-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }

        .order-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .order-table tr:hover {
            background-color: #f1f1f1;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border: 2px solid white;
        }

        .product-image:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 100px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background-color: #fff3e0;
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        .status-transit {
            background-color: #e3f2fd;
            color: var(--info);
            border-left: 4px solid var(--info);
        }

        .status-delivered {
            background-color: #e8f5e9; /* Light green background */
            color: #28a745; /* Green color */
            border-left: 4px solid #28a745;
        }

        .status-cancelled {
            background-color: #ffebee;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .status-cannot-reach {
            background-color: #ffebee;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        .status-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .status-form select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background-color: white;
            font-size: 0.9rem;
            min-width: 180px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .status-form select:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(154, 27, 154, 0.2);
        }

        .status-form textarea {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-family: inherit;
            resize: vertical;
            min-height: 40px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .status-form textarea:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(154, 27, 154, 0.2);
        }

        .status-form button {
            padding: 8px 16px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .status-form button:hover {
            background: linear-gradient(to right, var(--primary-light), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .status-form button:active {
            transform: translateY(0);
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin: 2rem 0;
        }

        .no-orders i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-light);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .status-message::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, 
                var(--electric-purple), 
                var(--neon-blue), 
                var(--lightning-yellow));
            background-size: 200% 100%;
            animation: lightning 3s linear infinite;
        }

        .success {
            background-color: #e8f5e9;
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .error {
            background-color: #ffebee;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .info {
            background-color: #e3f2fd;
            color: var(--info);
            border-left: 4px solid var(--info);
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .notes-toggle {
            color: var(--primary);
            cursor: pointer;
            font-size: 0.8rem;
            margin-top: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
            padding: 5px;
            border-radius: 4px;
        }

        .notes-toggle:hover {
            background-color: rgba(106, 27, 154, 0.1);
        }

        .delivery-notes {
            margin-top: 8px;
            padding: 8px;
            background-color: #f5f5f5;
            border-radius: 4px;
            font-size: 0.85rem;
            display: none;
            white-space: pre-line;
            border-left: 3px solid var(--primary-light);
        }

        .delivery-notes.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .order-date {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 5px;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
        }

        .customer-name {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .payment-method {
            font-size: 0.8rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .payment-method i {
            color: var(--primary);
        }

        /* Animation for status change */
        @keyframes statusChange {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .status-updated {
            animation: statusChange 0.5s ease;
        }

        /* Responsive styles */
        @media (max-width: 1200px) {
            .order-table {
                display: block;
                overflow-x: auto;
            }
            
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }
            
            .navbar nav {
                margin-top: 1rem;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .navbar a {
                margin: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .order-details-container {
                padding: 1rem;
            }
            
            .status-form {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .status-form select, 
            .status-form textarea {
                width: 100%;
            }
            
            .status-form button {
                width: 100%;
                justify-content: center;
            }
        }

        /* Glow effect for important elements */
        .glow {
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 5px rgba(154, 27, 154, 0.5);
            }
            to {
                box-shadow: 0 0 15px rgba(154, 27, 154, 0.8);
            }
        }

        /* Floating animation for delivery truck icon */
        .fa-truck {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0); }
        }
        .status-cancelled-by-customer {
            background-color: #ffebee;
            color: #c2185b;
            border-left: 4px solid #c2185b;
        }
        
        .status-update-disabled {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 8px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
        </div>
        <nav>
            <a href="orders.php"><i class="fas fa-clipboard-list"></i> My Orders</a>
            <a href="delivery_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    <div class="order-details-container">
        <h1 class="page-title">
            <i class="fas fa-truck"></i> Delivery Management
            <span style="font-size: 0.8rem; margin-left: auto; color: var(--gray);">
                Your Delivery Area: <strong><?php echo htmlspecialchars($user_city); ?></strong>
            </span>
        </h1>

        <?php if (isset($_SESSION['status_message'])): ?>
            <div class="status-message <?php echo $_SESSION['status_message']['type']; ?>">
                <i class="fas fa-<?php echo $_SESSION['status_message']['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $_SESSION['status_message']['text']; ?>
            </div>
            <?php unset($_SESSION['status_message']); ?>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Details</th>
                        <th>Amount</th>
                        <th>Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): 
                        $status_class = 'status-' . strtolower(str_replace(' ', '-', $order['delivery_status']));
                    ?>
                        <tr>
                            <td>
                                #<?php echo htmlspecialchars($order['id']); ?>
                                <div class="order-date"><?php echo htmlspecialchars($order['formatted_date']); ?></div>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name"><?php echo htmlspecialchars($order['user_name']); ?></span>
                                    <span class="payment-method">
                                        <i class="fas fa-<?php echo strtolower($order['payment_method']) === 'credit card' ? 'credit-card' : 'money-bill-wave'; ?>"></i>
                                        <?php echo htmlspecialchars($order['payment_method']); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <img src="<?php echo htmlspecialchars($order['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($order['product_name']); ?>" 
                                     class="product-image">
                                <div><?php echo htmlspecialchars($order['product_name']); ?></div>
                            </td>
                            <td>
                                Qty: <?php echo htmlspecialchars($order['quantity']); ?><br>
                                Size: <?php echo htmlspecialchars($order['size']); ?>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--primary-dark);">
                                    ₹<?php echo number_format($order['final_amount'], 2); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                                <?php if (!empty($order['delivery_notes'])): ?>
                                    <div class="notes-toggle" onclick="toggleNotes(this)">
                                        <i class="fas fa-notes"></i> View Delivery Notes
                                    </div>
                                    <div class="delivery-notes">
                                        <?php echo nl2br(htmlspecialchars($order['delivery_notes'])); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($order['delivery_status']); ?>
                                </div>
                                <?php if (!in_array($order['delivery_status'], ['Cancelled', 'Delivered'])): ?>
                                    <?php if ($order['delivery_status'] === 'In Transit'): ?>
                                        <form method="POST" action="update_status.php" class="status-form">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <select name="delivery_status" required>
                                                <option value="">Select Status</option>
                                                <option value="Delivered">Delivered</option>
                                                <option value="Cannot Reach Customer">Cannot Reach Customer</option>
                                            </select>
                                            <textarea name="delivery_notes" placeholder="Add delivery notes"></textarea>
                                            <button type="submit" name="update_status" class="glow">
                                                <i class="fas fa-save"></i> Update Status
                                            </button>
                                        </form>
                                    <?php elseif ($order['delivery_status'] === 'Pending'): ?>
                                        <form method="POST" action="update_status.php" class="status-form">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <select name="delivery_status" required>
                                                <option value="">Select Status</option>
                                                <option value="In Transit">Mark as In Transit</option>
                                                <option value="Cancelled">Cancel Order</option>
                                            </select>
                                            <button type="submit" name="update_status" class="glow">
                                                <i class="fas fa-play"></i> Start Delivery
                                            </button>
                                        </form>
                                    <?php elseif ($order['delivery_status'] === 'Cannot Reach Customer'): ?>
                                        <form method="POST" action="update_status.php" class="status-form">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <select name="delivery_status" required>
                                                <option value="">Select Action</option>
                                                <option value="In Transit">Try Again</option>
                                                <option value="Cancelled">Cancel Order</option>
                                            </select>
                                            <textarea name="delivery_notes" placeholder="Add delivery notes"></textarea>
                                            <button type="submit" name="update_status" class="glow">
                                                <i class="fas fa-save"></i> Update Status
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="status-update-disabled">
                                        <i class="fas fa-info-circle"></i> This order cannot be updated further
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-orders">
                <i class="fas fa-box-open"></i>
                <h3>No Orders Found in Your Delivery Area</h3>
                <p>Currently there are no orders assigned to your delivery location (<?php echo htmlspecialchars($user_city); ?>).</p>
                <p>Check back later or contact support if you believe this is incorrect.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Toggle delivery notes visibility
        function toggleNotes(element) {
            const notes = element.nextElementSibling;
            notes.classList.toggle('show');
            
            // Toggle icon and text
            const icon = element.querySelector('i');
            if (notes.classList.contains('show')) {
                icon.classList.replace('fa-notes', 'fa-chevron-up');
                element.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Delivery Notes';
            } else {
                icon.classList.replace('fa-chevron-up', 'fa-notes');
                element.innerHTML = '<i class="fas fa-notes"></i> View Delivery Notes';
            }
        }

        // Add animation when status is updated
        document.addEventListener('DOMContentLoaded', function() {
            const statusMessages = document.querySelectorAll('.status-message');
            statusMessages.forEach(msg => {
                setTimeout(() => {
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                }, 5000);
            });

            // Highlight status changes
            if (window.location.search.includes('updated')) {
                const statusBadges = document.querySelectorAll('.status-badge');
                statusBadges.forEach(badge => {
                    badge.classList.add('status-updated');
                    setTimeout(() => badge.classList.remove('status-updated'), 1000);
                });
            }

            // Add confirmation for critical actions
            const statusForms = document.querySelectorAll('.status-form');
            statusForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const select = this.querySelector('select');
                    if (select.value === 'Cancelled') {
                        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                            e.preventDefault();
                        }
                    }
                    
                    if (select.value === 'Cannot Reach Customer') {
                        const notes = this.querySelector('textarea');
                        if (notes && notes.value.trim() === '') {
                            alert('Please provide details about why you cannot reach the customer.');
                            e.preventDefault();
                        }
                    }
                });
            });
        });

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    </script>
</body>
</html>