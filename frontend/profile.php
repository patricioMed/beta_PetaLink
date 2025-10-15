<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../backend/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile</title>
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
.sidebar p {
  font-size:14px; 
  line-height:1.6; 
  margin-bottom:25px;
  background: #2a0038; 
  padding:12px; 
  border-radius:10px; 
  border: 1px solid #660066;
  color:#eee;
}
.sidebar p strong { color:#a81ea8ff; }
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
.sidebar a:hover { 
  background:#660066; 
  color:white; 
  border-color:#ff66cc;
  transform: translateX(4px);
}
/* .logout { 
  margin-top:auto; 
  background:#e74c3c !important; 
  border:none !important;
}
.logout:hover { background:#c0392b !important; } */

/* Main Content */
.main {
  flex-grow:1; 
  display:flex; 
  flex-direction:column;
}
.navbar {
  height:60px; 
  background:#2a0038; 
  display:flex; 
  align-items:center;
  justify-content:space-between; 
  padding:0 20px; 
  color:white; 
  font-weight:500;
  border-bottom: 2px solid #660066;
}
.navbar .shop-info { font-size:16px; color:#ff66cc; }
.navbar .logout-btn { 
  background:#e74c3c; 
  padding:8px 12px; 
  border-radius:6px; 
  text-decoration:none; 
  color:white; 
  transition:0.3s; 
}
.navbar .logout-btn:hover { background:#c0392b; }
.iframe-container { flex-grow:1; overflow:hidden; }
iframe { width:100%; height:100%; border:none; }
.logo {
  display: flex;
  justify-content: center; /* centers horizontally */
  align-items: center;     /* centers vertically (if needed) */
  margin-bottom: 20px;     /* space below logo */
  border: 2px solid #660066;
  border-radius: 20px;
}
.logo img {
  height: 150px;
  border-radius: 8px;
}

/* Responsive */
@media(max-width:900px){
  body{flex-direction:column;}
  .sidebar{width:100%; flex-direction:row; justify-content:space-around; padding:10px;}
  .sidebar h3,.sidebar p{display:none;}
  .sidebar a{flex:1;margin:5px;font-size:14px;padding:10px;}
  .main{border-radius:0;}
  .navbar{flex-direction:column; height:auto; padding:10px;}
}
</style>
</head>
<body>
  
<!-- Sidebar -->
<!-- Sidebar -->
 
<div class="sidebar">
    <div class="back-btn">
    <!-- <a onclick="window.history.back();"><i class="fas fa-arrow-left"></i> Back</a> -->
    <a href="home.php"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
   <div class="logo">
    <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
  </div>
  <a href="account.php" target="mainFrame"><i class="fas fa-user"></i>Account</a>
  <a href="accountUpgrade.php" target="mainFrame"><i class="fas fa-user"></i>Create Seller Account</a>
  <a href="helpSupport.php" target="mainFrame"><i class="fas fa-star"></i>Help and Support</a>
  <a href="about.php" target="mainFrame"><i class="fas fa-star"></i>About</a>
  <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>


<!-- Main Content -->
<div class="main">
  <div class="iframe-container">
    <iframe name="mainFrame" src="account.php"></iframe>
  </div>
</div>
</body>
</html>
