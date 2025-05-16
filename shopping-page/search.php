<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search query
$search_results = [];
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $conn->real_escape_string($_GET['query']);
    $query = "SELECT * FROM products WHERE name LIKE '%$search_query%'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
</head>
<body>
    <h1>Search Results</h1>
    <form method="get" action="search.php">
        <input type="text" name="query" placeholder="Search for products..." value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" required>
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($search_results)): ?>
        <ul>
            <?php foreach ($search_results as $product): ?>
                <li>
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p>Price: ₹<?php echo $product['new_price']; ?></p>
                    <p>Stock: <?php echo $product['stock']; ?> available</p>
                    <?php 
                    // Convert local path to web-accessible URL
                    $web_image_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $product['image']);
                    ?>
                    <img src="<?php echo $web_image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product__img">
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>

    <a href="shopping.php">Back to Shopping</a>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
