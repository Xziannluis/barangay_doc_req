<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch admin name
$admin_name = "";
$admin_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($admin_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($admin_name);
$stmt->fetch();
$stmt->close();

// Check view mode
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

// Fetch document requests
if ($view === 'dashboard') {
    $query = "SELECT dr.id, u.name, dr.document_type, dr.purpose, dr.status, dr.request_date 
              FROM document_requests dr 
              JOIN users u ON dr.user_id = u.id 
              ORDER BY dr.request_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Fetch registered users
if ($view === 'users') {
    $user_query = "SELECT id, name, email, phone, gender, role FROM users ORDER BY name ASC";
    $user_result = $conn->query($user_query);
}

// Handle approval/denial
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if (!in_array($action, ['approve', 'deny'])) {
        die("Invalid action.");
    }

    $update_query = "UPDATE document_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $status = $action === 'approve' ? 'Approved' : 'Denied';
    $stmt->bind_param("si", $status, $request_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        die("Database error: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Barangay Online Documentation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            padding: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background: white;
            border-radius: 10px;
            margin: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .status-approved {
            color: green;
            font-weight: bold;
        }

        .status-denied {
            color: red;
            font-weight: bold;
        }

        td form {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .btn {
            padding: 5px 10px;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            width: 100px;
            text-align: center;
        }

        .btn-approve {
            background-color: green;
        }

        .btn-deny {
            background-color: red;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin_dashboard.php">🏠 Dashboard</a></li>
            <li><a href="admin_dashboard.php?view=users">👥 Registered Users</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>

        <?php if ($view === 'dashboard'): ?>
            <p>Recent Document Requests</p>
            <table>
                <tr>
                    <th>NAME</th>
                    <th>DOCUMENT</th>
                    <th>PURPOSE</th>
                    <th>DATE REQUESTED</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['document_type']) ?></td>
                        <td><?= htmlspecialchars($row['purpose']) ?></td>
                        <td><?= date('F j, Y', strtotime($row['request_date'])) ?></td>
                        <td class="status-<?= strtolower($row['status']) ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'Pending'): ?>
                                <form method="POST" action="admin_dashboard.php">
                                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="action" value="deny" class="btn btn-deny">Deny</button>
                                    <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                                </form>
                            <?php else: ?>
                                No action needed
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

        <?php elseif ($view === 'users'): ?>
            <p>All Registered Users</p>
            <table>
                <tr>
                    <th>NAME</th>
                    <th>EMAIL</th>
                    <th>PHONE</th>
                    <th>GENDER</th>
                </tr>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td><?= htmlspecialchars($user['gender']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
