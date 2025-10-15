<?php
session_start();

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    die("<p style='color:red;'>Error: Owner not logged in. Please log in to continue.</p>");
}

$owner_id = $_SESSION['owner_id'];

// Connection
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flowerName = $_POST['flowerName'];
    $price = $_POST['price'];

    $image = $_FILES['image'];
    $targetDir = "uploads/";
    $imageName = basename($image["name"]);
    $targetFile = $targetDir . time() . "_" . $imageName;

    // Only allow specific types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (in_array($ext, $allowedTypes)) {
        if (move_uploaded_file($image["tmp_name"], $targetFile)) {
            // Save to database with owner_id
            $stmt = $conn->prepare("INSERT INTO Anniversary (owner_id, flowerName, price, image_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isis", $owner_id, $flowerName, $price, $targetFile);

            if ($stmt->execute()) {
                echo "<p style='color:green;'>Flower added successfully!</p>";
            } else {
                echo "<p style='color:red;'>Database error: " . $stmt->error . "</p>";
            }

            $stmt->close();
        } else {
            echo "<p style='color:red;'>Failed to upload image.</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid image type. Allowed: JPG, PNG, GIF, WEBP.</p>";
    }
}

$conn->close();
?>

<!-- Upload Form -->
<!DOCTYPE html>
<html>
<head>
  <title>Add Flower</title>
</head>
<body>
  <h2>Add New Flower</h2>
  <form action="" method="post" enctype="multipart/form-data">
    <label>Flower Name:</label><br>
    <input type="text" name="flowerName" required><br><br>

    <label>Price:</label><br>
    <input type="number" name="price" required><br><br>

    <label>Select Image:</label><br>
    <input type="file" name="image" accept="image/*" required><br><br>

    <input type="submit" value="Add Flower">
  </form>
</body>
</html>
