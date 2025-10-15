<?php
session_start();

// ✅ Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id        = $_SESSION['user_id'];
$shop_name      = trim($_POST['shop_name']);
$address        = trim($_POST['address']);
$contact_number = trim($_POST['contact_number']);
$map_link       = trim($_POST['map_link']);

// ✅ Handle image upload
$shop_image = null;
if (isset($_FILES['shop_image']) && $_FILES['shop_image']['error'] === UPLOAD_ERR_OK) {
    $uploads_dir = '../frontend/uploads/';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }

    $file_name = time() . '_' . basename($_FILES['shop_image']['name']);
    $target_path = $uploads_dir . $file_name;

    if (move_uploaded_file($_FILES['shop_image']['tmp_name'], $target_path)) {
        $shop_image = $target_path;
    }
}

// ✅ Check if shop already exists
$check_sql = "SELECT * FROM flowershopOwners WHERE user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$existing_shop = $check_stmt->get_result()->fetch_assoc();

// ✅ Update or Insert logic
if ($existing_shop) {
    // Update
    if ($shop_image) {
        $update_sql = "UPDATE flowershopOwners 
                       SET shop_name=?, address=?, contact_number=?, map_link=?, shop_image=? 
                       WHERE user_id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssi", $shop_name, $address, $contact_number, $map_link, $shop_image, $user_id);
    } else {
        $update_sql = "UPDATE flowershopOwners 
                       SET shop_name=?, address=?, contact_number=?, map_link=? 
                       WHERE user_id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $shop_name, $address, $contact_number, $map_link, $user_id);
    }
    $stmt->execute();

} else {
    // Insert new shop
    $insert_sql = "INSERT INTO flowershopOwners (user_id, shop_name, address, contact_number, map_link, shop_image) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("isssss", $user_id, $shop_name, $address, $contact_number, $map_link, $shop_image);
    $stmt->execute();
}

// ✅ Redirect back to manageShop.php
header("Location: manageShop.php");
exit();
?>
