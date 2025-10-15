<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$notif_sql = "
    SELECT COUNT(*) as new_notifs 
    FROM verification v
    JOIN flowershopowners f ON f.owner_id = v.owner_id
    WHERE v.user_id = ? AND v.status IN ('approved','rejected') AND v.viewed = 0
    UNION ALL
    SELECT COUNT(*) as new_orders
    FROM checkout_history c
    JOIN flowershopowners f ON f.owner_id = c.owner_id
    WHERE c.user_id = ? AND c.status IN ('Completed','Out for Delivery','Confirmed/Preparing') AND c.viewed = 0

";

$stmt = $conn->prepare("SELECT SUM(new_notifs) as total_new FROM (
    SELECT COUNT(*) as new_notifs 
    FROM verification v
    JOIN flowershopowners f ON f.owner_id = v.owner_id
    WHERE v.user_id = ? AND v.status IN ('approved','rejected') AND v.viewed = 0
    UNION ALL
    SELECT COUNT(*) as new_orders
    FROM checkout_history c
    JOIN flowershopowners f ON f.owner_id = c.owner_id
    WHERE c.user_id = ? AND c.status IN ('Completed','Out for Delivery','Confirmed/Preparing') AND c.viewed = 0

) as notif_count");

$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$notif_res = $stmt->get_result();
$new_notifs = 0;
if ($notif_res && $notif_res->num_rows > 0) {
    $row = $notif_res->fetch_assoc();
    $new_notifs = $row['total_new'];
}
$stmt->close();
$stmt = $conn->prepare("SELECT name, email, contact_number, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
body { 
  background:#1a0026; 
  color:white; 
  min-height:100vh; 
  display:flex; 
  flex-direction:column; 
}

/* Header */
header {
  flex-shrink:0;
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  background: #11001a;
  padding: 12px 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2px solid #660066;
  flex-wrap: wrap;
  z-index: 1000;
}
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2;}
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.tagline { font-size:0.8rem; font-weight:400; color:#ccc; letter-spacing:0.5px; margin-top:2px; }

/* Icons */
.icons { display:flex; align-items:center; gap:15px; }
.icons a, .icons i { color:white; text-decoration:none; font-size:18px; transition:color 0.3s; }
.icons a:hover, .icons i:hover { color:white; }

/* Content wrapper */
.content-wrapper { 
  flex:1;
  display:flex; 
  justify-content:center; 
  align-items:center; 
  padding:20px; 
  margin-top:80px; 
}
main {
  max-width:800px; 
  width:100%; 
  background:rgba(42,0,56,0.85); 
  padding:30px; 
  border-radius:20px; 
  box-shadow:0 8px 24px rgba(0,0,0,0.3); 
}

/* Headings */
h2 { text-align:center; margin-bottom:20px; color:#ff66cc; }

/* Grid Layout for Info */
.grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px 20px;
}
.info label { font-weight:bold; display:block; margin-bottom:5px; color:#ff66cc; }
.info p, 
#edit-form input[type="text"], 
#edit-form input[type="email"], 
#edit-form input[type="password"] {
  width:100%; 
  padding:10px; 
  border-radius:8px; 
  border:1px solid #660066; 
  background:rgba(255,255,255,0.1); 
  color:white; 
}
#edit-form input:focus { border-color:#ff66cc; background:rgba(255,255,255,0.2); outline:none; }

/* Buttons */
.logout, .buttons { text-align:center; margin-top:20px; grid-column: span 2; }
.logout button, .buttons button { 
  background:#660066; 
  color:white; 
  padding:10px 20px; 
  border:none; 
  border-radius:25px; 
  font-weight:bold; 
  cursor:pointer; 
  margin:5px; 
  transition: background 0.3s, transform 0.2s; 
}
.logout button:hover, .buttons button:hover { background:#ff66cc; transform:scale(1.05); }

/* Password Eye */
.password-wrapper { position: relative; }
.toggle-eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); width:22px; height:22px; cursor:pointer; opacity:0.7; }
.toggle-eye:hover { opacity:1; }

/* Responsive */
@media (max-width:600px){
  .grid { grid-template-columns: 1fr; }
  .logo img{height:45px;}
  .logo-text span:first-child{font-size:1.2rem;}
  .tagline{font-size:0.7rem;}
  h2{font-size:1.2rem;}
  main{padding:15px;}
}
</style>
</head>
<body>

<header>
  <div class="logo">
    <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
    <div class="logo-text">
      <span>PETALINK</span>
      <span class="tagline">Powered by petals, driven by links</span>
    </div>
  </div>
   <div class="icons">
   <a href="notification.php" id="notifBell" title="Notification" style="position:relative;">
      <i class="fas fa-bell"></i>
      <!-- Always show badge -->
      <span id="notifCount" style="
        position:absolute;
        top:-5px;
        right:-9px;
        background:red;
        color:white;
        font-size:0.7rem;
        padding:2px 6px;
        border-radius:50%;
        font-weight:bold;
      "><?= $new_notifs ?></span>
    </a>
    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
    <a href="purchaseList.php" title="Cart"><i class="fas fa-cart-shopping"></i></a>
    <a href="checkoutPending.php" title="Orders"><i class="fa-solid fa-shopping-bag"></i></a> 
  </div>
</header>

<div class="content-wrapper">
  <main>
    <h2>Your Account</h2>

    <!-- Display Section -->
    <div id="display-section" class="grid">
      <div class="info"><label>Name:</label><p><?= htmlspecialchars($user['name']) ?></p></div>
      <div class="info"><label>Email:</label><p><?= htmlspecialchars($user['email']) ?></p></div>
      <div class="info"><label>Contact Number:</label><p><?= htmlspecialchars($user['contact_number']) ?></p></div>
      <div class="info"><label>Address:</label><p><?= htmlspecialchars($user['address']) ?></p></div>
      <div class="info"><label>Password:</label><p>********</p></div>
      <div class="logout"><button id="edit-btn">Edit</button></div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="editProfile.php" id="edit-form" class="grid" style="display:none;">
      <div class="info">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required />
      </div>
      <div class="info">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required />
      </div>
      <div class="info">
        <label>Contact Number:</label>
        <input type="text" name="contact_number" value="<?= htmlspecialchars($user['contact_number']) ?>" required />
      </div>
      <div class="info">
        <label>Address:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required />
      </div>
      <div class="info password-wrapper" style="grid-column: span 2;">
        <label>New Password (leave blank to keep current):</label>
        <input type="password" name="password" id="password" placeholder="Enter new password" />
        <img src="Images/hiddenEye.png" id="togglePassword" class="toggle-eye" alt="Toggle Password" />
      </div>
      <div class="buttons">
        <button type="submit">Save</button>
        <button type="button" id="cancel-btn">Cancel</button>
      </div>
    </form>
  </main>
</div>

<script>
document.getElementById("edit-btn").onclick = function () {
  document.getElementById("display-section").style.display = "none";
  document.getElementById("edit-form").style.display = "grid";
};
document.getElementById("cancel-btn").onclick = function () {
  document.getElementById("edit-form").style.display = "none";
  document.getElementById("display-section").style.display = "grid";
};

// Toggle password visibility
const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");
togglePassword.addEventListener("click", () => {
  const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
  passwordInput.setAttribute("type", type);
  togglePassword.src = type === "password" ? "Images/hiddenEye.png" : "Images/showEye.png";
});
</script>
<!-- <script src="JS/notification.js"></script> -->
</body>
</html>
