<?php
session_start();

// Check if the address is set in the session
if (!isset($_SESSION['selected_address'])) {
    if (file_exists('addresses.php')) {
        header('Location: addresses.php'); // Redirect back if no address is selected
    } else {
        die('The requested resource /shopping-page/addresses.php was not found on this server.');
    }
    exit();
}

$address = $_SESSION['selected_address'];

// Retrieve the final amount from the session
if (!isset($_SESSION['final_amount'])) {
    die("Final amount not found in session.");
}

$final_amount = $_SESSION['final_amount'];

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    $_SESSION['message'] = "Your cart is empty!";
    header('Location: ../shopping.php'); // Corrected the path to the shopping page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
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

        .order-summary {
            margin-bottom: 20px;
        }

        .order-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-summary th, .order-summary td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .order-summary th {
            background: #f9f9f9;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .payment-method {
            margin-top: 20px;
        }

        .payment-method h3 {
            margin-bottom: 15px;
            color: #2ecc71;
        }

        .payment-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .payment-option input[type="radio"] {
            margin-right: 10px;
        }

        .payment-details {
            display: none;
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .payment-details.show {
            display: block;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
        }

        .btn:hover {
            background: #27ae60;
        }

        .btn-container {
            text-align: center;
        }

        .qr-code img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Payment</h2>

        <!-- Display Selected Address -->
        <div class="address-card">
            <h3><?php echo $address['address_type']; ?> Address</h3>
            <p><strong>Full Name:</strong> <?php echo $address['fullname']; ?></p>
            <p><strong>Phone:</strong> <?php echo $address['phone']; ?></p>
            <p><strong>Alternative Phone:</strong> <?php echo $address['alt_phone']; ?></p>
            <p><strong>Address:</strong> <?php echo $address['house_no'] . ', ' . $address['building_name'] . ', ' . $address['road_name'] . ', ' . $address['area_name']; ?></p>
            <p><strong>City:</strong> <?php echo $address['city']; ?></p>
            <p><strong>State:</strong> <?php echo $address['state']; ?></p>
            <p><strong>PIN Code:</strong> <?php echo $address['pincode']; ?></p>
            <p><strong>Landmark:</strong> <?php echo $address['landmark']; ?></p>
        </div>

        <!-- Display Order Summary -->
        <div class="order-summary">
            <table>
                <thead>
                    <tr>
                        <th>Product Image</th>
                        <th>Product Details</th>
                        <th>Size</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($_SESSION['cart'] as $item): 
                    ?>
                        <tr>
                            <td>
                                <?php
                                // Convert local path to web-accessible URL
                                $web_image_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $item['image']);
                                ?>
                                <img src="<?php echo $web_image_path; ?>" alt="Product Image" class="product-image">
                            </td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['size']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Final Price: ₹<?php echo $final_amount; ?></strong></p>
        </div>

        <!-- Payment Method Selection -->
        <div class="payment-method">
            <h3>Choose Payment Method</h3>

            <!-- Card Payment Option -->
            <div class="payment-option">
                <input type="radio" id="card-payment" name="payment-method" value="card">
                <label for="card-payment">Pay with Card</label>
            </div>

            <!-- Card Payment Details -->
            <div class="payment-details" id="card-details">
                <h3>Card Payment Details</h3>
                <form id="card-form">
                    <label for="card_number">Card Number:</label>
                    <input type="text" id="card_number" name="card_number" required><br><br>

                    <label for="expiry_date">Expiry Date:</label>
                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" required><br><br>

                    <label for="cvv">CVV:</label>
                    <input type="text" id="cvv" name="cvv" required><br><br>
                </form>
            </div>

            <!-- QR Code Payment Option -->
            <div class="payment-option">
                <input type="radio" id="qr-code-payment" name="payment-method" value="qr-code">
                <label for="qr-code-payment">Scan QR Code</label>
            </div>

            <!-- QR Code Payment Details -->
            <div class="payment-details" id="qr-code-details">
                <h3>Scan QR Code to Pay</h3>
                <img src="https://example.com/path-to-your-qr-code.png" alt="QR Code">
                <p>Scan the QR code using your UPI app to complete the payment.</p>
            </div>

            <!-- Cash on Delivery Option -->
            <div class="payment-option">
                <input type="radio" id="cod-payment" name="payment-method" value="cod">
                <label for="cod-payment">Cash on Delivery</label>
            </div>

            <!-- Cash on Delivery Details -->
            <div class="payment-details" id="cod-details">
                <h3>Cash on Delivery</h3>
                <p>You have selected Cash on Delivery. Please ensure you have the exact amount ready when the delivery arrives.</p>
            </div>

            <!-- Place Order Button -->
            <div class="btn-container" id="place-order-btn" style="display: none;">
                <button class="btn" onclick="redirectToOrderSuccess()">Place Order</button>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to toggle payment details and show the "Place Order" button
        document.querySelectorAll('input[name="payment-method"]').forEach((radio) => {
            radio.addEventListener('change', function() {
                // Hide all payment details
                document.querySelectorAll('.payment-details').forEach((details) => {
                    details.classList.remove('show');
                });

                // Show the selected payment details
                const selectedDetailsId = this.value + '-details';
                const selectedDetails = document.getElementById(selectedDetailsId);
                if (selectedDetails) {
                    selectedDetails.classList.add('show');
                }

                // Show the "Place Order" button
                document.getElementById('place-order-btn').style.display = 'block';
            });
        });

        // Function to redirect to the Order Successful page
        function redirectToOrderSuccess() {
            const selectedPaymentMethod = document.querySelector('input[name="payment-method"]:checked').value;
            const orderId = '<?php echo uniqid("ORDER_"); ?>'; // Generate unique order ID
            window.location.href = `order_successful.php?payment_method=${selectedPaymentMethod}&order_id=${orderId}`;
        }
    </script>
</body>
</html>