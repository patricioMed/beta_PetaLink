<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../backend/login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "oldSchool");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orderId = $_POST['id'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

$update = $conn->prepare("UPDATE checkout_history SET rating = ?, comment = ? WHERE id = ? AND user_id = ?");
$update->bind_param("isii", $rating, $comment, $orderId, $_SESSION['user_id']);
$update->execute();

$conn->close();
header("Location: checkoutHistory.php");
exit();
?>
