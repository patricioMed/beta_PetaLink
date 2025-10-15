<?php
include '../backend/security.php';  

if (!isset($_GET['owner_id'])) {
    die("No shop selected.");
}

$owner_id = intval($_GET['owner_id']);

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 🔔 Notification count
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
$new_notifs = ($notif_res && $notif_res->num_rows > 0) ? $notif_res->fetch_assoc()['total_new'] : 0;
$stmt->close();

// 🏪 Get shop name
$stmtShop = $conn->prepare("SELECT shop_name FROM flowershopOwners WHERE owner_id = ?");
$stmtShop->bind_param("i", $owner_id);
$stmtShop->execute();
$shopResult = $stmtShop->get_result();
if ($shopResult->num_rows === 0) die("Shop not found.");
$shopName = htmlspecialchars($shopResult->fetch_assoc()['shop_name']);

// 🔍 Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 🌸 Fetch flowers by this owner
$sql = "SELECT a.id, a.flowerName, a.price, a.image_path, a.availability
        FROM Valentines a
        WHERE a.owner_id = ?";
$params = [$owner_id];
$types = "i";
if (!empty($search)) {
    $sql .= " AND a.flowerName LIKE ?";
    $types .= "s";
    $params[] = "%$search%";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= $shopName ?> - Valentines Flowers</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="css/flowerCategory.css">
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<!-- <link rel="stylesheet" href="CSS/flowerCategory.css"> -->
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }
header { position:fixed; top:0; left:0; width:100%; background:#11001a; padding:12px 30px;
display:flex; align-items:center; justify-content:space-between; border-bottom:2px solid #660066; flex-wrap:wrap; z-index:1000; }
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2; }
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.tagline { font-size:0.8rem; color:#ccc; margin-top:2px; }
.icons { display:flex; align-items:center; gap:15px; }
.icons a { color:white; text-decoration:none; font-size:18px; position:relative; }
.search-bar { display:flex; align-items:center; height:40px; background:#11001a; border-radius:8px; overflow:hidden; }
.search-bar input { flex:1; padding:8px 12px; border:none; outline:none; background:white; color:black; font-size:0.95rem; border-radius:8px 0 0 8px; }
.search-bar button { display:flex; align-items:center; justify-content:center; padding:15px 14px; border:none; background:#660066; color:white; cursor:pointer; border-radius:0 8px 8px 0; }
.search-bar button:hover { background:#ff66cc; }
.navbar { position:fixed; top:80px; left:0; width:100%; display:flex; justify-content:center; background:#f3e9ff; padding:10px 0; z-index:999; }
.navbar a { margin:0 15px; text-decoration:none; color:#5b2c6f; font-weight:600; font-size:14px; }
.navbar a:hover { color:#a81ea8ff; }
.picFlower { background-image:url("./Images/finalLogo_real.png"); background-position:center; width:100%; height:310px; position:relative; display:flex; align-items:center; justify-content:center; margin-top:120px; }
.picFlower::after { content:""; position:absolute; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4); }
.picOverlay { position:relative; text-align:center; color:white; z-index:1; }
.back-btn { margin:20px 30px 10px; background:rgba(102,0,102,0.7); width:75px; padding:5px; font-weight:bold; border-radius:20px; }
.back-btn a { text-decoration:none; color:white; font-size:16px; display:flex; align-items:center; }
main { max-width:1100px; margin:30px auto; padding:20px; display:flex; flex-direction:column; gap:30px; }
.product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px; }
.product-card { background:#2a0038; border:1px solid #660066; border-radius:12px; padding:20px; text-align:center; transition:transform 0.2s; }
.product-card:hover { transform:translateY(-5px); box-shadow:0 8px 25px rgba(0,0,0,0.15); }
.product-card img { width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:15px; }
.product-info h3 { font-size:1.2rem; color:#ff66cc; margin-bottom:8px; font-weight:600; }
.product-info .shop-name a { color:#ff66cc; text-decoration:none; font-weight:600; }
.product-info .price { font-size:1rem; font-weight:bold; color:#FFBF00; margin-bottom:5px; }
.product-info .sold {color:#fff; font-weight:500; margin-top:5px; }
.view-button { background:#660066; color:white; padding:8px 20px; border:none; border-radius:25px; text-decoration:none; font-size:0.95rem; }
.view-button:hover { background:#ff66cc; transform:scale(1.05); }
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
    <form method="GET" action="valentinesSpecificowner.php" class="search-bar">
      <input type="hidden" name="owner_id" value="<?= $owner_id ?>">
      <input type="text" name="search" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
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

<!-- Navigation -->
<div class="navbar">
  <a href="anniversarySpecificowner.php?owner_id=<?= $owner_id ?>">Anniversary</a>
  <a href="birthdaySpecificowner.php?owner_id=<?= $owner_id ?>">Birthday</a>
  <a style="border-bottom: 3px solid #5b2c6f; text-decoration: none;"  href="valentinesSpecificowner.php?owner_id=<?= $owner_id ?>">Valentines</a>
  <a href="sympathySpecificowner.php?owner_id=<?= $owner_id ?>">Sympathy</a>
  <a href="othersSpecificowner.php?owner_id=<?= $owner_id ?>">Others</a>
</div>

<div class="picFlower">
  <div class="picOverlay">
    <h1><?= $shopName ?> - Valentines Collection</h1>
    <p>Elegant flowers, perfect for your moments</p>
  </div>
</div>

<div class="back-btn">
  <a href="home.php"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<main>
  <div class="product-grid">
    <?php
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $flowerName = htmlspecialchars($row["flowerName"]);
            $price = number_format($row["price"], 2);
            $imagePath = htmlspecialchars($row["image_path"]);

            // 🌼 Average rating
            $rating_sql = "SELECT COUNT(*) as total_reviews, SUM(rating) as total_points
                           FROM checkout_history
                           WHERE flowerName = ? AND owner_id = ?";
            $rating_stmt = $conn->prepare($rating_sql);
            $rating_stmt->bind_param("si", $row["flowerName"], $owner_id);
            $rating_stmt->execute();
            $rating_res = $rating_stmt->get_result();
            // $avgRating = "No ratings yet";
            if ($rating_res && $rating_res->num_rows > 0) {
                $rating_data = $rating_res->fetch_assoc();
                if ($rating_data['total_reviews'] > 0) {
                    $avg = round($rating_data['total_points'] / $rating_data['total_reviews'], 1);
                    // $avgRating = "⭐ $avg/5 (".$rating_data['total_reviews']." reviews)";
                }
            }
            $rating_stmt->close();

            // 🌸 Total sold pieces per flower by this owner
            $sold_sql = "SELECT SUM(quantity) as total_sold 
                         FROM checkout_history 
                         WHERE flowerName = ? AND owner_id = ?";
            $sold_stmt = $conn->prepare($sold_sql);
            $sold_stmt->bind_param("si", $row["flowerName"], $owner_id);
            $sold_stmt->execute();
            $sold_res = $sold_stmt->get_result();
            $sold_data = $sold_res->fetch_assoc();
            $totalSold = $sold_data['total_sold'] ? intval($sold_data['total_sold']) : 0;
            $sold_stmt->close();

            $availabilityText = ($row['availability'] == 1)
                ? "<span style='color:#28a745;'>Available</span>"
                : "<span style='color:#ff4d4d;'>Unavailable</span>";

echo '
<div class="product-card">
  <a href="'.($row['availability'] == 1 ? 'details.php?id='.$id.'&category=Valentines' : '#').'" 
     '.($row['availability'] == 0 ? 'style="pointer-events:none;cursor:not-allowed;"' : '').'>
    <img src="'.$imagePath.'" alt="'.$flowerName.'" 
         '.($row['availability'] == 0 ? 'style="opacity:0.6;filter:grayscale(60%);"' : '').'>
  </a>
  <div class="product-info">
    <h3>'.$flowerName.'</h3>
    <div class="shop-name">by <a href="shopLocation.php?owner_id='.$owner_id.'">'.$shopName.'</a></div>
    <div class="price">₱ '.$price.'</div>
    <div style="color:#fff; font-weight:500; margin-top:5px;">( Sold '.$totalSold.' )</div>
    <div style="margin-bottom: 15px;" class="availability">'.$availabilityText.'</div>
    <a class="view-button" 
       href="'.($row['availability'] == 1 ? 'details.php?id='.$id.'&category=Valentines' : '#').'" 
       '.($row['availability'] == 0 ? 'style="pointer-events:none;opacity:0.5;cursor:not-allowed;"' : '').'>
       View Details
    </a>
  </div>
</div>';
        }
    } else {
        echo "<p style='padding:20px; text-align:center; color:#ccc;'>No flowers found in this shop.</p>";
    }

    $stmt->close();
    $stmtShop->close();
    $conn->close();
    ?>
  </div>
</main>
<script src="JS/notification.js"></script>
</body>
</html>
