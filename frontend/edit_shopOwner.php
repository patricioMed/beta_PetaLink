<?php
include '../backend/security.php';

// ✅ Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Update record
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $owner_id = intval($_POST['owner_id']);
    $shop_name = $_POST['shop_name'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : NULL;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : NULL;

    $stmt = $conn->prepare("UPDATE flowershopOwners 
        SET shop_name=?, name=?, email=?, contact_number=?, latitude=?, longitude=? 
        WHERE owner_id=?");
    $stmt->bind_param("ssssdsi", $shop_name, $name, $email, $contact_number, $latitude, $longitude, $owner_id);

if ($stmt->execute()) {
    echo '
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h3>✅ Update Successful</h3>
            <p>The shop owner details have been updated successfully.</p>
            <button onclick="window.location.href=\'manage_shopOwners.php\'">OK</button>
        </div>
    </div>
    <style>
      .modal {
        display:flex;
        justify-content:center;
        align-items:center;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.5);
        background: #11001a;
        z-index:9999;
      }
      .modal-content {
        background:#2a0038;
        border:1px solid #a81ea8ff;
        border-radius:12px;
        padding:30px 40px;
        text-align:center;
        color:white;
        box-shadow:0 4px 20px rgba(0,0,0,0.5);
      }
      .modal-content h3 {
        color:#a81ea8ff;
        margin-bottom:10px;
      }
      .modal-content button {
        margin-top:15px;
        background:#25d66f;
        border:none;
        padding:10px 30px;
        border-radius:8px;
        font-weight:600;
        color:white;
        cursor:pointer;
        transition:0.3s;
      }
      .modal-content button:hover {
        background:#2ecc71;
      }
    </style>
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("successModal").style.display = "flex";
      });
    </script>';
} else {
    echo "Error: " . $conn->error;
}

    $stmt->close();
    exit;
}

