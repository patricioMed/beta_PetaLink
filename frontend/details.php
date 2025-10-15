<?php
include '../backend/security.php';

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$user_id = $_SESSION['user_id'];

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
$new_notifs = $notif_res->fetch_assoc()['total_new'] ?? 0;
$stmt->close();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category = isset($_GET['category']) ? $_GET['category'] : '';

$allowedCategories = ['Anniversary', 'Birthday', 'Valentines', 'Sympathy', 'Others'];
if (!in_array($category, $allowedCategories)) { die("Invalid category."); }

// 🌸 Fetch flower details
$sql = "SELECT '$category' AS category, a.flowerName, a.price, a.image_path, f.shop_name, f.owner_id
        FROM $category a
        JOIN flowershopOwners f ON a.owner_id = f.owner_id
        WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  $flowerName = htmlspecialchars($row["flowerName"]);
  $price = number_format($row["price"], 2);
  $rawPrice = $row["price"];
  $imagePath = htmlspecialchars($row["image_path"]);
  $shopName = htmlspecialchars($row["shop_name"]);
  $owner_id = $row["owner_id"];
} else {
  $flowerName = "Not found";
  $price = "N/A";
  $rawPrice = 0;
  $imagePath = "";
  $shopName = "";
  $owner_id = 0;
}

// 🌼 Total sold (instead of average)
$sold_sql = "SELECT SUM(quantity) AS total_sold 
             FROM checkout_history 
             WHERE flowerName = ? AND owner_id = ?";
$stmt2 = $conn->prepare($sold_sql);
$stmt2->bind_param("si", $flowerName, $owner_id);
$stmt2->execute();
$sold_result = $stmt2->get_result();
$sold_row = $sold_result->fetch_assoc();
$total_sold = $sold_row['total_sold'] ?? 0;
$stmt2->close();

