<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get owner_id and shop name
$ownerStmt = $conn->prepare("SELECT owner_id, shop_name FROM flowershopOwners WHERE user_id = ?");
$ownerStmt->bind_param("i", $user_id);
$ownerStmt->execute();
$ownerResult = $ownerStmt->get_result();

if ($ownerRow = $ownerResult->fetch_assoc()) {
    $owner_id = $ownerRow['owner_id'];
    $shopName = htmlspecialchars($ownerRow['shop_name']);
} else {
    echo "<p>You are not registered as a flower shop owner.</p>";
    exit();
}

$feedbackMsg = "";
$editMode = false;
$editData = [];

// DELETE
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $getFlower = $conn->prepare("SELECT flowerName, image_path FROM Others WHERE id = ? AND owner_id = ?");
    $getFlower->bind_param("ii", $deleteId, $owner_id);
    $getFlower->execute();
    $flowerResult = $getFlower->get_result();
    if ($flowerRow = $flowerResult->fetch_assoc()) {
        $flowerName = $flowerRow['flowerName'];
        $oldImage = $flowerRow['image_path'];

        $stmt = $conn->prepare("DELETE FROM Others WHERE id = ? AND owner_id = ?");
        $stmt->bind_param("ii", $deleteId, $owner_id);
        $stmt->execute();

        $stmt2 = $conn->prepare("DELETE FROM flowers WHERE owner_id = ? AND flowerName = ?");
        $stmt2->bind_param("is", $owner_id, $flowerName);
        $stmt2->execute();

        if (file_exists($oldImage)) unlink($oldImage);

        $feedbackMsg = "<p style='color:lime;'>Flower deleted successfully.</p>";
        echo "<meta http-equiv='refresh' content='2;url=" . strtok($_SERVER['REQUEST_URI'], '?') . "'>";
    }
}

// EDIT
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT flowerName, price, image_path, availability FROM Others WHERE id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $editId, $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $editMode = true;
        $editData = $row;
        $editData['id'] = $editId;
    }
}

// ADD/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flowerName = $_POST['flowerName'];
    $price = $_POST['price'];
    $availability = intval($_POST['availability']);

    if (isset($_POST['edit_id'])) {
        $editId = intval($_POST['edit_id']);
        $newImagePath = $editData['image_path'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = $_FILES['image'];
            $targetDir = "uploads/";
            $imageName = basename($image["name"]);
            $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg','jpeg','png','gif','webp'];

            if (in_array($ext, $allowedTypes)) {
                $newImagePath = $targetDir . time() . "_" . $imageName;
                if (move_uploaded_file($image["tmp_name"], $newImagePath)) {
                    if (file_exists($editData['image_path'])) unlink($editData['image_path']);
                } else {
                    $feedbackMsg = "<p style='color:red;'>Failed to upload new image.</p>";
                }
            } else {
                $feedbackMsg = "<p style='color:red;'>Invalid image type.</p>";
            }
        }

        $stmt = $conn->prepare("UPDATE Others SET flowerName = ?, price = ?, image_path = ?, availability = ? WHERE id = ? AND owner_id = ?");
        $stmt->bind_param("sdsiis", $flowerName, $price, $newImagePath, $availability, $editId, $owner_id);
        $stmt->execute();

        $stmt2 = $conn->prepare("UPDATE flowers SET flowerName = ?, price = ?, image = ?, availability = ? WHERE owner_id = ? AND flowerName = ?");
        $stmt2->bind_param("sdsiss", $flowerName, $price, $newImagePath, $availability, $owner_id, $editData['flowerName']);
        $stmt2->execute();

        $feedbackMsg = "<p style='color:lime;'>Flower updated successfully.</p>";
        echo "<meta http-equiv='refresh' content='2;url=" . $_SERVER['PHP_SELF'] . "'>";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = $_FILES['image'];
            $targetDir = "uploads/";
            $imageName = basename($image["name"]);
            $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg','jpeg','png','gif','webp'];

            if (in_array($ext, $allowedTypes)) {
                $targetFile = $targetDir . time() . "_" . $imageName;
                if (move_uploaded_file($image["tmp_name"], $targetFile)) {
                    $stmt = $conn->prepare("INSERT INTO Others (owner_id, flowerName, price, image_path, availability) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("isdis", $owner_id, $flowerName, $price, $targetFile, $availability);
                    $stmt->execute();

                    $stmt2 = $conn->prepare("INSERT INTO flowers (owner_id, flowerName, description, price, image, availability, created_at) VALUES (?, ?, '', ?, ?, ?, NOW())");
                    $stmt2->bind_param("isdis", $owner_id, $flowerName, $price, $targetFile, $availability);
                    $stmt2->execute();

                    $feedbackMsg = "<p style='color:lime;'>Flower added successfully.</p>";
                    echo "<meta http-equiv='refresh' content='2;url=" . $_SERVER['PHP_SELF'] . "'>";
                } else {
                    $feedbackMsg = "<p style='color:red;'>Failed to upload image.</p>";
                }
            } else {
                $feedbackMsg = "<p style='color:red;'>Invalid image type.</p>";
            }
        } else {
            $feedbackMsg = "<p style='color:red;'>Please upload an image.</p>";
        }
    }
}

