<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // ...existing code for adding a product...
}

// Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    // ...existing code for updating a product...
}

// Handle deleting a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = $_POST['id'];
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all products
$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage & Update Products</title>
    <style>
        /* ...existing styles... */
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage & Update Products</h1>

        <!-- Add New Product Form -->
        <h2>Add New Product</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <!-- ...existing form fields for adding a product... -->
            <button type="submit" name="add_product" class="btn">Add Product</button>
        </form>

        <!-- Display All Products -->
        <h2>Product List</h2>
        <table>
            <thead>
                <tr>
                    <!-- ...existing table headers... -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!-- ...existing product details... -->
                        <td>
                            <button class="btn" onclick="toggleEditForm(<?php echo $row['id']; ?>)">Edit</button>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_product" class="btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <!-- Edit Form for Each Product -->
                    <tr class="edit-form" id="edit-form-<?php echo $row['id']; ?>">
                        <td colspan="10">
                            <form method="post" action="" enctype="multipart/form-data">
                                <!-- ...existing form fields for editing a product... -->
                                <button type="submit" name="update_product" class="btn">Update Product</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Function to toggle the edit form
        function toggleEditForm(id) {
            const editForm = document.getElementById(`edit-form-${id}`);
            if (editForm) {
                editForm.classList.toggle('active');
            } else {
                console.error(`Edit form with ID edit-form-${id} not found.`);
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
