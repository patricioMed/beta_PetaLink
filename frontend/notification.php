<?php
include '../backend/security.php';

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

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

// Verification notifications (for customer)
$sql_verif = "
    SELECT 'verification' as type, v.id, v.status, f.shop_name, v.submitted_at
    FROM verification v
    JOIN flowershopowners f ON f.owner_id = v.owner_id
    WHERE v.user_id = ? AND v.status IN ('approved','rejected')
";

// Completed/Delivered orders (for customer)
$sql_orders = "
    SELECT 'order' as type, c.id, c.status, f.shop_name, c.purchase_date as submitted_at
    FROM checkout_history c
    JOIN flowershopowners f ON f.owner_id = c.owner_id
    WHERE c.user_id = ? AND c.status IN ('Completed','Out for Delivery','Confirmed/Preparing')
";

$sql = "$sql_verif UNION ALL $sql_orders ORDER BY submitted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Mark verification notifications as viewed
$update_verif_sql = "UPDATE verification SET viewed = 1 WHERE user_id = ?";
$update_verif_stmt = $conn->prepare($update_verif_sql);
$update_verif_stmt->bind_param("i", $user_id);
$update_verif_stmt->execute();
$update_verif_stmt->close();

// Mark orders as viewed for customer
$update_order_sql = "UPDATE checkout_history SET viewed = 1 WHERE user_id = ? AND status IN ('Completed','Out for Delivery', 'Confirmed/Preparing')";
$update_order_stmt = $conn->prepare($update_order_sql);
$update_order_stmt->bind_param("i", $user_id);
$update_order_stmt->execute();
$update_order_stmt->close();
?>


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>PetaLink - Notifications</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }

/* Header */
header {
  position: fixed; top: 0; left: 0;
  width: 100%; background: #11001a;
  padding: 12px 30px; display: flex;
  align-items: center; justify-content: space-between;
  border-bottom: 2px solid #660066; flex-wrap: wrap;
  z-index: 1000;
}
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2; }
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.tagline { font-size:0.8rem; font-weight:400; color:#ccc; letter-spacing:0.5px; margin-top:2px; }

/* Icons */
.icons { display:flex; align-items:center; gap:15px; }
.icons a, .icons i { color:white; text-decoration:none; font-size:18px; transition:color 0.3s; }
.icons a:hover, .icons i:hover { color:white; }

.navbar {
  position: fixed;       /* Make it fixed */
  top: 80px;         /* Below the header (header height ~80px) */
  left: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  background: #f3e9ff;
  padding: 10px 0;
  z-index: 999;          /* Above main content */
}

.navbar a {
  margin:0 15px;
  text-decoration:none;
  color:#5b2c6f;
  font-weight:600;
  font-size:14px;
  position: relative;
}
.navbar a:hover { color:#a81ea8ff; }
.navbar a::after {
  content:"";
  position:absolute;
  left:0; bottom:-3px;
  width:0;
  height:2px;
  background-color:#a81ea8ff;
  transition:width 0.3s;
}
.navbar a:hover::after { width:100%; }

/* Banner */
.picFlower {
  background-image: url("./Images/finalLogo_real.png");
  background-position: center;
  width: 100%;
  height: 310px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 95px;
}
.picFlower::after {
  content: "";
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.4);
}
.picOverlay { position: relative; text-align:center; color:white; z-index:1; }
.picOverlay h1 { font-size: 2.2rem; margin-bottom:10px; font-weight:600; }
.picOverlay p { font-size:1rem; }

/* Back button */
.back-btn { margin:20px 30px 10px 30px;
  background: rgba(102,0,102,0.7);
  width: 75px;
  padding: 5px;
   font-weight:bold;
  border-radius: 20px;
 }
.back-btn a {
  text-decoration:none;
  color:white;
  font-size:16px;
  display:flex;
  align-items:center;
}
.back-btn a i { margin-right:6px; }

/* Main content */
main { max-width:1100px; margin:10px auto 30px auto; padding:20px; display:flex; flex-direction:column; gap:20px; background-color: #fff; border-radius:12px; }

/* Notification cards */
.notification { background:#2a0038; padding:20px; border-radius:12px; border:1px solid #660066; box-shadow:0 4px 12px rgba(0,0,0,0.2); color:white; }
.approved { border-left:5px solid #28a745; }
.rejected { border-left:5px solid #dc3545; }
.completed, .delivered { border-left:5px solid #007bff; }
.notification small { display:block; margin-top:5px; color:#ccc; }

/* Responsive */
@media (max-width:768px) { main { margin:120px 20px 20px 20px; padding:15px; } }
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
        top:-10px;
        right:-8px;
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

<div class="navbar">
  <a href="anniversary.php">Anniversary</a>
  <a href="birthday.php">Birthday</a>
  <a href="valentines.php">Valentines</a>
  <a href="sympathy.php">Sympathy</a>
  <a href="others.php">Others</a>
</div>

<div class="picFlower">
  <div class="picOverlay">
    <h1>Bloom with Every Occasion</h1>
    <p>Elegant flowers, perfect for your moments</p>
  </div>
</div>

<div class="back-btn">
  <a href="anniversary.php"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<main>
<?php if ($result && $result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <?php if($row['type'] == 'verification'): ?>
            <div class="notification <?= $row['status'] ?>">
                Your shop <strong><?= htmlspecialchars($row['shop_name']) ?></strong> has been 
                <strong><?= ucfirst($row['status']) ?></strong> by admin.
                <small><?= $row['submitted_at'] ?></small>
            </div>
        <?php else: ?>
            <div class="notification <?= strtolower($row['status']) ?>">
                Customer order at <strong><?= htmlspecialchars($row['shop_name']) ?></strong> is 
                <strong><?= $row['status'] ?></strong>.
                <small><?= $row['submitted_at'] ?></small>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center; color:#ccc;">No notifications.</p>
<?php endif; ?>
</main>
<script src="JS/notification.js"></script>
</body>
</html>