// SEARCH filter
$search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";
$stmt = $conn->prepare("SELECT id, flowerName, price, image_path, availability FROM Others WHERE owner_id = ? AND flowerName LIKE ?");
$stmt->bind_param("is", $owner_id, $search);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $shopName ?> - Manage Others Flowers</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/manageCategories.css">
<!-- <style>
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
.logo { display: flex; align-items: center; gap: 12px; }
.logo img { height: 55px; border-radius: 8px; }
.logo-text { display: flex; flex-direction: column; line-height: 1.2; }
.logo-text span:first-child { font-size: 1.6rem; font-weight: 700; color: #a81ea8ff; letter-spacing: 1px; }
.tagline { font-size: 0.8rem; font-weight: 400; color: #ccc; letter-spacing: 0.5px; margin-top: 2px; }

/* Search bar in header */
.search-bar {
  display: flex;
  align-items: center;
  height: 50px;
  background: #11001a;
  border:1px solid #11001a;
  border-radius: 8px;
  overflow: hidden;
  width: 300px; /* adjust width as needed */
}

.search-bar input {
  flex: 1;
  padding: 8px 12px;
  border: none;
  outline: none;
  color: black;
  font-size: 0.95rem;
  margin-top: 15px;
}

.search-bar button {
  padding: 8px 14px;
  border: none;
  border-top-right-radius: 10px;
  border-bottom-right-radius: 10px;
  background: #660066;
  color: white;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.3s;
}

.search-bar button:hover {
  background: #ff66cc;
}

/* rest same */
.main-content { margin-top:120px; padding:30px; display:flex; gap:30px; flex-wrap:wrap; }
.form-section { flex:1; min-width:300px; }
form { background:#2a0038; padding:20px; border-radius:12px; border:1px solid #660066; }
form input[type="text"], form input[type="number"], form input[type="file"] {
  width:100%; margin-bottom:15px; padding:8px; border-bottom-left-radius:8px;
  border-top-left-radius:8px; border:1px solid #660066;
  background:#11001a; color:white;
}

.availability{
  margin-bottom: 15px;
}
.back-btn, .submit-btn {
  display:inline-block; margin-bottom:20px; padding:12px 20px;
  background:#1a0026; color:white; text-decoration:none;
  border-radius:25px; border:1px solid #660066;
}
.back-btn:hover, .submit-btn:hover { background:#660066; border-color:#ff66cc; }
.message { text-align:center; margin-bottom:15px; }
.flowers-section { flex:2; min-width:400px; max-height:70vh; overflow-y:auto; }
.product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px; }
.product-card { background:#2a0038; border:1px solid #660066; border-radius:12px; padding:15px; text-align:center; }
.product-card img { width:100%; height:160px; object-fit:cover; border-radius:12px; }
.product-info h3 { color:#ff66cc; margin:10px 0; }
.view-button { padding:8px 14px; border-radius:30px; text-decoration:none; color:white; font-size:0.9rem; }
.edit-btn { background:#f0ad4e; } .delete-btn { background:#d9534f; }
.modal { display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; }
.modal-content { background:#2a0038; color:white; padding:20px; border-radius:12px; width:350px; text-align:center; border:1px solid #660066; }
.modal-buttons { margin-top:20px; display:flex; justify-content:center; gap:15px; }
.modal-btn { padding:10px 18px; border:none; border-radius:6px; cursor:pointer; }
.confirm-btn { background:#d9534f; color:white; } .cancel-btn { background:#6c757d; color:white; }
</style> -->
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
  <form method="GET" action="" class="search-bar">
    <input style="background-color: #fff;" type="text" name="search" placeholder="Search flowers..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</header>

<div class="main-content">
  <div class="form-section">
    <a href="ownerCategory_Others.php" class="back-btn"><i class="fa-sharp fa-solid fa-arrow-left"></i> Back</a>
    <h2><?= $editMode ? "Edit Flower" : "Add New Flower" ?></h2>
    <div class="message"><?= $feedbackMsg ?></div>
    <form action="" method="post" enctype="multipart/form-data">
      <input type="text" name="flowerName" value="<?= $editMode ? htmlspecialchars($editData['flowerName']) : '' ?>" placeholder="Flower Name" required>
      <input type="number" name="price" value="<?= $editMode ? htmlspecialchars($editData['price']) : '' ?>" placeholder="Price" required>
      <?php if (!$editMode): ?>
        <input type="file" name="image" accept="image/*" required>
      <?php else: ?>
        <input type="file" name="image" accept="image/*">
        <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
      <?php endif; ?>
     <div style="
  position: relative;
  width: 100%;
">
  <select class="availability" name="availability" required style="
    width: 100%;
    padding: 10px 40px 10px 15px;
    border-radius: 10px;
    border:1px solid #11001a;
    background: #11001a;
    color: #fff;
    font-weight: 500;
    backdrop-filter: blur(10px);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
  ">
    <option value="1" <?= ($editMode && $editData['availability']==1) ? 'selected' : '' ?>>Available</option>
    <option value="0" <?= ($editMode && $editData['availability']==0) ? 'selected' : '' ?>>Unavailable</option>
  </select>

  <!-- ▼ Arrow icon -->
  <i class="fa-solid fa-chevron-down" style="
    position: absolute;
    right: 15px;
    top: 35%;
    transform: translateY(-50%);
    color: #fff;
    pointer-events: none;
  "></i>
</div>


      <button type="submit" class="submit-btn"><?= $editMode ? "Update Flower" : "Add Flower" ?></button>
    </form>
  </div>

  <div class="flowers-section">
    <h2>Your Flower Listings</h2>
    <div class="product-grid">
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $id = $row['id'];
              $flowerName = htmlspecialchars($row['flowerName']);
              $price = number_format($row['price'], 2);
              $img = htmlspecialchars($row['image_path']);
              $availability = $row['availability'] ? "<span style='color:lime;'>Available</span>" : "<span style='color:red;'>Unavailable</span>";

              echo "<div class='product-card'>
                      <img src='$img' alt='$flowerName' />
                      <div class='product-info'>
                        <h3>$flowerName</h3>
                        <p>₱ $price</p>
                        <p style='margin-bottom: 10px;'>$availability</p>
                        <a class='view-button edit-btn' href='?edit=$id'>Edit</a>
                        <a class='view-button delete-btn' href='#' onclick='openModal($id)'>Delete</a>
                      </div>
                    </div>";
          }
      } else {
          echo "<p style='text-align:center; color:#ccc; grid-column:1/-1;'>No flowers found in your shop.</p>";
      }
      ?>
    </div>
  </div>
</div>

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
