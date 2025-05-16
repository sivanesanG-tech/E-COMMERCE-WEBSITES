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

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $created_at = date('Y-m-d H:i:s');

    // Check if the email already exists
    $check_query = "SELECT * FROM customercareuser WHERE email='$email'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        echo "<div class='alert error'>Email already registered. Please login.</div>";
    } else {
        $query = "INSERT INTO customercareuser (fullname, email, phone, password, created_at) VALUES ('$fullname', '$email', '$phone', '$password', '$created_at')";
        if ($conn->query($query)) {
            $_SESSION['just_registered'] = true;
            echo "<div class='alert success'>Registration successful. Please login.</div>";
        } else {
            echo "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM customercareuser WHERE email='$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['phone'] = $row['phone'];
            echo "<div class='alert success'>Login successful. Redirecting...</div>";
            echo "<script>
                    setTimeout(function() {
                        const alerts = document.querySelectorAll('.alert');
                        alerts.forEach(alert => alert.style.display = 'none');
                    }, 7000);
                  </script>";
            echo "<script>setTimeout(function(){ window.location.href='support-page.php'; }, 1500);</script>"; // Redirect to support-page.php
        } else {
            echo "<div class='alert error'>Incorrect password</div>";
        }
    } else {
        echo "<div class='alert error'>No user found. Please register first.</div>";
    }
}

if (isset($_POST['subscribe'])) {
    $email = $_POST['email'];

    // Check if the email is already subscribed
    $check_query = "SELECT * FROM newsletter_subscribers WHERE email='$email'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        echo "<div class='alert error'>Email is already subscribed to the newsletter.</div>";
    } else {
        // Add the email to the newsletter subscribers table
        $query = "INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES ('$email', NOW())";
        if ($conn->query($query)) {
            $_SESSION['newsletter_discount'] = true; // Set session to track the discount
            echo "<div class='alert success'>Subscription successful! You will receive a 25% discount on your first order.</div>";
        } else {
            echo "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
    }
}
?>
<script>
    // Hide alert messages after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => alert.style.display = 'none');
    }, 5000);
</script>

<!DOCTYPE html>
<html>

<head>
    <title>Login & Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: linear-gradient(120deg, #3498db, #8e44ad);
            color: white;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .container {
            width: 90%;
            max-width: 400px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-in-out;
        }

        .form-box {
            display: none;
            transition: opacity 0.5s ease-in-out;
        }

        .active {
            display: block;
            opacity: 1;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px; 
            transition: all 0.3s ease;
        }

        input,
        select {
            background: rgba(255, 255, 255, 0.8);
            color: #333;
        }

        input:focus,
        select:focus {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        button {
            background: #2ecc71;
            color: white;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        button:hover {
            background: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }

        .toggle-btn {
            cursor: pointer;
            color: yellow;
            text-decoration: underline;
            font-weight: bold;
            transition: 0.3s;
        }

        .toggle-btn:hover {
            color: #f1c40f;
            transform: translateY(-2px);
        }

        h2 {
            margin-bottom: 20px;
            animation: fadeIn 1s ease-in-out;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 10px;
        }

        .password-wrapper .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
        }

        .password-wrapper .toggle-password:hover {
            color: #333;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateX(-50%) scale(0.9);
            }

            20% {
                opacity: 1;
                transform: translateX(-50%) scale(1);
            }

            80% {
                opacity: 1;
                transform: translateX(-50%) scale(1);
            }

            100% {
                opacity: 0;
                transform: translateX(-50%) scale(0.9);
            }
        }

        .alert {
            width: 90%;
            max-width: 400px;
            padding: 15px;
            margin: 10px auto;
            text-align: center;
            border-radius: 5px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            animation: fadeInOut 4s ease-in-out;
        }

        .success {
            background-color: #2ecc71;
            color: white;
        }

        .error {
            background-color: #e74c3c;
            color: white;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            0% {
                opacity: 0;
                transform: translateX(-50px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            0% {
                opacity: 1;
                transform: translateX(0);
            }

            100% {
                opacity: 0;
                transform: translateX(50px);
            }
        }

        .form-box.active {
            animation: slideIn 0.5s ease-in-out;
        }

        .form-box:not(.active) {
            animation: slideOut 0.5s ease-in-out;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="loginBox" class="form-box <?php echo isset($_SESSION['just_registered']) ? '' : 'active'; ?>">
            <h2>Login</h2>
            <form method="POST">
                <input type="email" name="email" required placeholder="Email"><br>
                <div class="password-wrapper">
                    <input type="password" name="password" id="loginPassword" required placeholder="Password">
                    <i class="toggle-password fas fa-eye" onclick="togglePassword('loginPassword')"></i>
                </div><br>
                <button type="submit" name="login">Login</button>
            </form>
            <p class="toggle-btn" onclick="toggleForm()">Don't have an account? Register</p>
        </div>

        <div id="registerBox" class="form-box <?php echo isset($_SESSION['just_registered']) ? 'active' : ''; ?>">
            <h2>Register</h2>
            <form method="POST">
                <input type="text" name="fullname" required placeholder="Full Name"><br>
                <input type="text" name="username" required placeholder="Username"><br>
                <input type="email" name="email" required placeholder="Email"><br>
                <input type="text" name="phone" required placeholder="Phone Number"><br>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select><br>
                <div class="password-wrapper">
                    <input type="password" name="password" id="registerPassword" required placeholder="Password">
                </div><br>
                <button type="submit" name="register">Register</button>
            </form>
            <p class="toggle-btn" onclick="toggleForm()">Already have an account? Login</p>
        </div>

        <div id="subscribeBox" class="form-box">
            <h2>Subscribe to Newsletter</h2>
            <form method="POST" action="">
                <input type="email" name="email" required placeholder="Email"><br>
                <button type="submit" name="subscribe">Subscribe</button>
            </form>
        </div>
    </div>

    <script>
        function toggleForm() {
            document.getElementById('loginBox').classList.toggle('active');
            document.getElementById('registerBox').classList.toggle('active');
        }

        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.nextElementSibling;
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Automatically switch to login form if the user has just registered
        <?php if (isset($_SESSION['just_registered']) && $_SESSION['just_registered']): ?>
            document.getElementById('loginBox').classList.add('active');
            document.getElementById('registerBox').classList.remove('active');
            <?php unset($_SESSION['just_registered']); ?>
        <?php endif; ?>
    </script>
</body>

</html>