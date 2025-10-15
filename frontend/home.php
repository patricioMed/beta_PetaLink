<?php
session_start();
include '../backend/security.php';  
if (!isset($_SESSION['user_id'])) {
    header("Location: ../backend/loginCustomers.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];

/* 🔔 Notification Count */
$stmt = $conn->prepare("
    SELECT SUM(new_notifs) as total_new FROM (
        SELECT COUNT(*) as new_notifs 
        FROM verification v
        JOIN flowershopowners f ON f.owner_id = v.owner_id
        WHERE v.user_id = ? AND v.status IN ('approved','rejected') AND v.viewed = 0
        UNION ALL
        SELECT COUNT(*) as new_orders
        FROM checkout_history c
        JOIN flowershopowners f ON f.owner_id = c.owner_id
        WHERE c.user_id = ? AND c.status IN ('Completed','Out for Delivery','Confirmed/Preparing') AND c.viewed = 0
    ) as notif_count
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$notif_res = $stmt->get_result();
$new_notifs = 0;
if ($notif_res && $notif_res->num_rows > 0) {
    $row = $notif_res->fetch_assoc();
    $new_notifs = $row['total_new'];
}
$stmt->close();

$search = $_GET['search'] ?? "";
$searchParam = "%" . $search . "%";

/* 🌸 All Flower Shops */
$shopSql = "SELECT owner_id, shop_name, name, address 
            FROM flowershopowners 
            WHERE shop_name LIKE ? OR name LIKE ? OR address LIKE ? 
            ORDER BY shop_name ASC";
$stmtShop = $conn->prepare($shopSql);
$stmtShop->bind_param("sss", $searchParam, $searchParam, $searchParam);
$stmtShop->execute();
$shopResult = $stmtShop->get_result();

/* 🛒 Recent Orders (fixed same as oldcode.php) */
$recentSql = "SELECT f.flowerName, f.price, f.image, o.shop_name, r.purchase_date
              FROM checkout_history r
              JOIN flowers f ON r.flower_id = f.flower_id
              JOIN flowershopowners o ON f.owner_id = o.owner_id
              WHERE r.user_id = ? AND (f.flowerName LIKE ? OR o.shop_name LIKE ?)
              ORDER BY r.purchase_date DESC 
              LIMIT 8";
$stmtRecent = $conn->prepare($recentSql);
$stmtRecent->bind_param("iss", $user_id, $searchParam, $searchParam);
$stmtRecent->execute();
$recentResult = $stmtRecent->get_result();

/* 🌼 All Flowers (All Categories) */
$allSql = "
    SELECT 'Anniversary' AS category, a.id AS flower_id, a.flowerName, a.price, a.image_path, o.shop_name, o.owner_id
    FROM Anniversary a
    JOIN flowershopowners o ON a.owner_id = o.owner_id
    WHERE a.flowerName LIKE ? OR o.shop_name LIKE ?

    UNION

    SELECT 'Birthday' AS category, b.id AS flower_id, b.flowerName, b.price, b.image_path, o.shop_name, o.owner_id
    FROM Birthday b
    JOIN flowershopowners o ON b.owner_id = o.owner_id
    WHERE b.flowerName LIKE ? OR o.shop_name LIKE ?

    UNION

    SELECT 'Sympathy' AS category, s.id AS flower_id, s.flowerName, s.price, s.image_path, o.shop_name, o.owner_id
    FROM Sympathy s
    JOIN flowershopowners o ON s.owner_id = o.owner_id
    WHERE s.flowerName LIKE ? OR o.shop_name LIKE ?

    UNION

    SELECT 'Valentines' AS category, v.id AS flower_id, v.flowerName, v.price, v.image_path, o.shop_name, o.owner_id
    FROM Valentines v
    JOIN flowershopowners o ON v.owner_id = o.owner_id
    WHERE v.flowerName LIKE ? OR o.shop_name LIKE ?

    ORDER BY shop_name ASC, flowerName ASC
";
$stmtAll = $conn->prepare($allSql);
$stmtAll->bind_param("ssssssss", $searchParam, $searchParam, $searchParam, $searchParam,
                                     $searchParam, $searchParam, $searchParam, $searchParam);
$stmtAll->execute();
$allResult = $stmtAll->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PetaLink - Shops</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="CSS/home.css">
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}
body {
  background: #1a0026;
  color: white;
  min-height: 100vh;
}

/* Header */
header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: #11001a;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  border-bottom: 2px solid #660066;
  z-index: 1000;
}
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo img {
  height: 50px;
  border-radius: 8px;
}
.logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}
.logo-text span:first-child {
  font-size: 1.5rem;
  font-weight: 700;
  color: #a81ea8ff;
}
.tagline {
  font-size: 0.75rem;
  color: #ccc;
}
/* add */
.icons {
  display: flex;
  align-items: center;
  gap: 15px;
}
.icons a,
.icons i {
  color: white;
  text-decoration: none;
  font-size: 18px;
  transition: color 0.3s;
}
.icons a:hover,
.icons i:hover {
  color: white;
}

