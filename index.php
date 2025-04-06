<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $_SESSION['message'] = "✅ Successfully logged in! Redirecting...";
                $_SESSION['msg_type'] = "success";
                header("refresh:3;url=dashboard.php"); // Redirect after 3 seconds
            }
        } else {
            $_SESSION['message'] = "❌ Invalid password!";
            $_SESSION['msg_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "❌ User not found!";
        $_SESSION['msg_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barangay Online Documentation</title>
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
        input, button {
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
        /* Notification Box */
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
        .error { background-color: #ff4d4d; } /* Red for error */
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
            <h2>Welcome Back</h2>
            <p>Please login to access your account.</p>
            <a href="register.php"><button>Register</button></a>
        </div>
        <div class="right-panel">
            <h2>Login</h2>
            <form method="POST" action="index.php">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
