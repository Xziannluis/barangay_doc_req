<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}

// Fetch user name
$user_name = "";
$user_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM document_requests WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Handle Update Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_id'])) {
    $id = $_POST['update_id'];
    $new_document = $_POST['document_type'];
    $purpose = $_POST['purpose'];  // Ensure the purpose is updated in the database
    $stmt = $conn->prepare("UPDATE document_requests SET document_type = ?, purpose = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssii", $new_document, $purpose, $id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Fetch user requests
$query = "SELECT id, document_type, purpose, status, request_date FROM document_requests WHERE user_id = ? ORDER BY request_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Barangay Online Documentation</title>
    <style>
       * {
    box-sizing: border-box;
}

body {
    display: flex;
    font-family: Arial, sans-serif;
    margin: 0;
    background-color: #f8f9fa;
}

.sidebar {
    width: 250px;
    background-color: #2c3e50;
    color: white;
    height: 100vh;
    padding: 20px;
}

.sidebar h2 {
    margin-top: 0;
    margin-bottom: 20px;
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
    font-size: 16px;
}

.sidebar ul li a:hover {
    text-decoration: underline;
}

.main-content {
    flex-grow: 1;
    padding: 30px;
    background: white;
    border-radius: 10px;
    margin: 20px auto;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 1000px;
}

h2, h3 {
    margin-top: 0;
    color: #2c3e50;
}

.table-container {
    margin-top: 20px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 16px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background-color: #007bff;
    color: white;
    text-transform: uppercase;
    font-size: 14px;
}

.status-pending { color: orange; font-weight: bold; }
.status-approved { color: green; font-weight: bold; }
.status-denied { color: red; font-weight: bold; }

.actions a {
    margin-right: 10px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.actions a.edit { color: #007bff; }
.actions a.delete { color: #dc3545; }

.edit-form {
    margin: 40px auto;
    padding: 30px;
    background: #f1f1f1;
    border: 1px solid #ccc;
    border-radius: 12px;
    width: 600px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: left;
}

.edit-form h3 {
    margin-top: 0;
    color: #333;
    font-size: 22px;
    text-align: center;
}

.edit-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
    font-size: 15px;
}

.edit-form input[type="text"],
.edit-form select {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
}

.edit-form button {
    background-color: #007bff;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    transition: background 0.3s;
}

.edit-form button:hover {
    background-color: #0056b3;
}

@media (max-width: 768px) {
    body {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        height: auto;
    }

    .main-content {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .edit-form {
        width: 90%;
        padding: 20px;
    }
}

    </style>
</head>
<body>
    <div class="sidebar">
        <h2 style="color: white;">User Panel</h2>
        <ul>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="request.php">📄 Request a Document</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p>Your Recent Requests</p>

        <div class="table-container">
            <table>
                <tr>
                    <th>DOCUMENT</th>
                    <th>DATE REQUESTED</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                        <td><?php echo date('F j, Y', strtotime($row['request_date'])); ?></td>
                        <td class="status-<?php echo strtolower($row['status']); ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td class="actions">
                            <a class="edit" href="dashboard.php?edit=<?php echo $row['id']; ?>">Edit</a>
                            <a class="delete" href="dashboard.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this request?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <?php
        // Display update form if 'edit' is set
        if (isset($_GET['edit'])) {
            $edit_id = $_GET['edit'];
            $stmt = $conn->prepare("SELECT document_type, purpose FROM document_requests WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $edit_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($doc_type, $purpose); // Fetch both document type and purpose
            if ($stmt->fetch()) {
                echo '<div class="edit-form">
    <h3>Edit Request</h3>
    <form method="POST" action="dashboard.php">
        <input type="hidden" name="update_id" value="' . $edit_id . '">
        
        <label for="document_type">Document Type:</label>
        <select name="document_type" required>
            <option value="Barangay Clearance" ' . ($doc_type == 'Barangay Clearance' ? 'selected' : '') . '>Barangay Clearance</option>
            <option value="Certificate of Residency" ' . ($doc_type == 'Certificate of Residency' ? 'selected' : '') . '>Certificate of Residency</option>
            <option value="Certificate of Indigency" ' . ($doc_type == 'Certificate of Indigency' ? 'selected' : '') . '>Certificate of Indigency</option>
            <option value="Certificate of Good Moral Character" ' . ($doc_type == 'Certificate of Good Moral Character' ? 'selected' : '') . '>Certificate of Good Moral Character</option>
            <option value="Barangay Blotter Report" ' . ($doc_type == 'Barangay Blotter Report' ? 'selected' : '') . '>Barangay Blotter Report</option>
            <option value="Barangay Protection Order" ' . ($doc_type == 'Barangay Protection Order' ? 'selected' : '') . '>Barangay Protection Order</option>
            <option value="Barangay Business Permit" ' . ($doc_type == 'Barangay Business Permit' ? 'selected' : '') . '>Barangay Business Permit</option>
        </select>
                
        <label for="purpose">Purpose:</label>
        <input type="text" name="purpose" value="' . htmlspecialchars($purpose) . '" required>

        <button type="submit">Update</button>
    </form>
</div>';
            }
            $stmt->close();
        }
        ?>
    </div>
</body>
</html>
