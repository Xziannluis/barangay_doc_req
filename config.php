<?php
$host = "localhost"; // Server host (kung online na, ilisi)
$dbname = "barangay_doc_req"; // I-match sa imong database name
$username = "root"; // Default sa XAMPP
$password = ""; // Default sa XAMPP, wala ni siya password

$conn = new mysqli($host, $username, $password, $dbname);

// I-check kung naay error sa connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
