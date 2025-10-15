<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginCustomers.html");
    exit();
}

$servername = "localhost";
$dbname = "project_petalink";
$dbuser = "root";
$dbpass = "patricioMed";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$contact_number = trim($_POST['contact_number']);
$address = trim($_POST['address']);
$password = trim($_POST['password']);
$role = trim($_POST['role']);
$shop_name = isset($_POST['shop_name']) ? trim($_POST['shop_name']) : null;

// Basic validation
if (strlen($name) < 2 || strlen($email) < 5 || strlen($contact_number) < 5) {
    die("Invalid input.");
}

// Always update the original customer profile (keeps them as customer)
$stmt = $conn->prepare("UPDATE users 
    SET name = ?, email = ?, contact_number = ?, address = ? 
    WHERE id = ?");
$stmt->bind_param("ssssi", $name, $email, $contact_number, $address, $user_id);
$stmt->execute();
$stmt->close();

// ✅ If customer upgrades to owner
if ($role === 'owner') {
    // 1. Check if this email already exists in users table as owner
    $checkUser = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = 'owner'");
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $resultUser = $checkUser->get_result();

    if ($resultUser->num_rows === 0) {
        // Insert new owner account in users table
        $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        $insertUser = $conn->prepare("INSERT INTO users 
            (name, email, contact_number, address, role, password) 
            VALUES (?, ?, ?, ?, 'owner', ?)");
        $insertUser->bind_param("sssss", $name, $email, $contact_number, $address, $hashedPassword);
        if (!$insertUser->execute()) {
            die("Insert user failed: " . $insertUser->error);
        }
        $new_owner_user_id = $insertUser->insert_id;
        $insertUser->close();

        // Insert into flowershopowners table
        $insertOwner = $conn->prepare("INSERT INTO flowershopowners 
            (user_id, name, email, contact_number, address, shop_name, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $insertOwner->bind_param("isssss", $new_owner_user_id, $name, $email, $contact_number, $address, $shop_name);
        if (!$insertOwner->execute()) {
            die("Insert owner failed: " . $insertOwner->error);
        }
        $insertOwner->close();
    }
    $checkUser->close();
}

$conn->close();
echo "<script>window.top.location.reload();</script>";
exit();
?>
