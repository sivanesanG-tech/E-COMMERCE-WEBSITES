<?php
session_start();
require_once '../shopping-page/db_connection.php'; // Corrected the path to the database connection file

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$username = $_SESSION['username']; // Get the logged-in user's username

// Check if the cart session variable is set, if not, redirect to the cart page
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Fetch all addresses for the logged-in user
$query = "SELECT * FROM addresses WHERE username='$username'";
$result = $conn->query($query);

// Close the PHP block before starting HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Addresses</title>
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.62);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .address-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
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

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .btn:hover {
            background: #27ae60;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background: #2ecc71;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #27ae60;
        }

        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #2ecc71;
            color: white;
        }

        .error {
            background-color: #e74c3c;
            color: white;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2ecc71;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Addresses</h2>
        <form method="POST">
            <input type="text" name="fullname" required placeholder="Full Name"><br>
            <input type="text" name="phone" required placeholder="Phone Number"><br>
            <input type="text" name="alt_phone" placeholder="Alternative Phone Number"><br>
            <input type="text" name="pincode" required placeholder="Pin Code"><br>
            <input type="text" name="state" required placeholder="State"><br>
            <input type="text" name="city" required placeholder="City or District"><br>
            <input type="text" name="house_no" required placeholder="House No."><br>
            <input type="text" name="building_name" required placeholder="Building Name"><br>
            <input type="text" name="road_name" required placeholder="Road Name"><br>
            <input type="text" name="area_name" required placeholder="Area Name"><br>
            <input type="text" name="landmark" placeholder="Nearby Famous Shop/Mall/Landmark"><br>
            <select name="address_type" required>
                <option value="">Select Address Type</option>
                <option value="Home">Home</option>
                <option value="Work">Work</option>
            </select><br>
            <button type="submit">Save Address</button>
        </form>
        <h2>Your Addresses</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="address-card">
                    <h3><?php echo $row['address_type']; ?> Address</h3>
                    <p><strong>Full Name:</strong> <?php echo $row['fullname']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $row['phone']; ?></p>
                    <p><strong>Alternative Phone:</strong> <?php echo $row['alt_phone']; ?></p>
                    <p><strong>Address:</strong> <?php echo $row['house_no'] . ', ' . $row['building_name'] . ', ' . $row['road_name'] . ', ' . $row['area_name']; ?></p>
                    <p><strong>City:</strong> <?php echo $row['city']; ?></p>
                    <p><strong>State:</strong> <?php echo $row['state']; ?></p>
                    <p><strong>PIN Code:</strong> <?php echo $row['pincode']; ?></p>
                    <p><strong>Landmark:</strong> <?php echo $row['landmark']; ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No addresses found. Add a new address above.</p>
        <?php endif; ?>
        <div class="btn-container">
            <a href="order_summary.php" class="btn">Continue</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>