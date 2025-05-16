<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'shopping';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$username = $_SESSION['username']; // Get the logged-in user's username

// Handle form submission for adding a new address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $alt_phone = $conn->real_escape_string($_POST['alt_phone']);
    $pincode = $conn->real_escape_string($_POST['pincode']);
    $state = $conn->real_escape_string($_POST['state']);
    $city = $conn->real_escape_string($_POST['city']);
    $house_no = $conn->real_escape_string($_POST['house_no']);
    $building_name = $conn->real_escape_string($_POST['building_name']);
    $road_name = $conn->real_escape_string($_POST['road_name']);
    $area_name = $conn->real_escape_string($_POST['area_name']);
    $landmark = $conn->real_escape_string($_POST['landmark']);
    $address_type = $conn->real_escape_string($_POST['address_type']);

    $query = "INSERT INTO addresses (username, fullname, phone, alt_phone, pincode, state, city, house_no, building_name, road_name, area_name, landmark, address_type) 
              VALUES ('$username', '$fullname', '$phone', '$alt_phone', '$pincode', '$state', '$city', '$house_no', '$building_name', '$road_name', '$area_name', '$landmark', '$address_type')";

    if ($conn->query($query)) {
        header('Location: address.php'); // Redirect to the same page to show the updated list
        exit();
    } else {
        echo "<div class='alert error'>Error: " . $conn->error . "</div>";
    }
}

// Handle form submission for editing an address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $alt_phone = $conn->real_escape_string($_POST['alt_phone']);
    $pincode = $conn->real_escape_string($_POST['pincode']);
    $state = $conn->real_escape_string($_POST['state']);
    $city = $conn->real_escape_string($_POST['city']);
    $house_no = $conn->real_escape_string($_POST['house_no']);
    $building_name = $conn->real_escape_string($_POST['building_name']);
    $road_name = $conn->real_escape_string($_POST['road_name']);
    $area_name = $conn->real_escape_string($_POST['area_name']);
    $landmark = $conn->real_escape_string($_POST['landmark']);
    $address_type = $conn->real_escape_string($_POST['address_type']);

    $query = "UPDATE addresses SET fullname='$fullname', phone='$phone', alt_phone='$alt_phone', pincode='$pincode', state='$state', city='$city', house_no='$house_no', building_name='$building_name', road_name='$road_name', area_name='$area_name', landmark='$landmark', address_type='$address_type' WHERE id='$id' AND username='$username'";

    if ($conn->query($query)) {
        header('Location: accounts.php'); // Redirect to the account page after updating the address
        exit();
    } else {
        echo "<div class='alert error'>Error: " . $conn->error . "</div>";
    }
}

// Fetch all addresses for the logged-in user
$query = "SELECT * FROM addresses WHERE username='$username'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Addresses</title>
    <style>
        /* Add your CSS styles here */
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
        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
            <h2>Add Address</h2>
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
                <button type="submit" name="save_address">Save Address</button>
            </form>
            <a href="address.php">View All Addresses</a>
        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
            <?php
            $id = $conn->real_escape_string($_GET['id']);
            $query = "SELECT * FROM addresses WHERE id='$id' AND username='$username'";
            $result = $conn->query($query);
            $address = $result->fetch_assoc();
            ?>
            <h2>Edit Address</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $address['id']; ?>">
                <input type="text" name="fullname" required placeholder="Full Name" value="<?php echo $address['fullname']; ?>"><br>
                <input type="text" name="phone" required placeholder="Phone Number" value="<?php echo $address['phone']; ?>"><br>
                <input type="text" name="alt_phone" placeholder="Alternative Phone Number" value="<?php echo $address['alt_phone']; ?>"><br>
                <input type="text" name="pincode" required placeholder="Pin Code" value="<?php echo $address['pincode']; ?>"><br>
                <input type="text" name="state" required placeholder="State" value="<?php echo $address['state']; ?>"><br>
                <input type="text" name="city" required placeholder="City or District" value="<?php echo $address['city']; ?>"><br>
                <input type="text" name="house_no" required placeholder="House No." value="<?php echo $address['house_no']; ?>"><br>
                <input type="text" name="building_name" required placeholder="Building Name" value="<?php echo $address['building_name']; ?>"><br>
                <input type="text" name="road_name" required placeholder="Road Name" value="<?php echo $address['road_name']; ?>"><br>
                <input type="text" name="area_name" required placeholder="Area Name" value="<?php echo $address['area_name']; ?>"><br>
                <input type="text" name="landmark" placeholder="Nearby Famous Shop/Mall/Landmark" value="<?php echo $address['landmark']; ?>"><br>
                <select name="address_type" required>
                    <option value="Home" <?php echo $address['address_type'] === 'Home' ? 'selected' : ''; ?>>Home</option>
                    <option value="Work" <?php echo $address['address_type'] === 'Work' ? 'selected' : ''; ?>>Work</option>
                </select><br>
                <button type="submit" name="update_address">Update Address</button>
            </form>
            <a href="address.php">View All Addresses</a>
        <?php else: ?>
            <h2>Your Addresses</h2>
            <form action="order_summary.php" method="POST">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="address-card">
                            <label>
                                <input type="radio" name="selected_address" value="<?php echo $row['id']; ?>" required>
                                <h3><?php echo $row['address_type']; ?> Address</h3>
                                <p><strong>Full Name:</strong> <?php echo $row['fullname']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $row['phone']; ?></p>
                                <p><strong>Alternative Phone:</strong> <?php echo $row['alt_phone']; ?></p>
                                <p><strong>Address:</strong> <?php echo $row['house_no'] . ', ' . $row['building_name'] . ', ' . $row['road_name'] . ', ' . $row['area_name']; ?></p>
                                <p><strong>City:</strong> <?php echo $row['city']; ?></p>
                                <p><strong>State:</strong> <?php echo $row['state']; ?></p>
                                <p><strong>PIN Code:</strong> <?php echo $row['pincode']; ?></p>
                                <p><strong>Landmark:</strong> <?php echo $row['landmark']; ?></p>
                            </label>
                            <a href="address2.php?action=edit&id=<?php echo $row['id']; ?>" class="btn">Edit</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No addresses found. <a href="address2.php?action=add">Add a new address</a></p>
                <?php endif; ?>
                <div class="btn-container">
                    <a href="address.php?action=add" class="btn">Add New Address</a>
                    <button type="submit" class="btn">Continue</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>