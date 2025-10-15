<?php
include '../backend/security.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../backend/login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get owner_id
$stmtOwner = $conn->prepare("SELECT owner_id, shop_name FROM flowershopOwners WHERE user_id = ?");
$stmtOwner->bind_param("i", $_SESSION['user_id']);
$stmtOwner->execute();
$resOwner = $stmtOwner->get_result();
if ($resOwner->num_rows === 0) {
    die("You don't have a registered shop yet.");
}
$ownerData = $resOwner->fetch_assoc();
$owner_id = $ownerData['owner_id'];
$shopName = htmlspecialchars($ownerData['shop_name']);

// DELETE
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $getFlower = $conn->prepare("SELECT flowerName FROM Others WHERE id = ? AND owner_id = ?");
    $getFlower->bind_param("ii", $deleteId, $owner_id);
    $getFlower->execute();
    $flowerRes = $getFlower->get_result();
    if ($flowerRow = $flowerRes->fetch_assoc()) {
        $flowerName = $flowerRow['flowerName'];

        $stmtDel = $conn->prepare("DELETE FROM Others WHERE id = ? AND owner_id = ?");
        $stmtDel->bind_param("ii", $deleteId, $owner_id);
        $stmtDel->execute();

        $stmtDel3 = $conn->prepare("DELETE FROM purchases WHERE owner_id = ? AND flowerName = ?");
        $stmtDel3->bind_param("is", $owner_id, $flowerName);
        $stmtDel3->execute();

        echo "<meta http-equiv='refresh' content='1;url=" . strtok($_SERVER['REQUEST_URI'], '?') . "'>";
    }
}

// SEARCH
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT id, flowerName, price, image_path, availability FROM Others WHERE owner_id = ?";
$params = [$owner_id];
$types = "i";

if (!empty($search)) {
    $sql .= " AND flowerName LIKE ?";
    $types .= "s";
    $params[] = "%$search%";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= $shopName ?> - Others Flowers</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/ownerCategories.css">
</head>
<body>

<header>
  <div class="logo">
    <img src="Images/PetaLink_logo.png" alt="PetaLink Logo" />
    <div class="logo-text">
      <span>PETALINK</span>
      <span class="tagline">Powered by petals, driven by links</span>
    </div>
  </div>
  <form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search flowers..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</header>
<div class="main-content">
  <h2>Flower Category Others</h2>
  <div class="category-buttons">
    <button onclick="location.href='ownerCategory_anniversary.php'" class="category-button"><i class="fas fa-heart"></i> Anniversary</button>
    <button onclick="location.href='ownerCategory_birthday.php'" class="category-button"><i class="fas fa-birthday-cake"></i> Birthday</button>
    <button onclick="location.href='ownerCategory_valentines.php'" class="category-button"><i class="fas fa-gift"></i> Valentines</button>
    <button onclick="location.href='ownerCategory_sympathy.php'" class="category-button"><i class="fas fa-hand-holding-heart"></i> Sympathy</button>
    <button onclick="location.href='ownerCategory_others.php'" class="category-button"><i class="fas fa-leaf"></i> Others</button>
  </div>
</div>
<div class="header-top" style="margin-top:100px;">
  <!-- <div class="back-btn"><a href="ownerDashboard.php"><i class="fa-solid fa-arrow-left"></i> Back</a></div> -->
  <a href="manageOthers.php" class="add-flower-btn"><i class="fa-solid fa-plus"></i> Add products</a>
</div>

<section class="product-grid">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $flowerName = htmlspecialchars($row['flowerName']);
        $price = number_format($row['price'], 2);
        $img = htmlspecialchars($row['image_path']);
        $availability = $row['availability'] ? "<span style='color:lime;'>Available</span>" : "<span style='color:red;'>Unavailable</span>";

        echo "
        <div class='product-card'>
            <img src='$img' alt='$flowerName' style='width:100%; height:180px; object-fit:cover; border-radius:10px;' />
            <h3>$flowerName</h3>
            <p class='price'>₱ $price</p>
            <p class='availability'>$availability</p>
            <a href='manageOthers.php?edit=$id' class='edit-btn'><i class='fa-solid fa-pen'></i> Edit</a>
            <a href='#' class='delete-btn' onclick='openModal($id)'><i class='fa-solid fa-trash'></i> Delete</a>
        </div>";
    }
} else {
    echo "<p style='text-align:center; color:#ccc; grid-column:1/-1;'>No Others flowers found.</p>";
}
?>
</section>

<div class="modal" id="deleteModal">
  <div class="modal-content">
    <h3>Confirm Deletion</h3>
    <p>Are you sure you want to delete this flower?</p>
    <div class="modal-buttons">
      <button class="modal-btn confirm-btn" id="confirmDelete">Delete</button>
      <button class="modal-btn cancel-btn" onclick="closeModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
let deleteId = null;
function openModal(id) {
  deleteId = id;
  document.getElementById("deleteModal").style.display = "flex";
}
function closeModal() {
  document.getElementById("deleteModal").style.display = "none";
  deleteId = null;
}
document.getElementById("confirmDelete").addEventListener("click", function() {
  if (deleteId) {
    window.location.href = "?delete=" + deleteId;
  }
});
</script>
</body>
</html>
