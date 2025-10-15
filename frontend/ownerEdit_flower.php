<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid flower ID.");
}

$sql = "SELECT flowerName, price FROM Anniversary WHERE id = ? AND owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $_SESSION['owner_id']);
$stmt->execute();
$result = $stmt->get_result();
$flower = $result->fetch_assoc();

if (!$flower) {
    die("Flower not found or access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flowerName = $_POST['flowerName'];
    $price = $_POST['price'];

    $update = $conn->prepare("UPDATE Anniversary SET flowerName = ?, price = ? WHERE id = ? AND owner_id = ?");
    $update->bind_param("sdii", $flowerName, $price, $id, $_SESSION['owner_id']);
    if ($update->execute()) {
        header("Location: manageFlowers.php");
        exit();
    } else {
        echo "Error updating flower.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Flower</title></head>
<body>
<h2>Edit Flower</h2>
<form method="POST">
  Name: <input type="text" name="flowerName" value="<?= htmlspecialchars($flower['flowerName']) ?>" required><br>
  Price: <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($flower['price']) ?>" required><br>
  <button type="submit">Update</button>
</form>
</body>
</html>