// 💬 Feedback
$feedback_sql = "
SELECT ch.rating, ch.feedback, u.name, ch.purchase_date
FROM checkout_history ch
JOIN users u ON ch.user_id = u.id
WHERE ch.flowerName = ? AND ch.owner_id = ?
AND ch.feedback IS NOT NULL
ORDER BY ch.purchase_date DESC
";
$stmt3 = $conn->prepare($feedback_sql);
$stmt3->bind_param("si", $flowerName, $owner_id);
$stmt3->execute();
$feedback_result = $stmt3->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($category) ?> Flower Details</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
.icons a,.icons i{color:white;text-decoration:none;font-size:18px;transition:color .3s;}
.icons a:hover,.icons i:hover{color:#ff66cc;}
.navbar{position:fixed;top:80px;left:0;width:100%;display:flex;justify-content:center;background:#f3e9ff;padding:10px 0;z-index:999;}
.navbar a{margin:0 15px;text-decoration:none;color:#5b2c6f;font-weight:600;font-size:14px;position:relative;}
.navbar a:hover{color:#a81ea8ff;}
.navbar a::after{content:"";position:absolute;left:0;bottom:-3px;width:0;height:2px;background:#a81ea8ff;transition:width .3s;}
.navbar a:hover::after{width:100%;}
.picFlower{background-image:url("./Images/finalLogo_real.png");background-position:center;width:100%;height:310px;position:relative;display:flex;align-items:center;justify-content:center;margin-top:70px;}
.picFlower::after{content:"";position:absolute;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.4);}
.picOverlay{position:relative;text-align:center;color:white;z-index:1;}
.picOverlay h1{font-size:2rem;margin-bottom:10px;font-weight:600;}
.back-btn{margin:20px 30px 10px 30px;background:rgba(102,0,102,0.7);width:75px;padding:5px;font-weight:bold;border-radius:20px;}
.back-btn a{text-decoration:none;color:white;font-size:16px;display:flex;align-items:center;}
.back-btn a i{margin-right:6px;}
main{max-width:1100px;margin:20px auto;padding:20px;display:flex;flex-direction:column;gap:30px;}
.details-wrapper{display:flex;flex-wrap:wrap;gap:30px;justify-content:center;}
.left-panel,.right-panel{background:#2a0038;border:1px solid #660066;border-radius:12px;padding:20px;color:white;}
.left-panel{width:350px;text-align:center;}
.left-panel img{width:100%;height:220px;object-fit:cover;border-radius:12px;margin-bottom:15px;}
.right-panel{flex:1;max-height:500px;overflow-y:auto;}
.feedback-box{margin-bottom:15px;padding:10px;background:rgba(255,255,255,0.1);border-radius:10px;}
.feedback-box strong{color:white;}
.quantity-wrapper{display:flex;justify-content:center;align-items:center;gap:10px;margin:20px 0;}
input[type="number"]{width:60px;padding:8px;border-radius:8px;border:1px solid #660066;background:#fff;color:#000;text-align:center;}
button{padding:10px;width:100%;border:none;border-radius:25px;background:#660066;color:#fff;cursor:pointer;transition:.3s;}
button:hover{background:#ff66cc;transform:scale(1.05);}
</style>
</head>
<body>
<header>
  <div class="logo">
    <img src="Images/finalLogo_real.png" alt="PetaLink Logo">
    <div class="logo-text">
      <span>PETALINK</span>
      <span class="tagline">Powered by petals, driven by links</span>
    </div>
  </div>
  <div class="icons">
    <a href="notification.php" id="notifBell" title="Notification" style="position:relative;">
      <i class="fas fa-bell"></i>
      <span id="notifCount" style="position:absolute;top:-5px;right:-8px;background:red;color:white;font-size:0.7rem;padding:2px 6px;border-radius:50%;font-weight:bold;"><?= $new_notifs ?></span>
    </a>
    <a href="profile.php"><i class="fas fa-user"></i></a>
    <a href="purchaseList.php"><i class="fas fa-shopping-bag"></i></a>
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
  <a href="javascript:history.back()"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<main>
  <div class="details-wrapper">
    <div class="left-panel">
      <?php if ($imagePath): ?>
        <img src="<?= $imagePath ?>" alt="<?= $flowerName ?>">
      <?php endif; ?>
      <h2><?= $flowerName ?></h2>
      <p style="font-size:18px; color:#28a745; font-weight:bold;">₱<?= $price ?></p>
      <p>by <a href="shopLocation.php?owner_id=<?= $owner_id ?>" style="color:#ff66cc;"><?= $shopName ?></a></p>

      <p style="color:#ccc;">( Sold <?= $total_sold ?> )</p>

      <form action="purchase.php" method="POST">
        <input type="hidden" name="flowerName" value="<?= $flowerName ?>">
        <input type="hidden" name="price" value="<?= $rawPrice ?>">
        <input type="hidden" name="owner_id" value="<?= $owner_id ?>">
        <div class="quantity-wrapper">
          <button type="button" onclick="adjustQty(-1)">-</button>
          <input type="number" id="quantity" name="quantity" value="1" min="1">
          <button type="button" onclick="adjustQty(1)">+</button>
        </div>
        <button type="submit">Add to Cart</button>
      </form>
    </div>

    <div class="right-panel">
      <h3>Customer Feedback</h3>
      <?php if ($feedback_result->num_rows > 0): ?>
        <?php while ($fb = $feedback_result->fetch_assoc()): ?>
          <div class="feedback-box">
            <strong><?= htmlspecialchars($fb['name']) ?></strong> ⭐ <?= $fb['rating'] ?>/5
            <p><?= htmlspecialchars($fb['feedback']) ?></p>
            <small><?= $fb['purchase_date'] ?></small>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No feedback available</p>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
function adjustQty(change){
  const qty=document.getElementById('quantity');
  qty.value=Math.max(1,parseInt(qty.value)+change);
}
</script>
<script src="JS/notification.js"></script>
</body>
</html>
