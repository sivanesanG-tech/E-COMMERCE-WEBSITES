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
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$success_message = '';
$error_message = '';

// Handle form submission for adding a new address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $fields = [
        'fullname' => $conn->real_escape_string($_POST['fullname'] ?? ''),
        'phone' => $conn->real_escape_string($_POST['phone'] ?? ''),
        'alt_phone' => $conn->real_escape_string($_POST['alt_phone'] ?? ''),
        'pincode' => $conn->real_escape_string($_POST['pincode'] ?? ''),
        'state' => $conn->real_escape_string($_POST['state'] ?? ''),
        'city' => $conn->real_escape_string($_POST['city'] ?? ''),
        'house_no' => $conn->real_escape_string($_POST['house_no'] ?? ''),
        'building_name' => $conn->real_escape_string($_POST['building_name'] ?? ''),
        'road_name' => $conn->real_escape_string($_POST['road_name'] ?? ''),
        'area_name' => $conn->real_escape_string($_POST['area_name'] ?? ''),
        'landmark' => $conn->real_escape_string($_POST['landmark'] ?? ''),
        'address_type' => $conn->real_escape_string($_POST['address_type'] ?? '')
    ];

    // Validate required fields
    $required = ['fullname', 'phone', 'pincode', 'state', 'city', 'house_no', 'building_name', 'road_name', 'area_name', 'address_type'];
    $valid = true;
    foreach ($required as $field) {
        if (empty($fields[$field])) {
            $error_message = "Please fill in all required fields.";
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $query = "INSERT INTO addresses (username, " . implode(', ', array_keys($fields)) . ") 
                  VALUES ('$username', '" . implode("', '", $fields) . "')";

        if ($conn->query($query)) {
            $success_message = "Address added successfully!";
            header('Location: address.php?success=' . urlencode($success_message));
            exit();
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

// Handle form submission for editing an address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $id = $conn->real_escape_string($_POST['id'] ?? '');
    $fields = [
        'fullname' => $conn->real_escape_string($_POST['fullname'] ?? ''),
        'phone' => $conn->real_escape_string($_POST['phone'] ?? ''),
        'alt_phone' => $conn->real_escape_string($_POST['alt_phone'] ?? ''),
        'pincode' => $conn->real_escape_string($_POST['pincode'] ?? ''),
        'state' => $conn->real_escape_string($_POST['state'] ?? ''),
        'city' => $conn->real_escape_string($_POST['city'] ?? ''),
        'house_no' => $conn->real_escape_string($_POST['house_no'] ?? ''),
        'building_name' => $conn->real_escape_string($_POST['building_name'] ?? ''),
        'road_name' => $conn->real_escape_string($_POST['road_name'] ?? ''),
        'area_name' => $conn->real_escape_string($_POST['area_name'] ?? ''),
        'landmark' => $conn->real_escape_string($_POST['landmark'] ?? ''),
        'address_type' => $conn->real_escape_string($_POST['address_type'] ?? '')
    ];

    // Validate required fields
    $required = ['fullname', 'phone', 'pincode', 'state', 'city', 'house_no', 'building_name', 'road_name', 'area_name', 'address_type'];
    $valid = true;
    foreach ($required as $field) {
        if (empty($fields[$field])) {
            $error_message = "Please fill in all required fields.";
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $updates = [];
        foreach ($fields as $key => $value) {
            $updates[] = "$key='$value'";
        }

        $query = "UPDATE addresses SET " . implode(', ', $updates) . " 
                  WHERE id='$id' AND username='$username'";

        if ($conn->query($query)) {
            $success_message = "Address updated successfully!";
            header('Location: address.php?success=' . urlencode($success_message));
            exit();
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

// Handle address deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    $query = "DELETE FROM addresses WHERE id='$id' AND username='$username'";
    
    if ($conn->query($query)) {
        $success_message = "Address deleted successfully!";
        header('Location: address.php?success=' . urlencode($success_message));
        exit();
    } else {
        $error_message = "Error deleting address: " . $conn->error;
    }
}

// Display success/error messages from URL parameters
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}

// Fetch all addresses for the logged-in user
$query = "SELECT * FROM addresses WHERE username='$username' ORDER BY address_type, fullname";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Addresses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6bff;
            --primary-hover: #3a56d4;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .header h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header h2 i {
            color: var(--primary-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            border: none;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(74, 107, 255, 0.2);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-container {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .address-card {
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            background: white;
            transition: var(--transition);
            position: relative;
            box-shadow: var(--box-shadow);
        }

        .address-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .address-card.selected {
            border: 2px solid var(--primary-color);
            background-color: rgba(74, 107, 255, 0.05);
        }

        .address-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .address-card p {
            margin: 0.5rem 0;
            color: #555;
        }

        .address-card p strong {
            color: var(--dark-color);
            min-width: 120px;
            display: inline-block;
        }

        .address-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }

        .address-type-badge {
            position: absolute;
            top: -10px;
            right: 15px;
            background-color: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .no-addresses {
            text-align: center;
            padding: 2rem;
            color: var(--secondary-color);
            grid-column: 1 / -1;
        }

        .no-addresses i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.25);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .radio-label {
            display: flex;
            cursor: pointer;
        }

        .radio-input {
            margin-right: 0.75rem;
            accent-color: var(--primary-color);
            width: 18px;
            height: 18px;
        }

        @media (max-width: 768px) {
            .address-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-container {
                flex-direction: column;
            }
            
            .address-actions {
                flex-direction: column;
            }
            
            .address-card p {
                flex-direction: column;
            }
            
            .address-card p strong {
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-address-book"></i> Your Addresses</h2>
            <div class="btn-container">
                <a href="address.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Address
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
            <div class="form-container">
                <h2><i class="fas fa-plus-circle"></i> Add New Address</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="alt_phone">Alternative Phone Number</label>
                        <input type="text" id="alt_phone" name="alt_phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="pincode">PIN Code *</label>
                        <input type="text" id="pincode" name="pincode" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State *</label>
                        <input type="text" id="state" name="state" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City/District *</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="house_no">House No. *</label>
                        <input type="text" id="house_no" name="house_no" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="building_name">Building Name *</label>
                        <input type="text" id="building_name" name="building_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="road_name">Road Name *</label>
                        <input type="text" id="road_name" name="road_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="area_name">Area Name *</label>
                        <input type="text" id="area_name" name="area_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="landmark">Landmark (Optional)</label>
                        <input type="text" id="landmark" name="landmark" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="address_type">Address Type *</label>
                        <select id="address_type" name="address_type" class="form-control" required>
                            <option value="">Select Address Type</option>
                            <option value="Home">Home</option>
                            <option value="Work">Work</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <a href="address.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" name="save_address" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Address
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
            <?php
            $id = $conn->real_escape_string($_GET['id']);
            $query = "SELECT * FROM addresses WHERE id='$id' AND username='$username'";
            $result = $conn->query($query);
            $address = $result->fetch_assoc();
            ?>
            
            <div class="form-container">
                <h2><i class="fas fa-edit"></i> Edit Address</h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($address['id']); ?>">
                    
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" 
                               value="<?php echo htmlspecialchars($address['fullname']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($address['phone']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="alt_phone">Alternative Phone Number</label>
                        <input type="text" id="alt_phone" name="alt_phone" class="form-control" 
                               value="<?php echo htmlspecialchars($address['alt_phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="pincode">PIN Code *</label>
                        <input type="text" id="pincode" name="pincode" class="form-control" 
                               value="<?php echo htmlspecialchars($address['pincode']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State *</label>
                        <input type="text" id="state" name="state" class="form-control" 
                               value="<?php echo htmlspecialchars($address['state']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City/District *</label>
                        <input type="text" id="city" name="city" class="form-control" 
                               value="<?php echo htmlspecialchars($address['city']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="house_no">House No. *</label>
                        <input type="text" id="house_no" name="house_no" class="form-control" 
                               value="<?php echo htmlspecialchars($address['house_no']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="building_name">Building Name *</label>
                        <input type="text" id="building_name" name="building_name" class="form-control" 
                               value="<?php echo htmlspecialchars($address['building_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="road_name">Road Name *</label>
                        <input type="text" id="road_name" name="road_name" class="form-control" 
                               value="<?php echo htmlspecialchars($address['road_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="area_name">Area Name *</label>
                        <input type="text" id="area_name" name="area_name" class="form-control" 
                               value="<?php echo htmlspecialchars($address['area_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="landmark">Landmark (Optional)</label>
                        <input type="text" id="landmark" name="landmark" class="form-control" 
                               value="<?php echo htmlspecialchars($address['landmark']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address_type">Address Type *</label>
                        <select id="address_type" name="address_type" class="form-control" required>
                            <option value="Home" <?php echo $address['address_type'] === 'Home' ? 'selected' : ''; ?>>Home</option>
                            <option value="Work" <?php echo $address['address_type'] === 'Work' ? 'selected' : ''; ?>>Work</option>
                            <option value="Other" <?php echo $address['address_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <a href="address.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" name="update_address" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Address
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <form action="order_summary.php" method="POST">
                <?php if ($result->num_rows > 0): ?>
                    <div class="address-grid">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="address-card">
                                <div class="address-type-badge">
                                    <?php echo htmlspecialchars($row['address_type']); ?>
                                </div>
                                
                                <label class="radio-label">
                                    <input type="radio" name="selected_address" value="<?php echo htmlspecialchars($row['id']); ?>" 
                                           class="radio-input" required>
                                    <div>
                                        <h3><?php echo htmlspecialchars($row['fullname']); ?></h3>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
                                        <?php if (!empty($row['alt_phone'])): ?>
                                            <p><strong>Alt. Phone:</strong> <?php echo htmlspecialchars($row['alt_phone']); ?></p>
                                        <?php endif; ?>
                                        <p><strong>Address:</strong> <?php echo htmlspecialchars(
                                            $row['house_no'] . ', ' . $row['building_name'] . ', ' . 
                                            $row['road_name'] . ', ' . $row['area_name']
                                        ); ?></p>
                                        <p><strong>City:</strong> <?php echo htmlspecialchars($row['city']); ?></p>
                                        <p><strong>State:</strong> <?php echo htmlspecialchars($row['state']); ?></p>
                                        <p><strong>PIN:</strong> <?php echo htmlspecialchars($row['pincode']); ?></p>
                                        <?php if (!empty($row['landmark'])): ?>
                                            <p><strong>Landmark:</strong> <?php echo htmlspecialchars($row['landmark']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </label>
                                
                                <div class="address-actions">
                                    <a href="address.php?action=edit&id=<?php echo htmlspecialchars($row['id']); ?>" 
                                       class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="address.php?delete=<?php echo htmlspecialchars($row['id']); ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this address?');">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Continue with Selected Address
                        </button>
                    </div>
                <?php else: ?>
                    <div class="no-addresses">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>No Addresses Found</h3>
                        <p>You haven't added any addresses yet. Add one to continue with your order.</p>
                        <a href="address.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Address
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>