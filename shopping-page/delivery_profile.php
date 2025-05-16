<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: delivery_login.php'); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Person Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #6a1b9a;
        }

        .profile-details {
            margin-top: 20px;
        }

        .profile-details p {
            font-size: 1.1rem;
            margin: 10px 0;
        }

        .profile-details p strong {
            color: #333;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6a1b9a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .back-btn:hover {
            background-color: #9c4dcc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Delivery Person Profile</h1>
        <div class="profile-details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['phone']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($_SESSION['location']); ?></p>
        </div>
        <a href="delivery.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Delivery Management</a>
    </div>
</body>
</html>
