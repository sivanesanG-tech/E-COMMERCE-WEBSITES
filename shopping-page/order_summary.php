<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'shopping';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if the selected address ID is passed
if (!isset($_POST['selected_address'])) {
    header('Location: addresses.php');
    exit();
}

$selected_address_id = (int)$_POST['selected_address'];

// Fetch the selected address from the database
$query = "SELECT * FROM addresses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $selected_address_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Address not found.");
}

$address = $result->fetch_assoc();
$_SESSION['selected_address'] = $address;

// Calculate order totals
$total_amount = 0;
$total_discount = 0;
$platform_fee = 50;
$delivery_charge = 0;
$items_count = 0;

foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['price'] * $item['quantity'];
    $total_discount += ($item['price'] * 0.30) * $item['quantity'];
    $items_count += $item['quantity'];
}

$final_amount = $total_amount + $platform_fee + $delivery_charge - $total_discount;
$_SESSION['final_amount'] = $final_amount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary | YourStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --primary-hover: #27ae60;
            --secondary-color: #3498db;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .header h2 {
            color: var(--dark-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #ddd;
            z-index: 1;
            transform: translateY(-50%);
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ddd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .step.active .step-number {
            background: var(--primary-color);
        }

        .step.completed .step-number {
            background: var(--primary-color);
        }

        .step.completed .step-number::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .step-label {
            font-size: 0.9rem;
            color: #777;
        }

        .step.active .step-label {
            color: var(--dark-color);
            font-weight: bold;
        }

        .address-card {
            border: 1px solid #e0e0e0;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: var(--border-radius);
            background: white;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
        }

        .address-card:hover {
            border-color: var(--primary-color);
        }

        .address-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .address-card h3 i {
            margin-right: 0.5rem;
        }

        .address-card p {
            margin: 0.5rem 0;
            display: flex;
        }

        .address-card p strong {
            min-width: 120px;
            color: var(--dark-color);
        }

        .change-address-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .change-address-btn:hover {
            background: #2980b9;
        }

        .order-summary {
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        .order-summary table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .order-summary th {
            background: var(--dark-color);
            color: white;
            padding: 1rem;
            text-align: left;
        }

        .order-summary td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .order-summary tr:last-child td {
            border-bottom: none;
        }

        .order-summary tr:hover {
            background: #f5f5f5;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        .product-name {
            font-weight: 600;
            color: var(--dark-color);
        }

        .product-size {
            color: #777;
            font-size: 0.9rem;
        }

        .price {
            font-weight: 600;
        }

        .discount {
            color: var(--primary-color);
        }

        .summary-total {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-row.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark-color);
            border-top: 1px solid #e0e0e0;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-back {
            background: #f1f1f1;
            color: var(--dark-color);
        }

        .btn-back:hover {
            background: #e0e0e0;
        }

        .btn-continue {
            background: var(--primary-color);
            color: white;
        }

        .btn-continue:hover {
            background: var(--primary-hover);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .progress-steps {
                flex-wrap: wrap;
            }

            .step {
                width: 50%;
                margin-bottom: 1rem;
            }

            .address-card p {
                flex-direction: column;
            }

            .address-card p strong {
                margin-bottom: 0.2rem;
            }

            .btn-container {
                flex-direction: column-reverse;
                gap: 1rem;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-shopping-bag"></i> Order Summary</h2>

        <!-- Display Selected Address -->
        <div class="address-card">
            <h3><i class="fas fa-map-marker-alt"></i> <?php echo $address['address_type']; ?> Address</h3>
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($address['fullname']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($address['phone']); ?></p>
            <?php if (!empty($address['alt_phone'])): ?>
                <p><strong>Alternative Phone:</strong> <?php echo htmlspecialchars($address['alt_phone']); ?></p>
            <?php endif; ?>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($address['house_no'] . ', ' . $address['building_name'] . ', ' . $address['road_name'] . ', ' . $address['area_name']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($address['city']); ?></p>
            <p><strong>State:</strong> <?php echo htmlspecialchars($address['state']); ?></p>
            <p><strong>PIN Code:</strong> <?php echo htmlspecialchars($address['pincode']); ?></p>
            <?php if (!empty($address['landmark'])): ?>
                <p><strong>Landmark:</strong> <?php echo htmlspecialchars($address['landmark']); ?></p>
            <?php endif; ?>
            <button class="change-address-btn" onclick="window.location.href='address,.php'">
                <i class="fas fa-edit"></i> Change
            </button>
        </div>

        <!-- Display Order Summary -->
        <div class="order-summary">
            <h3><i class="fas fa-shopping-cart"></i> Order Details (<?php echo $items_count; ?> items)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <?php
                                    $web_image_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $item['image']);
                                    ?>
                                    <img src="<?php echo htmlspecialchars($web_image_path); ?>" alt="Product Image" class="product-image">
                                    <div>
                                        <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="product-size">Size: <?php echo htmlspecialchars($item['size']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="price">₹<?php echo number_format($item['price'], 2); ?></td>
                            <td class="discount">-₹<?php echo number_format($item['price'] * 0.30, 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td class="price">₹<?php echo number_format(($item['price'] * $item['quantity']) - ($item['price'] * 0.30 * $item['quantity']), 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="summary-total">
                <div class="summary-row">
                    <span>Subtotal (<?php echo $items_count; ?> items)</span>
                    <span>₹<?php echo number_format($total_amount, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Discount</span>
                    <span class="discount">-₹<?php echo number_format($total_discount, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Platform Fee</span>
                    <span>₹<?php echo number_format($platform_fee, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Charge</span>
                    <span>₹<?php echo number_format($delivery_charge, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span>₹<?php echo number_format($final_amount, 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="btn-container">
            <button class="btn btn-back" onclick="window.location.href='cart.php'">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </button>
            <form action="payment.php" method="POST">
                <button type="submit" class="btn btn-continue">
                    Proceed to Payment <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</body>
</html>