// ✅ Fetch current data
if (!isset($_GET['id'])) {
    die("No owner ID provided.");
}
$owner_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT shop_name, name, email, contact_number, latitude, longitude FROM flowershopOwners WHERE owner_id=?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$stmt->bind_result($shop_name, $name, $email, $contact_number, $latitude, $longitude);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Shop Owner</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: "Poppins", sans-serif; }
body { display:flex; height:100vh; background:#1a0026; color:white; overflow:hidden; }

/* Sidebar */
.sidebar {
  width: 260px;
  background: #11001a; 
  color: white;
  display: flex;
  flex-direction: column;
  padding: 25px 15px;
  border-right: 2px solid #660066;
}
.sidebar h3 {
  font-size: 20px; 
  text-align:center; 
  color:#a81ea8ff; 
  margin-bottom:20px; 
  font-weight:600;
}
.sidebar a {
  display:flex; 
  align-items:center; 
  gap:12px; 
  margin:6px 0; 
  padding:12px;
  color:white; 
  font-weight:500; 
  text-decoration:none; 
  border-radius:8px;
  background:#1a0026; 
  border: 1px solid #660066;
  transition: all 0.3s ease;
}
.sidebar a:hover, .sidebar a.active { 
  background:#660066; 
  color:white; 
  border-color:#ff66cc;
  transform: translateX(4px);
}
.logo {
  display:flex;
  justify-content:center;
  align-items:center;
  margin-bottom:20px;
  border:2px solid #660066;
  border-radius:20px;
}
.logo img {
  height:120px;
  border-radius:8px;
}
.logout { 
  margin-top:auto; 
  background:#e74c3c !important; 
  border:none !important;
}
.logout:hover { background:#c0392b !important; }

/* Main Content */
.main {
  flex-grow:1; 
  display:flex; 
  flex-direction:column;
  overflow:hidden;
}
.header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  background:#11001a;
  border-bottom:2px solid #660066;
  padding:10px 20px;
}
.header-left {
  display:flex;
  align-items:center;
  gap:12px;
}
.header-left img {
  height:60px;
  border-radius:10px;
}
.logo-text {
  display:flex;
  flex-direction:column;
  line-height:1.2;
}
.logo-text span:first-child {
  font-size:1.4rem;
  font-weight:700;
  color:#a81ea8ff;
  letter-spacing:1px;
}
.tagline {
  font-size:0.8rem;
  font-weight:400;
  color:#ccc;
  letter-spacing:0.5px;
  margin-top:2px;
}

/* Container & Form */
.container {
  padding:30px;
  flex-grow:1;
  overflow-y:auto;
}
.form-container {
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  padding:30px 40px;
  max-width:900px;
  margin:auto;
  box-shadow:0 8px 24px rgba(0,0,0,0.3);
}
.form-container h2 {
  text-align:center;
  color:#a81ea8ff;
  margin-bottom:25px;
  font-size:1.6rem;
}
.form-grid {
  display:grid;
  grid-template-columns: repeat(2, 1fr);
  gap:20px;
}
label {
  font-weight:500;
  margin-bottom:6px;
  display:block;
}
input {
  width:100%;
  padding:10px;
  border:1px solid #660066;
  border-radius:8px;
  background:#1a0026;
  color:white;
  outline:none;
}
input:focus {
  border-color:#a81ea8ff;
}
button {
  margin-top:30px;
  width:100%;
  padding:12px;
  background:#25d66f;
  border:none;
  border-radius:8px;
  color:white;
  font-weight:600;
  font-size:16px;
  transition:0.3s;
}
button:hover {
  background:#2ecc71;
  transform:scale(1.05);
  cursor:pointer;
}

/* Responsive */
@media(max-width:900px){
  body{flex-direction:column;}
  .sidebar{width:100%; flex-direction:row; justify-content:space-around; padding:10px;}
  .sidebar h3{display:none;}
  .sidebar a{flex:1;margin:5px;font-size:14px;padding:10px;}
  .header{flex-direction:column; align-items:flex-start; gap:10px;}
  .form-grid{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="logo">
    <img src="Images/finalLogo_real.png" alt="Admin Logo">
  </div>
  <h3>Admin Panel</h3>
  <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
  <a href="manage_shopOwners.php" class="active"><i class="fas fa-store"></i>Manage Owners</a>
  <a href="manage_customers.php"><i class="fas fa-users"></i>Manage Customers</a>
  <a href="manage_ownerVerification.php"><i class="fas fa-clipboard-check"></i>Verification</a>
  <a href="../backend/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- Main -->
<div class="main">
  <div class="header">
    <div class="header-left">
      <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
      <div class="logo-text">
        <span>PETALINK</span>
        <span class="tagline">Powered by petals, driven by links</span>
      </div>
    </div>
  </div>
<button onclick="window.history.back()" style="
    padding: 8px 16px;
    background:#660066;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:500;
    transition:0.3s;
    width: 100px;
    margin-left: 20px;
  " onmouseover="this.style.background='#a81ea8ff'" onmouseout="this.style.background='#660066'">
    Back
  </button>
  <div class="container">
    <div class="form-container">
      <h2>Edit Shop Owner Details</h2>
      <form method="POST" action="edit_shopOwner.php">
        <input type="hidden" name="owner_id" value="<?= htmlspecialchars($owner_id) ?>">

        <div class="form-grid">
          <div>
            <label>Shop Name</label>
            <input type="text" name="shop_name" value="<?= htmlspecialchars($shop_name) ?>" required>
          </div>
          <div>
            <label>Owner Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
          </div>
          <div>
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
          </div>
          <div>
            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?= htmlspecialchars($contact_number) ?>" required>
          </div>
          <div>
            <label>Latitude</label>
            <input type="text" name="latitude" value="<?= htmlspecialchars($latitude) ?>">
          </div>
          <div>
            <label>Longitude</label>
            <input type="text" name="longitude" value="<?= htmlspecialchars($longitude) ?>">
          </div>
        </div>

        <button type="submit">Update Owner</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
