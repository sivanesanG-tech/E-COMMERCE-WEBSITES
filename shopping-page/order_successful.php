<?php
session_start();

// Check if the cart and selected address are set in the session
if (!isset($_SESSION['cart'])) {
    header('Location: cart.php'); // Redirect back to the cart page if the cart is empty
    exit();
}

if (!isset($_SESSION['selected_address'])) {
    header('Location: addresses.php'); // Redirect back if no address is selected
    exit();
}

// Check if username is set in the session
if (!isset($_SESSION['username'])) {
    $_SESSION['message'] = "User information is missing!";
    header('Location: login.php'); // Redirect to the correct login page
    exit();
}

$address = $_SESSION['selected_address'];
$cart = $_SESSION['cart'];

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    $_SESSION['message'] = "Your cart is empty!";
    header('Location: ../shopping.php'); // Corrected the path to the shopping page
    exit();
}

// Retrieve the final amount from the session
if (!isset($_SESSION['final_amount'])) {
    die("Final amount not found in session.");
}

$final_amount = $_SESSION['final_amount'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the 'size' column exists in the 'orders' table
$check_column_query = "SHOW COLUMNS FROM orders LIKE 'size'";
$column_result = $conn->query($check_column_query);

if ($column_result->num_rows === 0) {
    $add_column_query = "ALTER TABLE orders ADD COLUMN size VARCHAR(10) NOT NULL DEFAULT 'M'";
    if (!$conn->query($add_column_query)) {
        die("Failed to add 'size' column to the 'orders' table: " . $conn->error);
    }
}

// Generate a unique transaction number
$transaction_number = uniqid('TRANS_');

// Fetch the address details from the session
$address_text = $address['house_no'] . ', ' . $address['building_name'] . ', ' . $address['road_name'] . ', ' . $address['area_name'] . ', ' . $address['city'] . ', ' . $address['state'] . ', ' . $address['pincode'];

// Get username from session
$username = $_SESSION['username'];

// Retrieve or generate order ID and payment method
$order_id = $_GET['order_id'] ?? uniqid('ORDER_'); // Retrieve or generate order ID
$payment_method = $_GET['payment_method'] ?? 'unknown'; // Retrieve payment method

// Store each product in the cart as a separate entry in the database
foreach ($_SESSION['cart'] as $item) {
    $product_id = $item['id'];
    $quantity = $item['quantity'];
    $product_name = $item['name'];
    $product_image = $item['image'];
    $size = $item['size']; // Include size

    // Prepare the SQL statement with the 'size' field
    $stmt = $conn->prepare("INSERT INTO orders (transaction_number, order_id, user_name, product_id, product_name, product_image, quantity, size, address, payment_method, final_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssd", $transaction_number, $order_id, $username, $product_id, $product_name, $product_image, $quantity, $size, $address_text, $payment_method, $final_amount);

    // Execute the statement
    $stmt->execute();

    // Update the stock in the database
    $update_stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    $update_stock_stmt->bind_param("iii", $quantity, $product_id, $quantity);
    $update_stock_stmt->execute();

    // Check if stock update was successful
    if ($update_stock_stmt->affected_rows === 0) {
        $_SESSION['message'] = "Failed to update stock for product ID: $product_id. Insufficient stock.";
    }

    $update_stock_stmt->close();
}

// Clear the cart after successful payment
$_SESSION['cart'] = [];
$_SESSION['message'] = "Payment successful! Your transaction number is $transaction_number. Thank you for your purchase.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2ecc71;
        }

        .success-message {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .order-details {
            margin-bottom: 20px;
        }

        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-details th, .order-details td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .order-details th {
            background: #f9f9f9;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .address-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .address-card h3 {
            margin: 0 0 10px;
            color: #2ecc71;
        }

        .address-card p {
            margin: 5px 0;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Order Successful</h2>

        <!-- Success Message -->
        <div class="success-message">
            <p>Thank you for your purchase! Your order has been placed successfully.</p>
            <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
            <p><strong>Payment Method:</strong> <?php echo ucfirst($payment_method); ?></p>
            <p><strong>Final Amount:</strong> ₹<?php echo $final_amount; ?></p>
        </div>

        <!-- Display Order Details -->
        <div class="order-details">
            <table>
                <thead>
                    <tr>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Size</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): ?>
                        <tr>
                            <td>
                                <?php
                                // Convert local path to web-accessible URL
                                $web_image_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $item['image']);
                                ?>
                                <img src="<?php echo $web_image_path; ?>" alt="Product Image" class="product-image">
                            </td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['size']; ?></td>
                            <td>₹<?php echo $item['price'] * $item['quantity']; // Total price ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Continue Shopping Button -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="shopping.php" class="btn">Continue Shopping</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>