/* Search */
.search-bar {
  display: flex;
  align-items: center;
  height: 38px;
  background: #11001a;
  border-radius: 8px;
  overflow: hidden;
  flex: 1;
  max-width: 300px;
  margin-top: 10px;
}
.search-bar input {
  flex: 1;
  padding: 6px 10px;
  border: none;
  outline: none;
  background: white;
  color: black;
  font-size: 0.9rem;
}
.search-bar button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 12px;
  border: none;
  background: #660066;
  color: white;
  cursor: pointer;
  transition: 0.3s;
}
.search-bar button:hover {
  background: #a81ea8ff;
}

/* Banner */
.picFlower {
  background-image: url("./Images/finalLogo_real.png");
  background-position: center;
  background-repeat: no-repeat;
  width: 100%;
  height: 300px;
  margin-top: 90px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.picFlower::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.4);
}
.picOverlay {
  position: relative;
  text-align: center;
  color: white;
  z-index: 1;
}
.picOverlay h1 {
  font-size: 2rem;
  margin-bottom: 10px;
  font-weight: 600;
}
.picOverlay p {
  font-size: 1rem;
}

/* Main */
main {
  max-width: 1100px;
  margin: 30px auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 30px;
}

/* Buttons */
.all-shops-btn {
  display: inline-block;
  padding: 10px 20px;
  background: #1a0026;
  color: white;
  border-radius: 25px;
  border: 1px solid #660066;
  text-decoration: none;
  transition: 0.3s;
}
.all-shops-btn:hover {
  background: #660066;
  border-color: #ff66cc;
}

/* Grid cards */
.shop-list {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  padding-bottom: 10px;
  scrollbar-width: thin;
  scrollbar-color: #660066 #1a0026;
}

.shop-list::-webkit-scrollbar {
  height: 8px;
}

.shop-list::-webkit-scrollbar-track {
  background: #1a0026;
}

.shop-list::-webkit-scrollbar-thumb {
  background: #660066;
  border-radius: 4px;
}

.shop-card {
  flex: 0 0 auto;
  min-width: 200px;
  background: #2a0038;
  border: 1px solid #660066;
  border-radius: 12px;
  padding: 15px;
  text-align: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

/* not scrollable vertical*/
/* .shop-list,
.flower-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}
.shop-card,
.flower-card {
  background: #2a0038;
  border: 1px solid #660066;
  border-radius: 12px;
  padding: 15px;
  text-align: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.shop-card:hover,
.flower-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
.shop-card a {
  text-decoration: none;
  color: white;
  font-weight: 600;
  font-size: 1.1rem;
  display: block;
  margin-bottom: 8px;
}
.shop-card p {
  font-size: 0.9rem;
  color: #ccc;
} */
.flower-card img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 10px;
}
.flower-card h3 {
  font-size: 1rem;
  color: #ff66cc;
  margin-bottom: 5px;
}
.flower-card p {
  font-size: 0.85rem;
  color: #ccc;
}

/* Sections */
.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
  margin-bottom: 15px;
}

/* Recent Orders Scroll */
.recent-scroll {
  display: flex;
  gap: 15px;
  overflow-x: auto;
  padding-bottom: 10px;
  scrollbar-width: thin;
  scrollbar-color: #660066 #1a0026;
}
.recent-scroll::-webkit-scrollbar {
  height: 8px;
}
.recent-scroll::-webkit-scrollbar-track {
  background: #1a0026;
}
.recent-scroll::-webkit-scrollbar-thumb {
  background: #660066;
  border-radius: 4px;
}

