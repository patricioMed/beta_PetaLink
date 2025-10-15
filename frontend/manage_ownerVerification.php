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

// ✅ Handle Approve/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_id'], $_POST['action'])) {
    $verification_id = intval($_POST['verification_id']);
    $status = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    // Update verification status
    $stmt = $conn->prepare("UPDATE verification SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $verification_id);

    if ($stmt->execute()) {
        // Populate owner_id from flowershopOwners
        $conn->query("
            UPDATE verification v
            JOIN flowershopowners f ON v.user_id = f.user_id
            SET v.owner_id = f.owner_id
            WHERE v.owner_id IS NULL AND v.id = $verification_id
        ");

        // Update flowershopowners status
        $stmt2 = $conn->prepare("
            UPDATE flowershopowners f
            JOIN verification v ON v.owner_id = f.owner_id
            SET f.status = ?
            WHERE v.id = ?
        ");
        $stmt2->bind_param("si", $status, $verification_id);
        $stmt2->execute();
        $stmt2->close();

        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.createElement('div');
                modal.classList.add('status-modal');
                modal.innerHTML = `
                    <div class='modal-content'>
                        <h2>Status Updated</h2>
                        <p>The owner verification has been <strong>$status</strong> successfully.</p>
                        <button onclick='window.location.href=window.location.href'>OK</button>
                    </div>`;
                document.body.appendChild(modal);
            });
        </script>";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

// ✅ Fetch all verifications
$sql = "
    SELECT v.*, u.name, u.email, f.owner_id, f.status AS owner_status, f.shop_name
    FROM verification v
    JOIN users u ON v.user_id = u.id
    LEFT JOIN flowershopowners f 
        ON f.owner_id = v.owner_id OR f.user_id = v.user_id
    ORDER BY v.submitted_at DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Owner Verification</title>
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
.header-left { display:flex; align-items:center; gap:12px; }
.header-left img { height:60px; border-radius:10px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2; }
.logo-text span:first-child {
  font-size:1.4rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px;
}
.tagline { font-size:0.8rem; color:#ccc; margin-top:2px; }
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
table { width:100%; border-collapse:collapse; min-width:1000px; }
th, td {
  padding:12px;
  text-align:center;
  border-bottom:1px solid #660066;
}
th { background:#11001a; color:#fff; position:sticky; top:0; z-index:1; }
tr:hover { background:#3a004e; }

/* Buttons */
.action-btn {
  padding:6px 12px;
  border-radius:6px;
  border:none;
  color:white;
  cursor:pointer;
  font-weight:500;
  transition:0.3s;
}
.approve { background:#25d66f; }
.reject { background:#e74c3c; }
.approve:hover { background:#2ecc71; transform:scale(1.05); }
.reject:hover { background:#c0392b; transform:scale(1.05); }

/* ✅ Confirmation Modal */
.modal {
  display:none;
  position:fixed;
  top:0; left:0;
  width:100%; height:100%;
  background:rgba(0,0,0,0.6);
  justify-content:center;
  align-items:center;
  z-index:1000;
}
.modal-content {
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  padding:30px 40px;
  text-align:center;
  box-shadow:0 0 15px rgba(255,102,204,0.5);
}
.modal-content h2 { color:#a81ea8ff; margin-bottom:10px; }
.modal-content p { margin-bottom:20px; color:#eee; }
.modal-content button {
  margin:5px 8px;
  padding:10px 20px;
  border:none;
  border-radius:8px;
  font-weight:600;
  cursor:pointer;
  transition:0.3s;
}
.btn-yes { background:#25d66f; color:white; }
.btn-no { background:#e74c3c; color:white; }
.btn-yes:hover { background:#2ecc71; }
.btn-no:hover { background:#c0392b; }

/* ✅ Success Modal */
.status-modal {
  position:fixed;
  top:0; left:0;
  width:100%; height:100%;
  background:rgba(0,0,0,0.6);
  display:flex;
  justify-content:center;
  align-items:center;
  z-index:2000;
}
.status-modal .modal-content {
  text-align:center;
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  padding:25px 40px;
}
.status-modal .modal-content h2 { color:#25d66f; margin-bottom:10px; }
.status-modal .modal-content button {
  background:#25d66f;
  color:white;
  border:none;
  padding:10px 20px;
  border-radius:8px;
  font-weight:600;
  margin-top:10px;
  cursor:pointer;
}

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
  <a href="manage_shopOwners.php"><i class="fas fa-store"></i>Manage Owners</a>
  <a href="manage_customers.php"><i class="fas fa-users"></i>Manage Customers</a>
  <a href="manage_ownerVerification.php" class="active"><i class="fas fa-clipboard-check"></i>Verification</a>
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
      <input type="text" id="searchInput" placeholder="Search verification...">
    </div>
  </div>

  <div class="container">
    <h1 style="margin-bottom:15px; color:#a81ea8ff;">Owner Verification</h1>
    <div class="table-container">
      <table id="verificationTable">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Shop Name</th>
            <th>Barangay Clearance</th>
            <th>Business Permit</th>
            <th>SSS/PhilHealth/Pag-IBIG</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['shop_name'] ?? '-') ?></td>
            <td><?php if ($row['barangay_clearance']) echo "<a href='{$row['barangay_clearance']}' target='_blank' style='color:white;'>View</a>"; ?></td>
            <td><?php if ($row['business_permit']) echo "<a href='{$row['business_permit']}' target='_blank' style='color:white;'>View</a>"; ?></td>
            <td><?php if ($row['sss_philhealth_pagibig']) echo "<a href='{$row['sss_philhealth_pagibig']}' target='_blank' style='color:white;'>View</a>"; ?></td>
            <td style="color: <?= $row['status']=='approved'?'#25d66f':($row['status']=='rejected'?'#e74c3c':'#ffcc00') ?>;">
              <?= ucfirst($row['status']) ?>
            </td>
            <td>
              <?php if ($row['status'] == 'pending'): ?>
                <button class="action-btn approve" onclick="showModal(<?= $row['id'] ?>, 'approve')">Approve</button>
                <button class="action-btn reject" onclick="showModal(<?= $row['id'] ?>, 'reject')">Reject</button>
              <?php else: ?> - <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ✅ Confirmation Modal -->
<div id="confirmModal" class="modal">
  <div class="modal-content">
    <h2 id="modalTitle">Confirm Action</h2>
    <p id="modalMessage"></p>
    <form method="POST" id="confirmForm">
      <input type="hidden" name="verification_id" id="verificationId">
      <input type="hidden" name="action" id="actionType">
      <button type="submit" class="btn-yes">Yes</button>
      <button type="button" class="btn-no" onclick="closeModal()">No</button>
    </form>
  </div>
</div>

<script>
function showModal(id, action) {
  document.getElementById('confirmModal').style.display = 'flex';
  document.getElementById('verificationId').value = id;
  document.getElementById('actionType').value = action;
  const title = action === 'approve' ? 'Approve Verification?' : 'Reject Verification?';
  const message = action === 'approve'
    ? 'Are you sure you want to approve this owner verification?'
    : 'Are you sure you want to reject this owner verification?';
  document.getElementById('modalTitle').innerText = title;
  document.getElementById('modalMessage').innerText = message;
}
function closeModal() {
  document.getElementById('confirmModal').style.display = 'none';
}

// ✅ Search filter
const searchInput = document.getElementById("searchInput");
const rows = document.querySelectorAll("#verificationTable tbody tr");
searchInput.addEventListener("input", function () {
  const query = this.value.toLowerCase();
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(query) ? "" : "none";
  });
});
</script>
</body>
</html>

<?php $conn->close(); ?>
