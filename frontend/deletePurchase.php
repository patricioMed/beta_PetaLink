<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../backend/loginCustomers.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $purchase_id = intval($_POST['id']);

    $conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];

    // Securely delete only if the purchase belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM purchases WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $purchase_id, $user_id);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}

header("Location: purchaseList.php"); // or wherever your list is
exit();
?>
