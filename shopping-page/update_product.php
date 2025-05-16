<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shopping');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all brands for the dropdown
$brand_query = "SELECT id, name AS brand_name FROM brands";
$brand_result = $conn->query($brand_query);

// Handle adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $old_price = $_POST['old_price'];
    $new_price = $_POST['new_price'];
    $details = $_POST['details'];
    $image = '';
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $brand_name = $_POST['brand_name'] ?? '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // Folder to store uploaded images
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the folder if it doesn't exist
        }
        $image_name = basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        // Move the uploaded file to the uploads folder
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path; // Save the relative path to the database
        } else {
            die("Error uploading image.");
        }
    }

    // Insert new product into the database
    $query = "INSERT INTO products (name, old_price, new_price, details, image, category, stock, brand_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sddsssiss', $name, $old_price, $new_price, $details, $image, $category, $stock, $brand_name);
    $stmt->execute();
    $stmt->close();
}

// Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $old_price = $_POST['old_price'];
    $new_price = $_POST['new_price'];
    $details = $_POST['details'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $brand_name = $_POST['brand_name'] ?? '';

    // Handle image upload for update
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $image_name = basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path;
        }
    } else {
        // Keep the existing image if no new image is uploaded
        $image = $_POST['existing_image'];
    }

    // Update the product in the database
    $query = "UPDATE products SET name = ?, old_price = ?, new_price = ?, details = ?, image = ?, category = ?, stock = ?, brand_name = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sddsssisi', $name, $old_price, $new_price, $details, $image, $category, $stock, $brand_name, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle deleting a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = $_POST['id'];
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_products.php"); // Redirect to refresh the page
    exit;
}

// Fetch all products with brand_name
$query = "SELECT products.*, brands.name AS brand_name FROM products LEFT JOIN brands ON products.brand_name = brands.id";
$result = $conn->query($query);

// Fetch all brands for the dropdown (reset the result pointer for reuse)
$brand_result = $conn->query($brand_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: auto; padding: 20px; background: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; }
        .form-group textarea { height: 100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .btn { background: #ff6600; color: white; padding: 8px 12px; border: none; cursor: pointer; }
        .btn:hover { background: #e65c00; }
        .edit-form { display: none; } /* Hide the edit form by default */
        .edit-form.active { display: table-row; } /* Show the edit form when active */
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Products</h1>

        <!-- Add New Product Form -->
        <h2>Add New Product</h2>
        <form method="post" action="manage_products.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="old_price">Old Price:</label>
                <input type="number" id="old_price" name="old_price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="new_price">New Price:</label>
                <input type="number" id="new_price" name="new_price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="details">Product Details:</label>
                <textarea id="details" name="details" required></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            <div class="form-group">
                <label for="brand_name">Brand Name:</label>
                <select name="brand_name" required>
                    <option value="">Select Brand</option>
                    <?php while ($brand = $brand_result->fetch_assoc()): ?>
                        <option value="<?php echo $brand['id']; ?>"><?php echo $brand['brand_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image">
            </div>
            <button type="submit" name="add_product" class="btn">Add Product</button>
        </form>

        <!-- Display All Products -->
        <h2>Product List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Old Price</th>
                    <th>New Price</th>
                    <th>Details</th>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Brand Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['old_price']; ?></td>
                        <td><?php echo $row['new_price']; ?></td>
                        <td><?php echo $row['details']; ?></td>
                        <td>
                            <?php
                            // Convert local path to web-accessible URL
                            $web_image_path = str_replace('C:/uploads/', 'http://localhost/uploads/', $row['image']);
                            echo '<img src="' . $web_image_path . '" alt="Product Image" width="100">';
                            ?>
                        </td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['stock']; ?></td>
                        <td><?php echo $row['brand_name']; ?></td>
                        <td>
                            <button class="btn" onclick="toggleEditForm(<?php echo $row['id']; ?>)">Edit</button>
                            <form method="post" action="manage_products.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_product" class="btn" style="background: #e60000;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <!-- Edit Form for Each Product -->
                    <tr class="edit-form" id="edit-form-<?php echo $row['id']; ?>">
                        <td colspan="10">
                            <form method="post" action="manage_products.php" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo $row['image']; ?>">
                                <div class="form-group">
                                    <label for="name">Product Name:</label>
                                    <input type="text" name="name" value="<?php echo $row['name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="old_price">Old Price:</label>
                                    <input type="number" name="old_price" value="<?php echo $row['old_price']; ?>" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_price">New Price:</label>
                                    <input type="number" name="new_price" value="<?php echo $row['new_price']; ?>" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="details">Product Details:</label>
                                    <textarea name="details" required><?php echo $row['details']; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="category">Category:</label>
                                    <input type="text" name="category" value="<?php echo $row['category']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="stock">Stock:</label>
                                    <input type="number" name="stock" value="<?php echo $row['stock']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="brand_name">Brand Name:</label>
                                    <select name="brand_name" required>
                                        <option value="">Select Brand</option>
                                        <?php
                                        $brand_result->data_seek(0); // Reset pointer to reuse the result set
                                        while ($brand = $brand_result->fetch_assoc()): ?>
                                            <option value="<?php echo $brand['id']; ?>" <?php echo ($brand['id'] == $row['brand_name']) ? 'selected' : ''; ?>>
                                                <?php echo $brand['brand_name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="image">Product Image:</label>
                                    <input type="file" name="image">
                                </div>
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