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
    $getFlower = $conn->prepare("SELECT flowerName FROM Anniversary WHERE id = ? AND owner_id = ?");
    $getFlower->bind_param("ii", $deleteId, $owner_id);
    $getFlower->execute();
    $flowerRes = $getFlower->get_result();
    if ($flowerRow = $flowerRes->fetch_assoc()) {
        $flowerName = $flowerRow['flowerName'];

        $stmtDel = $conn->prepare("DELETE FROM Anniversary WHERE id = ? AND owner_id = ?");
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
$sql = "SELECT id, flowerName, price, image_path, availability FROM Anniversary WHERE owner_id = ?";
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
<title><?= $shopName ?> - Anniversary Flowers</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<!-- <link rel="stylesheet" href="CSS/ownerCategories.css"> -->
<!-- wag alisin pang view agad -->
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins", sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }

header {
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  background: #11001a;
  padding: 12px 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2px solid #660066;
  z-index: 1000;
}
/* Category Section */
.main-content {
  text-align: center;
  margin-top: 110px;
  padding: 30px;
  background: #2a0038;
  border-radius: 12px;
  border: 1px solid #660066;
}
h2 {
  font-size: 2rem;
  margin-bottom: 25px;
  color: white;
  font-weight: 600;
}
.category-buttons {
  display: flex;
  justify-content: center;
  gap: 18px;
  flex-wrap: wrap;
}
.category-button {
  padding: 12px 26px;
  font-size: 1rem;
  border-radius: 25px;
  background: #1a0026;
  color: #fff;
  font-weight: 500;
  cursor: pointer;
  border: 1px solid #660066;
  transition: all 0.3s ease;
}
.category-button:hover {
  background: #660066;
  color: white;
  border-color: #ff66cc;
}
.logo { display: flex; align-items: center; gap: 12px; }
.logo img { height: 55px; border-radius: 8px; }
.logo-text { display: flex; flex-direction: column; line-height: 1.2; }
.logo-text span:first-child { font-size: 1.6rem; font-weight: 700; color: #a81ea8ff; letter-spacing: 1px; }
.tagline { font-size: 0.8rem; font-weight: 400; color: #ccc; letter-spacing: 0.5px; margin-top: 2px; }

.search-bar { display:flex; align-items:center; margin-right: 20px;}
.search-bar input {
  padding:8px 12px; border:none; border-radius:8px 0 0 8px; outline:none; font-size: 15px; width: 220px;
}
.search-bar button {
  background:#660066; color:white; font-size: 15px; border:0.2px solid white; padding:7px 12px; border-radius:0 8px 8px 0; cursor:pointer;
}
.search-bar button:hover { background:#ff66cc; }

.header-top { display:flex; justify-content:space-between; align-items:center; margin: 20px 30px;}
.header-top {
  margin-top: 20px !important;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
  padding: 0 40px;
}


.back-btn a, .add-flower-btn {
  background:#1a0026; color:white; padding:10px 20px; border-radius:25px;
  text-decoration:none; font-weight:600; border:1px solid #660066; transition:0.3s;
}
.back-btn a:hover, .add-flower-btn:hover {
  background: #660066; color: white; border-color: #ff66cc;
}

.product-grid {
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap:20px;
  padding:0 30px 40px 30px;
}
.product-card {
  background:#2a0038; border:1px solid #660066; border-radius:12px;
  padding:15px; text-align:center; transition:transform 0.3s;
}
.product-card:hover { transform: translateY(-5px); }
.product-card h3 { color:#ff66cc; margin:10px 0; }
.product-card .price { font-weight:600; margin:10px 0; color:#fff; }
.availability { margin:5px 0; font-size:0.9rem; }
.edit-btn, .delete-btn {
  display:inline-block; margin:5px 4px; padding:8px 14px; border-radius:30px;
  text-decoration:none; color:white; font-size:0.9rem;
}
.edit-btn { background:#f0ad4e; }
.delete-btn { background:#d9534f; }

.modal {
  display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%;
  background:rgba(0,0,0,0.6); justify-content:center; align-items:center;
}
.modal-content {
  background:#2a0038; color:white; padding:20px; border-radius:12px; width:350px; text-align:center;
  border:1px solid #660066;
}
.modal-buttons { margin-top:20px; display:flex; justify-content:center; gap:15px; }
.modal-btn { padding:10px 18px; border:none; border-radius:6px; cursor:pointer; font-size:0.95rem; }
.confirm-btn { background:#d9534f; color:white; }
.cancel-btn { background:#6c757d; color:white; }
</style>
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
  <h2>Flower Category Anniversary</h2>
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
  <a href="manageAnniversary.php" class="add-flower-btn"><i class="fa-solid fa-plus"></i> Add Flower</a>
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
            <a href='manageAnniversary.php?edit=$id' class='edit-btn'><i class='fa-solid fa-pen'></i> Edit</a>
            <a href='#' class='delete-btn' onclick='openModal($id)'><i class='fa-solid fa-trash'></i> Delete</a>
        </div>";
    }
} else {
    echo "<p style='text-align:center; color:#ccc; grid-column:1/-1;'>No anniversary flowers found.</p>";
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
