<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories from the database
$categories_query = "SELECT DISTINCT category FROM products";
$categories_result = $conn->query($categories_query);

// Fetch products based on selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;
$products_query = $selected_category 
    ? "SELECT * FROM products WHERE category = '$selected_category'" 
    : "SELECT * FROM products";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Categories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .categories {
            margin-bottom: 20px;
        }
        .categories a {
            margin-right: 10px;
            text-decoration: none;
            color: blue;
        }
        .categories a:hover {
            text-decoration: underline;
        }
        .product {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
        .product img {
            max-width: 100px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Product Categories</h1>

    <!-- Display categories -->
    <div class="categories">
        <strong>Categories:</strong>
        <a href="categories.php">All</a>
        <?php while ($category = $categories_result->fetch_assoc()): ?>
            <a href="categories.php?category=<?php echo urlencode($category['category']); ?>">
                <?php echo htmlspecialchars($category['category']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Display products -->
    <div class="products">
        <?php if ($products_result->num_rows > 0): ?>
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo str_replace('C:/uploads/', 'http://localhost/uploads/', $product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Price: ₹<?php echo $product['new_price']; ?></p>
                    <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products found in this category.</p>
        <?php endif; ?>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
