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
if ($id) {
    $stmt = $conn->prepare("DELETE FROM Birthday WHERE id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['owner_id']);
    $stmt->execute();
}

header("Location: manageFlowers_category.php");
exit();
?>
