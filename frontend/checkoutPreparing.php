<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../backend/loginCustomers.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

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

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch delivered or pickup-related orders with shop details
$sql = "SELECT ch.flowerName, ch.quantity, ch.total, ch.payment_method, ch.status, ch.purchase_date, 
               fo.shop_name, fo.address, fo.owner_id
        FROM checkout_history ch
        JOIN flowershopowners fo ON ch.owner_id = fo.owner_id
        WHERE ch.user_id = ? 
          AND (ch.status = 'Confirmed/Preparing' OR ch.status = 'Confirmed/Preparing')";
if (!empty($search)) {
    $sql .= " AND (
        ch.flowerName LIKE '%$search%' OR
        ch.quantity LIKE '%$search%' OR
        ch.total LIKE '%$search%' OR
        ch.payment_method LIKE '%$search%' OR
        ch.status LIKE '%$search%' OR
        ch.purchase_date LIKE '%$search%' OR
        fo.shop_name LIKE '%$search%' OR
        fo.address LIKE '%$search%'
    )";
}
$sql .= " ORDER BY ch.purchase_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Orders To Receive - Peta Link</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Poppins",sans-serif;}
body{background:#1a0026;color:white;min-height:100vh;}
header{position:fixed;top:0;left:0;width:100%;background:#11001a;padding:12px 30px;display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid #660066;flex-wrap:wrap;z-index:1000;}
.logo{display:flex;align-items:center;gap:12px;}
.logo img{height:55px;border-radius:8px;}
.logo-text{display:flex;flex-direction:column;line-height:1.2;}
.logo-text span:first-child{font-size:1.6rem;font-weight:700;color:#a81ea8ff;letter-spacing:1px;}
.tagline{font-size:0.8rem;font-weight:400;color:#ccc;letter-spacing:0.5px;margin-top:2px;}
.icons{display:flex;align-items:center;gap:15px;}
.icons a{color:white;text-decoration:none;font-size:18px;transition:color 0.3s;}
.icons a:hover{color:#660066;}
.search-bar{display:flex;align-items:center;height:40px;background:#11001a;border-radius:8px;overflow:hidden;}
.search-bar input{flex:1;padding:8px 12px;border:none;outline:none;background:white;color:black;font-size:0.95rem;border-radius:8px 0 0 8px;}
.search-bar button{display:flex;align-items:center;justify-content:center;padding:0 14px;height:100%;border:none;background:#660066;color:white;cursor:pointer;font-size:1rem;transition:background 0.3s;border-radius:0 8px 8px 0;}
.search-bar button:hover{background:#a81ea8ff;}
.menu{display:flex;justify-content:center;flex-wrap:wrap;gap:20px;padding:10px;background:white;margin-top:80px;}
.menu a{color:#5b2c6f;font-weight:bold;font-size:14px;text-decoration:none;position:relative;}
.menu a:hover{color:#a81ea8ff;}
.menu a::after{content:"";position:absolute;left:0;bottom:-3px;width:0;height:2px;background-color:#a81ea8ff;transition:width 0.3s;}
.menu a:hover::after{width:100%;}
.picFlower{background-image:url("./Images/finalLogo_real.png");background-position:center;width:100%;height:310px;position:relative;display:flex;align-items:center;justify-content:center;}
.picFlower::after{content:"";position:absolute;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.4);}
.picOverlay{position:relative;text-align:center;color:white;z-index:1;}
.picOverlay h1{font-size:2.5rem;margin-bottom:10px;font-weight:600;}
.picOverlay p{font-size:1rem;color:#ccc;}
main{max-width:1100px;margin:30px auto;padding:20px;background:rgba(42,0,56,0.8);border-radius:15px;box-shadow:0 8px 24px rgba(0,0,0,0.3);}
h2{text-align:center;margin-bottom:25px;color:white;}
.table-wrapper{max-height:450px;overflow-y:auto;border-radius:12px;}
table{width:100%;border-collapse:collapse;background:rgba(255,255,255,0.05);}
thead{background:#660066;color:white;position:sticky;top:0;}
th,td{padding:12px 15px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.2);}
tbody tr:nth-child(even){background:rgba(255,255,255,0.05);}
.status-delivered{color:#00ff00;font-weight:bold;}
.status-pickup{color:#ffa500;font-weight:bold;}
.status-pick{color:#00aaff;font-weight:bold;}
.no-orders{padding:20px;text-align:center;color:#ccc;font-size:18px;}
.back-btn {
  margin: 20px 30px 10px 30px;
  background: rgba(102, 0, 102, 0.7);
  width: 80px;
  padding: 5px;
  font-weight: bold;
  border-radius: 20px;
  cursor: pointer;
}
.back-btn a {
  text-decoration: none;
  color: white;
  font-size: 16px;
  display: flex;
  align-items: center;
}
.back-btn a i {
  margin-right: 6px;
}
.shop-link{color:#ff66cc;font-weight:600;text-decoration:none;transition:color 0.3s;}
.shop-link:hover{color:#ff33aa;text-decoration:underline;}
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
    <form method="GET" action="checkoutToReceive.php" class="search-bar">
      <input type="text" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <a href="notification.php" id="notifBell" title="Notification" style="position:relative;">
      <i class="fas fa-bell"></i>
      <!-- Always show badge -->
      <span id="notifCount" style="
        position:absolute;
        top:-8px;
        right:-7px;
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

<div class="menu">
   <a href="checkoutPending.php">Pending</a>
  <a href="checkoutPreparing.php" style="border-bottom: 3px solid red; text-decoration: none;">Preparing</a>
  <a href="checkoutToReceive.php">Pick-up/Out-for-Delivery</a>
  <a href="checkoutToCompleted.php">Completed</a>
</div>

<div class="picFlower">
  <div class="picOverlay">
    <h1>Orders To Receive</h1>
    <p>Track your delivered or pickup orders</p>
  </div>
</div>

<div class="back-btn">
  <a href="anniversary.php"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<main>
<h2>Your Orders To Receive</h2>

<?php if ($result->num_rows > 0): ?>
  <div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>Flower</th>
        <th>Quantity</th>
        <th>Total</th>
        <th>Payment Method</th>
        <th>Status</th>
        <th>Date</th>
        <th>Shop Name</th>
        <th>Shop Address</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php 
          $statusClass = "";
$statusText = "";
if ($row['payment_method'] === 'COD') {
    $statusClass = "status-delivered";
    $statusText = "Confirmed/Preparing
";
} elseif ($row['payment_method'] === 'Pick Up') {
    $statusClass = "status-pick";
    $statusText = "Confirmed/Preparing
";
} else {
    $statusClass = "status-pickup";
    $statusText = "Confirmed/Preparing
";
}

        ?>
        <tr>
          <td><?= htmlspecialchars($row['flowerName']) ?></td>
          <td><?= (int)$row['quantity'] ?></td>
          <td>₱<?= number_format($row['total'],2) ?></td>
          <td><?= htmlspecialchars($row['payment_method']) ?></td>
          <td class="<?= $statusClass ?>"><?= $statusText ?></td>
          <td><?= htmlspecialchars($row['purchase_date']) ?></td>
          <td><a class="shop-link" href="shopLocation.php?owner_id=<?= urlencode($row['owner_id']) ?>"><?= htmlspecialchars($row['shop_name']) ?></a></td>
          <td><?= htmlspecialchars($row['address']) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  </div>
<?php else: ?>
  <p class="no-orders">No orders to receive.</p>
<?php endif; ?>

</main>
<script src="JS/notification.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
