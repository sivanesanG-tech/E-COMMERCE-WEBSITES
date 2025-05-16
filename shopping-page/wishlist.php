<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Check if the wishlist session variable is set
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Handle removing a product from the wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_wishlist'])) {
    $product_id = $_POST['product_id'];

    // Remove the product from the wishlist
    foreach ($_SESSION['wishlist'] as $key => $item) {
        if ($item['id'] == $product_id) {
            unset($_SESSION['wishlist'][$key]);
            break;
        }
    }

    // Redirect back to the wishlist page
    header('Location: wishlist.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .wishlist-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .wishlist-table th, .wishlist-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .wishlist-table th {
            background-color: #f2f2f2;
            color: #333;
        }
        .wishlist-table img {
            max-width: 100px;
            height: auto;
        }
        .remove-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .remove-btn:hover {
            background-color: #cc0000;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Wishlist</h1>

        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Display Wishlist Items -->
        <?php if (!empty($_SESSION['wishlist'])): ?>
            <table class="wishlist-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['wishlist'] as $item): ?>
                        <tr>
                            <td>
                                <?php
                                // Convert local path to web-accessible URL
                                $web_image_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $item['image']);
                                ?>
                                <img src="<?php echo $web_image_path; ?>" alt="Product Image">
                            </td>
                            <td><?php echo $item['name']; ?></td>
                            <td>$<?php echo $item['price']; ?></td>
                            <td>
                                <form method="post" action="wishlist.php">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_from_wishlist" class="remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Your wishlist is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>