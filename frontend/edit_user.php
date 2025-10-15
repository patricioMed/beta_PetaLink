<?php
session_start();

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

// Check if ID is provided
if (!isset($_GET['id'])) {
    die("No user ID provided.");
}

$id = intval($_GET['id']);

// ✅ Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $role  = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $id);

   if ($stmt->execute()) {
    echo '
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h3>✅ Update Successful</h3>
            <p>The customer details have been updated successfully.</p>
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

// ✅ Fetch current user data
$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($name, $email, $role);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
body { display:flex; height:100vh; background:#1a0026; color:white; overflow:hidden; }

/* Sidebar */
.sidebar {
  width:260px;
  background:#11001a;
  color:white;
  display:flex;
  flex-direction:column;
  padding:25px 15px;
  border-right:2px solid #660066;
}
.sidebar h3 {
  font-size:20px;
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
  border:1px solid #660066;
  transition:all 0.3s ease;
}
.sidebar a:hover, .sidebar a.active {
  background:#660066;
  color:white;
  border-color:#ff66cc;
  transform:translateX(4px);
}
.logo {
  display:flex;
  justify-content:center;
  align-items:center;
  margin-bottom:20px;
  border:2px solid #660066;
  border-radius:20px;
}
.logo img { height:120px; border-radius:8px; }
.logout { margin-top:auto; background:#e74c3c !important; border:none !important; }
.logout:hover { background:#c0392b !important; }

/* Main Section */
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
.tagline { font-size:0.8rem; color:#ccc; letter-spacing:0.5px; margin-top:2px; }

/* Container */
.container {
  padding:20px;
  flex-grow:1;
  overflow-y:auto;
  display:flex;
  justify-content:center;
  align-items:center;
}

/* Form Card */
.form-container {
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  padding:30px 40px;
  width:100%;
  max-width:600px;
  box-shadow:0 8px 24px rgba(0,0,0,0.3);
}
.form-container h2 {
  text-align:center;
  margin-bottom:25px;
  color:#a81ea8ff;
}
label {
  display:block;
  margin-bottom:6px;
  font-weight:500;
  color:#fff;
}
input, select {
  width:100%;
  padding:10px 12px;
  border:1px solid #660066;
  border-radius:8px;
  background:#1a0026;
  color:white;
  outline:none;
  margin-bottom:18px;
  font-size:14px;
}
input:focus, select:focus {
  border-color:#ff66cc;
  /* box-shadow:0 0 6px #ff66cc; */
}
button {
  width:100%;
  background:#25d66f;
  color:white;
  border:none;
  padding:12px;
  font-size:16px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
  transition:0.3s;
}
button:hover { background:#2ecc71; transform:scale(1.03); }

/* Responsive */
@media(max-width:900px){
  body{flex-direction:column;}
  .sidebar{width:100%; flex-direction:row; justify-content:space-around; padding:10px;}
  .sidebar h3{display:none;}
  .sidebar a{flex:1; margin:5px; font-size:14px; padding:10px;}
  .header{flex-direction:column; align-items:flex-start; gap:10px;}
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
  <a href="manage_customers.php" class="active"><i class="fas fa-users"></i>Manage Customers</a>
  <a href="manage_ownerVerification.php"><i class="fas fa-clipboard-check"></i>Verification</a>
  <a href="../backend/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- Main Content -->
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
margin-top: 10px;
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
      <h2>Edit User Details</h2>
      <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>Role</label>
        <select name="role" required>
          <option value="customer" <?= $role == "customer" ? "selected" : "" ?>>Customer</option>
          <option value="owner" <?= $role == "owner" ? "selected" : "" ?>>Owner</option>
          <option value="admin" <?= $role == "admin" ? "selected" : "" ?>>Admin</option>
        </select>

        <button type="submit">Update User</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
