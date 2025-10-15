<?php
include '../backend/security.php';  
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

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
$res = $stmt->get_result();
$new_notifs = $res->fetch_assoc()['total_new'] ?? 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>PetaLink - Sympathy Flowers</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
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
    <div class="logo-text"><span>PETALINK</span><span class="tagline">Powered by petals, driven by links</span></div>
  </div>
  <div class="icons">
    <form method="GET" action="sympathy.php" class="search-bar">
      <input type="text" name="search" placeholder="Search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <a href="notification.php" id="notifBell"><i class="fas fa-bell"></i>
      <span id="notifCount" style="position:absolute;top:-5px;right:-8px;background:red;color:white;font-size:0.7rem;padding:2px 6px;border-radius:50%;font-weight:bold;"><?= $new_notifs ?></span>
    </a>
    <a href="profile.php"><i class="fas fa-user"></i></a>
    <a href="purchaseList.php"><i class="fas fa-cart-shopping"></i></a>
    <a href="checkoutPending.php"><i class="fa-solid fa-shopping-bag"></i></a>
  </div>
</header>

<div class="navbar">
  <a href="anniversary.php">Anniversary</a>
  <a href="birthday.php">Birthday</a>
  <a href="valentines.php">Valentines</a>
  <a style="border-bottom:3px solid #5b2c6f;" href="sympathy.php">Sympathy</a>
  <a href="others.php">Others</a>
</div>

<div class="picFlower">
  <div class="picOverlay">
    <h1>Bloom with Every Occasion</h1>
    <p>Elegant flowers, perfect for your moments</p>
  </div>
</div>

<div class="back-btn"><a href="home.php"><i class="fas fa-arrow-left"></i> Back</a></div>

<main>
  <div class="product-grid">
<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT a.id, a.flowerName, a.price, a.image_path, a.availability, f.shop_name, f.owner_id
        FROM Sympathy a
        JOIN flowershopOwners f ON a.owner_id = f.owner_id";
if (!empty($search)) $sql .= " WHERE a.flowerName LIKE '%$search%' OR f.shop_name LIKE '%$search%'";

$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
  while($row = $res->fetch_assoc()) {
    $id = $row['id'];
    $flowerName = htmlspecialchars($row['flowerName']);
    $price = number_format($row['price'],2);
    $img = htmlspecialchars($row['image_path']);
    $shop = htmlspecialchars($row['shop_name']);
    $owner_id = $row['owner_id'];

    // 🌸 Total pieces sold (per flower per shop)
    $sold_sql = "SELECT SUM(quantity) AS total_sold 
                 FROM checkout_history 
                 WHERE flowerName = '".$conn->real_escape_string($row['flowerName'])."' 
                 AND owner_id = ".$owner_id;
    $sold_res = $conn->query($sold_sql);
    $sold_count = 0;
    if ($sold_res && $sold_res->num_rows > 0) {
      $sold_row = $sold_res->fetch_assoc();
      $sold_count = $sold_row['total_sold'] ?? 0;
    }

    $avail = ($row['availability'] == 1) ? "<span style='color:#28a745;'>Available</span>" : "<span style='color:#ff4d4d;'>Unavailable</span>";

    echo '
    <div class="product-card">
      <a href="'.($row['availability'] == 1 ? 'details.php?id='.$id.'&category=Sympathy' : '#').'" '.($row['availability']==0?'style="pointer-events:none;opacity:0.6;cursor:not-allowed;"':'').'>
        <img src="'.$img.'" alt="'.$flowerName.'">
      </a>
      <div class="product-info">
        <h3>'.$flowerName.'</h3>
        <div class="shop-name">by <a href="shopLocation.php?owner_id='.$owner_id.'">'.$shop.'</a></div>
        <div class="price">₱ '.$price.'</div>
        <div class="sold">( Sold '.$sold_count.' )</div>
        <div style="margin-bottom:15px;">'.$avail.'</div>
        <a class="view-button" href="'.($row['availability']==1?'details.php?id='.$id.'&category=Sympathy':'#').'" '.($row['availability']==0?'style="pointer-events:none;opacity:0.5;cursor:not-allowed;"':'').'>View Details</a>
      </div>
    </div>';
  }
} else {
  echo "<p style='padding:20px;text-align:center;color:#ccc;'>No flowers or shop found.</p>";
}
$conn->close();
?>
  </div>
</main>
</body>
</html>
