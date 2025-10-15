<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../backend/login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// 🔒 Get the role from users table
$role_query = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$role_query->bind_param("i", $user_id);
$role_query->execute();
$role_query->bind_result($role);
$role_query->fetch();
$role_query->close();

// ❌ Block if not flower shop owner
if ($role !== 'owner') {
    echo "<h2>Access Denied. You are not authorized to view this page.</h2>";
    exit();
}

// ✅ Retrieve all orders made by customers
$sql = "SELECT 
            ch.user_id, 
            u.name AS customer_name, 
            ch.flowerName, 
            ch.price, 
            ch.quantity, 
            ch.total, 
            ch.purchase_date, 
            ch.payment_method
        FROM 
            checkout_history ch
        JOIN 
            users u ON ch.user_id = u.user_id
        WHERE 
            u.role = 'customer'
        ORDER BY 
            ch.purchase_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Orders</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background: #f3f3f3;
    }
    h2 {
      text-align: center;
      color: #188043;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      margin-top: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: center;
    }
    th {
      background-color: #188043;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
  </style>
</head>
<body>
  <h2>All Customer Orders</h2>

  <?php if ($result->num_rows > 0): ?>
    <table>
      <tr>
        <th>Customer</th>
        <th>Flower</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Total</th>
        <th>Payment</th>
        <th>Purchase Date</th>
      </tr>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['customer_name']) ?></td>
          <td><?= htmlspecialchars($row['flowerName']) ?></td>
          <td>₱<?= number_format($row['price'], 2) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td>₱<?= number_format($row['total'], 2) ?></td>
          <td><?= htmlspecialchars($row['payment_method']) ?></td>
          <td><?= htmlspecialchars($row['purchase_date']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No orders found.</p>
  <?php endif; ?>

<?php $conn->close(); ?>
</body>
</html>
