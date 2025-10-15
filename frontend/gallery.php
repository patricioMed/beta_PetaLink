<?php
include '../backend/security.php'; // Links head  
?>
<?php
$conn = new mysqli("localhost", "root", "patricioMed", "oldSchool");
$result = $conn->query("SELECT flowerName, price, image_path FROM Anniversary");
?>
<h2>Flower Gallery</h2>
<?php while($row = $result->fetch_assoc()): ?>
  <div style="margin-bottom: 20px;">
    <img src="<?= $row['image_path'] ?>" width="150"><br>
    <strong><?= htmlspecialchars($row['flowerName']) ?></strong><br>
    ₱<?= number_format($row['price'], 2) ?>
  </div>
<?php endwhile; ?>
