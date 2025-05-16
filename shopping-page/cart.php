<?php
session_start();

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ensure each product in the cart has a 'size' key
foreach ($_SESSION['cart'] as &$item) {
    if (!isset($item['size'])) {
        $item['size'] = 'M'; // Default size
    }
}
unset($item); // Break reference to avoid unexpected behavior

// Handle removing a product from the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];

    // Remove the product from the cart
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }

    // Redirect back to the cart page
    header('Location: cart.php');
    exit();
}

// Handle updating the quantity of a product in the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Update the quantity of the product in the cart
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['quantity'] = $quantity;
            break;
        }
    }

    // Redirect back to the cart page
    header('Location: cart.php');
    exit();
}

// Handle updating the size of a product in the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_size'])) {
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];

    // Update the size of the product in the cart
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['size'] = $size;
            break;
        }
    }

    // Redirect back to the cart page
    header('Location: cart.php');
    exit();
}

// Handle proceeding to address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_to_address'])) {
    // Redirect to the view addresses page
    header('Location: ../view_addresses.php');
    exit();
}

// Calculate total amount
$total_amount = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../styles.css">
    <script>
        function updateQuantity(productId, quantity) {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'cart.php';

            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            productIdInput.value = productId;
            form.appendChild(productIdInput);

            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = 'quantity';
            quantityInput.value = quantity;
            form.appendChild(quantityInput);

            const updateQuantityInput = document.createElement('input');
            updateQuantityInput.type = 'hidden';
            updateQuantityInput.name = 'update_quantity';
            updateQuantityInput.value = '1';
            form.appendChild(updateQuantityInput);

            document.body.appendChild(form);
            form.submit();
        }

        function updateSize(productId, size) {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'cart.php';

            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            productIdInput.value = productId;
            form.appendChild(productIdInput);

            const sizeInput = document.createElement('input');
            sizeInput.type = 'hidden';
            sizeInput.name = 'size';
            sizeInput.value = size;
            form.appendChild(sizeInput);

            const updateSizeInput = document.createElement('input');
            updateSizeInput.type = 'hidden';
            updateSizeInput.name = 'update_size';
            updateSizeInput.value = '1';
            form.appendChild(updateSizeInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Shopping Cart</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['cart'])): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Size</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td>
                                <?php
                                // Convert local path to web-accessible URL
                                $web_image_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $item['image']);
                                ?>
                                <img src="<?php echo $web_image_path; ?>" alt="Product Image">
                            </td>
                            <td><?php echo $item['name']; ?></td>
                            <td class="item-price">₹<?php echo $item['price']; ?></td>
                            <td>
                                <input type="number" class="quantity-input" data-product-id="<?php echo $item['id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                            </td>
                            <td>
                                <select onchange="updateSize(<?php echo $item['id']; ?>, this.value)">
                                    <option value="S" <?php echo $item['size'] === 'S' ? 'selected' : ''; ?>>S</option>
                                    <option value="M" <?php echo $item['size'] === 'M' ? 'selected' : ''; ?>>M</option>
                                    <option value="L" <?php echo $item['size'] === 'L' ? 'selected' : ''; ?>>L</option>
                                    <option value="XL" <?php echo $item['size'] === 'XL' ? 'selected' : ''; ?>>XL</option>
                                </select>
                            </td>
                            <td class="item-total">₹<?php echo $item['price'] * $item['quantity']; ?></td>
                            <td>
                                <form method="post" action="cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_from_cart" class="btn remove-btn" data-product-id="<?php echo $item['id']; ?>">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Total Amount:</strong></td>
                        <td colspan="2" class="cart-total-amount"><strong>₹<?php echo $total_amount; ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <!-- Add a button to proceed to the address page -->
        <div class="container">
            <a href="address.php" class="btn">Proceed to Order</a>
        </div>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
    <script src="cart.js"></script>
</body>
</html>