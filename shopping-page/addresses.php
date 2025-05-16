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

// Fetch addresses for the logged-in user
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$username = $_SESSION['username'];
$query = "SELECT * FROM addresses WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Address</title>
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
        .address-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .address-card h3 {
            margin-bottom: 10px;
            color: #0e4bf1;
        }
        .address-card p {
            margin: 5px 0;
        }
        .select-btn {
            background-color: #0e4bf1;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 3px;
        }
        .select-btn:hover {
            background-color: #0b3cc1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select Address</h1>
        <form method="POST" action="order_summary.php">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="address-card">
                        <h3><?php echo htmlspecialchars($row['address_type']); ?> Address</h3>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($row['fullname']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($row['house_no'] . ', ' . $row['building_name'] . ', ' . $row['road_name'] . ', ' . $row['area_name']); ?></p>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($row['city']); ?></p>
                        <p><strong>State:</strong> <?php echo htmlspecialchars($row['state']); ?></p>
                        <p><strong>PIN Code:</strong> <?php echo htmlspecialchars($row['pincode']); ?></p>
                        <button type="submit" name="selected_address" value="<?php echo $row['id']; ?>" class="select-btn">Select</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No addresses found. Please add an address in your account settings.</p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