/* Responsive */
@media (max-width: 1024px) {
  header {
    flex-direction: column;
    align-items: flex-start;
  }
  .search-bar {
    width: 100%;
    margin-top: 10px;
  }
  .picFlower {
    height: 250px;
    margin-top: 120px;
  }
  main {
    margin: 20px;
    padding: 15px;
  }
  .shop-list,
  .flower-list {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}
@media (max-width: 768px) {
  .picFlower {
    height: 200px;
    margin-top: 140px;
  }
  .picOverlay h1 {
    font-size: 1.5rem;
  }
  .picOverlay p {
    font-size: 0.85rem;
  }
  .shop-card a,
  .flower-card h3 {
    font-size: 0.95rem;
  }
}
@media (max-width: 480px) {
  .picFlower {
    height: 180px;
    margin-top: 130px;
  }
  header {
    padding: 10px 15px;
  }
  .logo img {
    height: 45px;
  }
  .picOverlay h1 {
    font-size: 1.2rem;
  }
  .picOverlay p {
    font-size: 0.75rem;
  }
  .shop-card a,
  .flower-card h3 {
    font-size: 0.85rem;
  }
  main {
    margin: 15px;
    padding: 10px;
  }
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
    <form method="GET" action="home.php" class="search-bar">
      <input type="text" name="search" placeholder="Search shops or flowers" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <a href="notification.php" id="notifBell" title="Notification" style="position:relative;">
      <i class="fas fa-bell"></i>
      <span id="notifCount" style="
        position:absolute;
        top:-5px;
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

<div class="picFlower">
  <div class="picOverlay">
    <h1>Explore Our Partner Shops</h1>
    <p>Find the perfect flowers from your favorite shops</p>
  </div>
</div>

<main>
  <div style="text-align:center;">
    <a href="anniversary.php" class="all-shops-btn">All Flowers</a>
  </div>

  <h2 class="section-title">All Flower Shops</h2>
  <div class="shop-list">
    <?php if ($shopResult->num_rows > 0): ?>
      <?php while ($row = $shopResult->fetch_assoc()): ?>
        <div class="shop-card">
          <a href="anniversarySpecificowner.php?owner_id=<?= $row['owner_id'] ?>">
            <img src="Images/finalLogo_real.png" alt="<?= htmlspecialchars($row['shop_name']) ?> Logo"
                 style="width:120px; height:120px; object-fit:cover; border-radius:50%; margin-bottom:10px; background:#11001a;">
          </a>
          <a href="anniversarySpecificowner.php?owner_id=<?= $row['owner_id'] ?>">
            <?= htmlspecialchars($row['shop_name']) ?>
          </a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center; color:#ccc;">No shops found.</p>
    <?php endif; ?>
  </div>

  <h2 class="section-title">Your Recent Orders</h2>
  <div class="recent-scroll">
    <?php if ($recentResult && $recentResult->num_rows > 0): ?>
      <?php while ($row = $recentResult->fetch_assoc()): ?>
        <div class="flower-card" style="min-width:200px; flex:0 0 auto;">
          <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['flowerName']) ?>">
          <h3><?= htmlspecialchars($row['flowerName']) ?></h3>
          <p>₱<?= number_format($row['price'], 2) ?></p>
          <p><small>From: <?= htmlspecialchars($row['shop_name']) ?></small></p>
          <p><small>Ordered on: <?= htmlspecialchars($row['purchase_date']) ?></small></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="color:#ccc;">You have no recent orders yet.</p>
    <?php endif; ?>
  </div>

  <h2 class="section-title">All Flowers from All Shops</h2>
  <div class="flower-list">
    <?php if ($allResult && $allResult->num_rows > 0): ?>
      <?php while ($row = $allResult->fetch_assoc()): ?>
        <?php
          $flowerId = $row['flower_id'];
          $flowerName = htmlspecialchars($row['flowerName']);
          $price = number_format($row['price'], 2);
          $image_path = htmlspecialchars($row['image_path']);
          $shopName = htmlspecialchars($row['shop_name']);
          $owner_id = $row['owner_id'];
          $category = $row['category'];
        ?>
        <div class="flower-card">
          <a href="details.php?id=<?= $flowerId ?>&category=<?= urlencode($category) ?>">
            <img src="<?= $image_path ?>" alt="<?= $flowerName ?>">
          </a>
          <h3><a href="details.php?id=<?= $flowerId ?>&category=<?= urlencode($category) ?>" style="color:white; text-decoration:none;"><?= $flowerName ?></a></h3>
          <p>₱<?= $price ?></p>
          <p><small>From: <a href="shopLocation.php?owner_id=<?= $owner_id ?>" style="color:white;"><?= $shopName ?></a></small></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="color:#ccc;">No flowers available.</p>
    <?php endif; ?>
  </div>
</main>

<script>
document.getElementById('notifBell').addEventListener('click', function(e){
    e.preventDefault();
    const notifCount = document.getElementById('notifCount');
    if (notifCount) notifCount.textContent = '0';
    fetch('notification.php', { method: 'POST' })
    .then(() => window.location.href = 'notification.php')
    .catch(err => console.error(err));
});

// Auto refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>
</body>
</html>
