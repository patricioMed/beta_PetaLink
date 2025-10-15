<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    echo "Unauthorized";
    exit();
}

if(isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
    if($conn->connect_error) {
        die("Connection failed: ".$conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE checkout_history SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    if($stmt->execute()) {
        echo "Status updated successfully!";
    } else {
        echo "Failed to update status.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>
