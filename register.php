<?php
session_start();
include 'config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user'; // Default role

    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "❌ Email already exists!";
        $_SESSION['msg_type'] = "error";
    } else {
        $insert_query = "INSERT INTO users (name, email, phone, gender, password, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $name, $email, $phone, $gender, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Registration successful! Redirecting to login...";
            $_SESSION['msg_type'] = "success";
            header("refresh:3;url=index.php");
        } else {
            $_SESSION['message'] = "❌ Something went wrong. Please try again.";
            $_SESSION['msg_type'] = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Barangay Online Documentation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f8f8;
            position: relative;
        }
        .container {
            display: flex;
            width: 800px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .left-panel {
            width: 50%;
            background: #ede7f6;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .right-panel {
            width: 50%;
            padding: 40px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #6a1b9a;
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #4a148c;
        }
        .notification {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            font-weight: bold;
        }
        .error { background-color: #ff4d4d; }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="notification <?php echo $_SESSION['msg_type']; ?>" id="notif-box">
            <?php echo $_SESSION['message']; ?>
        </div>
        <script>
            document.getElementById("notif-box").style.display = "block";
            setTimeout(function() {
                document.getElementById("notif-box").style.display = "none";
            }, 3000);
        </script>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="container">
        <div class="left-panel">
            <h2>Welcome</h2>
            <p>Please fill out the form to register for an account.</p>
            <a href="index.php"><button>Login</button></a>
        </div>
        <div class="right-panel">
            <h2>Sign Up</h2>
            <form method="POST" action="register.php">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <select name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Register</button>
            </form>
        </div>
    </div>
</body>
</html>
