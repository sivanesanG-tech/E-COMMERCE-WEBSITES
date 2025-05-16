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
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Initialize $user_email from session
$username = $_SESSION['username']; // Get the logged-in user's username
$user_email = $_SESSION['email']; // Initialize $user_email from session

// Handle form submission for support messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message = $conn->real_escape_string($_POST['message']);

    $query = "INSERT INTO support_messages (user_email, message, created_at) VALUES ('$user_email', '$message', NOW())";

    if ($conn->query($query)) {
        echo "<div class='alert success'>Message sent successfully. Our support team will get back to you soon.</div>";
        echo "<script>
                setTimeout(function() {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => alert.style.display = 'none');
                }, 7000); // Hide message after 7 seconds
              </script>";
    } else {
        echo "<div class='alert error'>Error: " . $conn->error . "</div>";
    }
}

// Fetch replied messages for the logged-in user
$query = "SELECT * FROM support_messages WHERE user_email='$user_email' AND reply IS NOT NULL ORDER BY replied_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Us</title>
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-box {
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        button {
            display: inline-block;
            padding: 10px 20px;
            background: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
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

        .message {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .message p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Support Us</h2>
        <div class="form-box">
            <form method="POST">
                <textarea name="message" rows="5" required placeholder="Write your message here..."></textarea><br>
                <button type="submit" name="send_message">Send Message</button>
            </form>
        </div>

        <h3>Your Replied Messages:</h3>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="message">
                    <p><strong>Your Message:</strong> <?php echo $row['message']; ?></p>
                    <p><strong>Reply:</strong> <?php echo $row['reply']; ?></p>
                    <p><strong>Replied At:</strong> <?php echo $row['replied_at']; ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No replies yet.</p>
        <?php endif; ?>
    </div>
    <h2>Submit a Customer Care Query</h2>
    <form method="POST" action="support-page.php">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>
        
        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="4" required></textarea>
        
        <button type="submit" name="submit_query">Submit</button>
    </form>

</body>
</html>

<?php
$conn->close();
?>