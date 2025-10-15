<?php
header('Content-Type: application/json');
session_start();

// ✅ Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "patricioMed"; // change if needed
$dbname = "project_petalink";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Fetch all users
$sql = "SELECT id, name, email, role, contact_number, address FROM users ORDER BY id ASC";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode($users);

$conn->close();
?>
