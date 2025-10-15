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
    die("DB Connection failed: " . $conn->connect_error);
}

// ✅ Handle Delete Request
if (isset($_GET['delete'])) {
    $owner_id = intval($_GET['delete']);

    // Get corresponding user_id before deleting
    $result = $conn->query("SELECT user_id FROM flowershopOwners WHERE owner_id = $owner_id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = intval($row['user_id']);

        // Delete from flowershopOwners first (to avoid foreign key errors)
        $conn->query("DELETE FROM flowershopOwners WHERE owner_id = $owner_id");
        // Then delete from users table
        $conn->query("DELETE FROM users WHERE id = $user_id");
    }

    header("Location: manage_shopOwners.php");
    exit;
}

// ✅ Fetch Shop Owners
$result = $conn->query("SELECT owner_id, name, email, contact_number, shop_name, status, address, latitude, longitude 
                        FROM flowershopOwners ORDER BY owner_id DESC");
$owners = [];
while ($row = $result->fetch_assoc()) {
    $owners[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Shop Owners</title>
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
.header-right input {
  padding:10px 12px;
  background:#1a0026;
  border:1px solid #660066;
  border-radius:8px;
  color:white;
  outline:none;
  width:250px;
}

/* Container */
.container {
  padding:20px;
  flex-grow:1;
  overflow-y:auto;
}

/* Table */
.table-container {
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  overflow:auto;
  box-shadow:0 8px 24px rgba(0,0,0,0.3);
}
table {
  width:100%;
  border-collapse:collapse;
  min-width:900px;
}
th, td {
  padding:12px;
  text-align:center;
  border-bottom:1px solid #660066;
}
th {
  background:#11001a;
  color:#fff;
  position:sticky;
  top:0;
  z-index:1;
}
tr:hover { background:#3a004e; }

/* Buttons */
.actions button, .actions a {
  padding:6px 12px;
  border-radius:6px;
  border:none;
  color:white;
  cursor:pointer;
  font-weight:500;
  transition:0.3s;
  text-decoration:none;
}
.edit { background:#25d66f; }
.delete { background:#e74c3c; }
.edit:hover { background:#2ecc71; transform:scale(1.05); }
.delete:hover { background:#c0392b; transform:scale(1.05); }

/* Modal */
.modal {
  display:none;
  position:fixed;
  z-index:999;
  left:0;
  top:0;
  width:100%;
  height:100%;
  background-color:rgba(0,0,0,0.5);
  justify-content:center;
  align-items:center;
}
.modal-content {
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  padding:25px;
  text-align:center;
  color:white;
  box-shadow:0 8px 24px rgba(0,0,0,0.3);
}
.modal-buttons {
  display:flex;
  justify-content:center;
  gap:15px;
  margin-top:20px;
}
.confirm-btn, .cancel-btn {
  border:none;
  padding:8px 16px;
  border-radius:5px;
  cursor:pointer;
  color:white;
}
.confirm-btn { background:#25d66f; }
.cancel-btn { background:#e74c3c; }

/* Responsive */
@media(max-width:900px){
  body{flex-direction:column;}
  .sidebar{width:100%; flex-direction:row; justify-content:space-around; padding:10px;}
  .sidebar h3{display:none;}
  .sidebar a{flex:1;margin:5px;font-size:14px;padding:10px;}
  .header{flex-direction:column; align-items:flex-start; gap:10px;}
  .header-right input{width:100%;}
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
  <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- Main Section -->
<div class="main">
  <div class="header">
    <div class="header-left">
      <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
      <div class="logo-text">
        <span>PETALINK</span>
        <span class="tagline">Powered by petals, driven by links</span>
      </div>
    </div>
    <div class="header-right">
      <input type="text" id="searchInput" placeholder="Search owners...">
    </div>
  </div>

  <div class="container">
    <h1 style="margin-bottom:15px; color:#a81ea8ff;">Manage Shop Owners</h1>
    <div class="table-container">
      <table id="ownersTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Shop Name</th>
            <th>Status</th>
            <th>Address</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($owners as $row): ?>
          <tr>
            <td><?= $row['owner_id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['contact_number']) ?></td>
            <td><?= htmlspecialchars($row['shop_name']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($row['latitude']) ?></td>
            <td><?= htmlspecialchars($row['longitude']) ?></td>
            <td class="actions">
              <a class="edit" href="edit_shopOwner.php?id=<?= $row['owner_id'] ?>">Edit</a>
              <button class="delete" onclick="openDeleteModal(<?= $row['owner_id'] ?>)">Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="deleteModal" class="modal">
  <div class="modal-content">
    <h3>Are you sure you want to delete this shop owner?</h3>
    <div class="modal-buttons">
      <button class="confirm-btn" id="confirmDelete">Confirm</button>
      <button class="cancel-btn" onclick="closeModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
  const searchInput = document.getElementById("searchInput");
  const rows = document.querySelectorAll("#ownersTable tbody tr");
  searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase();
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? "" : "none";
    });
  });

  let deleteId = null;
  const modal = document.getElementById("deleteModal");
  const confirmBtn = document.getElementById("confirmDelete");

  function openDeleteModal(id) {
    deleteId = id;
    modal.style.display = "flex";
  }

  function closeModal() {
    modal.style.display = "none";
    deleteId = null;
  }

  confirmBtn.addEventListener("click", function () {
    if (deleteId) {
      window.location.href = "manage_shopOwners.php?delete=" + deleteId;
    }
  });

  window.onclick = function(event) {
    if (event.target === modal) {
      closeModal();
    }
  };
</script>
</body>
</html>

<?php $conn->close(); ?>
