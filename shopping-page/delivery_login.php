<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'shopping';

// Use the same database connection as delivery.php
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle registration
if (isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $location = $conn->real_escape_string($_POST['location']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<div class='alert error'>Passwords do not match. Please try again.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $created_at = date('Y-m-d H:i:s');

        $check_query = "SELECT * FROM deliveryuser WHERE email='$email'";
        $check_result = $conn->query($check_query);
        if (!$check_result) {
            echo "<div class='alert error'>Error checking existing user: " . $conn->error . "</div>";
            exit;
        }

        if ($check_result->num_rows > 0) {
            echo "<div class='alert error'>Email already registered. Please login.</div>";
        } else {
            $query = "INSERT INTO deliveryuser (username, email, phone, location, password, created_at) 
                      VALUES ('$username', '$email', '$phone', '$location', '$hashed_password', '$created_at')";
            if ($conn->query($query)) {
                $_SESSION['just_registered'] = true;
                echo "<div class='alert success'>Registration successful. Please login.</div>";
            } else {
                echo "<div class='alert error'>Error: " . $conn->error . "</div>";
            }
        }
    }
}

// Handle login
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM deliveryuser WHERE email='$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['location'] = $row['location']; // Store location in session
            echo "<div class='alert success'>Login successful. Redirecting...</div>";
            echo "<script>setTimeout(function(){ window.location.href='delivery.php'; }, 1500);</script>";
        } else {
            echo "<div class='alert error'>Incorrect password</div>";
        }
    } else {
        echo "<div class='alert error'>No user found. Please register first.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login & Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            background: url('download.jpg') no-repeat center center fixed;
            background-size: cover;
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
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-in-out;
            backdrop-filter: blur(5px);
        }

        .form-box {
            display: none;
            transition: all 0.5s ease-in-out;
        }

        .active {
            display: block;
            opacity: 1;
        }

        h2 {
            margin-bottom: 25px;
            color: #2ecc71;
            font-size: 28px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.02);
            box-shadow: 0 0 15px rgba(46, 204, 113, 0.4);
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px;
            margin: 15px 0 10px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        button:hover {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        .toggle-btn {
            cursor: pointer;
            color: #f1c40f;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 15px;
        }

        .toggle-btn:hover {
            color: #f39c12;
            text-decoration: underline;
            transform: scale(1.05);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
            transition: all 0.3s ease;
        }

        .toggle-password:hover {
            color: #34495e;
        }

        .alert {
            width: 90%;
            max-width: 400px;
            padding: 15px;
            margin: 10px auto;
            text-align: center;
            border-radius: 8px;
            font-weight: bold;
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            animation: fadeInOut 4s ease-in-out;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }

        .error {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            15%, 85% { opacity: 1; transform: translateX(-50%) translateY(0); }
            100% { opacity: 0; transform: translateX(-50%) translateY(-20px); }
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(-30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Additional features */
        .strength-meter {
            height: 5px;
            background: #ddd;
            border-radius: 3px;
            margin: 5px 0 15px;
            overflow: hidden;
        }

        .strength-meter::after {
            content: '';
            display: block;
            height: 100%;
            width: 0;
            background: transparent;
            transition: width 0.3s, background 0.3s;
        }

        input[type="password"]:focus ~ .strength-meter::after {
            width: 25%;
            background: #e74c3c;
        }

        input[type="password"]:valid:not(:focus) ~ .strength-meter::after {
            width: 100%;
            background: #2ecc71;
        }

        .terms {
            text-align: left;
            margin: 15px 0;
            font-size: 14px;
        }

        .terms input {
            width: auto;
            margin-right: 10px;
        }

        .terms label {
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <div id="loginBox" class="form-box <?php echo isset($_SESSION['just_registered']) ? '' : 'active'; ?>">
        <h2>Delivery Login</h2>
        <form method="POST">
            <input type="email" name="email" required placeholder="Email Address"><br>
            <div class="password-wrapper">
                <input type="password" name="password" id="loginPassword" required placeholder="Password">
                <i class="toggle-password fas fa-eye" onclick="togglePassword('loginPassword')"></i>
            </div><br>
            <button type="submit" name="login">Login</button>
        </form>
        <p class="toggle-btn" onclick="toggleForm()">Don't have an account? Register here</p>
    </div>

    <div id="registerBox" class="form-box <?php echo isset($_SESSION['just_registered']) ? 'active' : ''; ?>">
        <h2>Delivery Registration</h2>
        <form method="POST">
            <input type="text" name="username" required placeholder="Full Name"><br>
            <input type="email" name="email" required placeholder="Email Address"><br>
            <input type="text" name="phone" required placeholder="Phone Number" pattern="[0-9]{10,15}"><br>
            <input type="text" name="location" required placeholder="Delivery Area"><br>
            <div class="password-wrapper">
                <input type="password" name="password" id="registerPassword" required placeholder="Password" minlength="6">
                <i class="toggle-password fas fa-eye" onclick="togglePassword('registerPassword')"></i>
                <div class="strength-meter"></div>
            </div>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirmPassword" required placeholder="Confirm Password" minlength="6">
                <i class="toggle-password fas fa-eye" onclick="togglePassword('confirmPassword')"></i>
            </div>
            <div class="terms">
                <input type="checkbox" id="terms" required>
                <label for="terms">I agree to the terms and conditions</label>
            </div>
            <button type="submit" name="register">Create Account</button>
        </form>
        <p class="toggle-btn" onclick="toggleForm()">Already have an account? Login here</p>
    </div>
</div>

<script>
    function toggleForm() {
        document.getElementById('loginBox').classList.toggle('active');
        document.getElementById('registerBox').classList.toggle('active');
        // Clear any session flag after toggling
        <?php unset($_SESSION['just_registered']); ?>
    }

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling;
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    // Password strength indicator
    document.getElementById('registerPassword').addEventListener('input', function(e) {
        const password = e.target.value;
        const strengthMeter = e.target.nextElementSibling.nextElementSibling;
        const strength = calculatePasswordStrength(password);
        
        strengthMeter.style.backgroundColor = '#ddd';
        strengthMeter.style.height = '5px';
        
        if (password.length > 0) {
            strengthMeter.style.backgroundColor = strength.color;
            strengthMeter.style.height = '5px';
            strengthMeter.style.width = strength.width;
        }
    });

    function calculatePasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length > 7) strength += 1;
        if (password.length > 10) strength += 1;
        
        // Character variety checks
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Determine color and width based on strength
        if (strength < 2) {
            return { color: '#e74c3c', width: '25%' };
        } else if (strength < 4) {
            return { color: '#f39c12', width: '50%' };
        } else if (strength < 6) {
            return { color: '#3498db', width: '75%' };
        } else {
            return { color: '#2ecc71', width: '100%' };
        }
    }
</script>
</body>
</html>