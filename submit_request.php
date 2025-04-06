<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$document = $_POST['document'] ?? null;
$purpose = $_POST['purpose'] ?? null;

// Validate input fields
if (!$document || !$purpose) {
    die("Missing required fields.");
}

// Validate document type against allowed ENUM values
$allowed_documents = [
    'Barangay Clearance',
    'Certificate of Residency',
    'Certificate of Indigency',
    'Certificate of Good Moral Character',
    'Barangay Blotter Report',
    'Barangay Protection Order',
    'Barangay Business Permit'
];
if (!in_array($document, $allowed_documents)) {
    die("Invalid document type.");
}

// Debugging: Log the submitted data
file_put_contents('debug.log', "Submitted data: " . print_r($_POST, true), FILE_APPEND);

// Insert the request into the database
$query = "INSERT INTO document_requests (user_id, document_type, purpose, status, request_date) 
          VALUES (?, ?, ?, 'Pending', NOW())";
$stmt = $conn->prepare($query);

// Debugging: Log database errors
if (!$stmt) {
    file_put_contents('debug.log', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("iss", $user_id, $document, $purpose);

if ($stmt->execute()) {
    // Debugging: Log successful insertion
    file_put_contents('debug.log', "Request successfully inserted for user ID $user_id\n", FILE_APPEND);
    // Redirect to the user dashboard
    header("Location: dashboard.php");
    exit();
} else {
    file_put_contents('debug.log', "Database error: " . $stmt->error . "\n", FILE_APPEND);
    die("Database error: " . $stmt->error);
}
?>