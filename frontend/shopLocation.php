<?php
include '../backend/security.php';  

// Connect to DB
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get owner_id from URL
$owner_id = isset($_GET['owner_id']) ? intval($_GET['owner_id']) : 0;

$shop = null;
if ($owner_id > 0) {
    $stmt = $conn->prepare("SELECT shop_name, name, email, contact_number, address, latitude, longitude 
                            FROM flowershopOwners 
                            WHERE owner_id = ?");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $shop = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>PetaLink - Shop Location</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }

/* Header */
header {
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

/* Navigation links */
.navbar {
  position: fixed;
  top: 80px;
  left: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  background: #f3e9ff;
  padding: 10px 0;
  z-index: 999;
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
  height: 280px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 120px;
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

/* Main */
main {
  max-width:1300px;
  margin:30px auto;
  padding:20px;
  display:flex;
  flex-direction:column;
  gap:20px;
  background-color: #fff;
  border-radius: 20px;
}
.container {
  display:flex;
  flex-wrap:wrap;
  gap:20px;
}
.shop-list {
  flex:1;
  min-width:300px;
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  padding:20px;
  word-wrap: break-word;
}
.shop-list strong { font-size:1.2rem; color:white; display:block; margin-bottom:10px; text-align: center; }
.shop-list span { display:block; margin:5px 0; color:#ddd; font-size:0.95rem; }

#map {
  flex:2;
  min-width:300px;
  height:400px;
  border-radius:12px;
  border:1px solid #660066;
}
</style>
</head>
<body>

<header>
  <div class="logo">
    <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
    <div class="logo-text">
      <span>PETA-LINK</span>
      <span class="tagline">Powered by petals, driven by links</span>
    </div>
  </div>
  <div class="icons">
    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
    <a href="purchaseList.php" title="Cart"><i class="fas fa-shopping-bag"></i></a>
  </div>
</header>

<!-- Navigation Links -->
<div class="navbar">
  <a href="anniversary.php">Anniversary</a>
  <a href="birthday.php">Birthday</a>
  <a href="valentines.php">Valentines</a>
  <a href="sympathy.php">Sympathy</a>
  <a href="others.php">Others</a>
</div>

<div class="picFlower">
  <div class="picOverlay">
    <h1>Shop Details</h1>
    <p>Location & Contact Information</p>
  </div>
</div>

<div class="back-btn">
  <a href="anniversary.php"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<main>
  <div class="container">
    <div class="shop-list">
      <?php if ($shop): ?>
        <strong><?= htmlspecialchars($shop['shop_name']) ?></strong>
        <span><b>Owner:</b> <?= htmlspecialchars($shop['name']) ?></span>
        <span><b>Email:</b> <?= htmlspecialchars($shop['email']) ?></span>
        <span><b>Contact:</b> <?= htmlspecialchars($shop['contact_number']) ?></span>
        <span><b>Address:</b> <?= htmlspecialchars($shop['address']) ?></span>
        <br>
        <!-- <span><b>WAAAHHH PANG TEST LANG ITONG COORDINATES</b></span>
        <span><b>Latitude: </b> <?= htmlspecialchars($shop['latitude']) ?></span>
        <span><b>Longitude: </b> <?= htmlspecialchars($shop['longitude']) ?></span> -->
      <?php else: ?>
        <p>No shop details found.</p>
      <?php endif; ?>
    </div>
    <div id="map"></div>
  </div>
</main>

<script>
function initMap() {
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 15,
    center: { lat: 12.8797, lng: 121.7740 },
  });

  const shop = <?php echo json_encode($shop); ?>;

  if (shop && shop.latitude && shop.longitude) {
    const position = { lat: parseFloat(shop.latitude), lng: parseFloat(shop.longitude) };
    const marker = new google.maps.Marker({
      position,
      map,
      title: shop.shop_name,
    });

    const infoWindow = new google.maps.InfoWindow({
      content: `<strong>${shop.shop_name}</strong><br>
                Owner: ${shop.name}<br>
                Contact: ${shop.contact_number}<br>
                Address: ${shop.address}`
    });

    marker.addListener("click", () => { infoWindow.open(map, marker); });

    map.setCenter(position);
  }
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBTq5W8yIVSOkYBsEVE1QBweMx7kDhFOd0&callback=initMap"></script>
</body>
</html>

<?php $conn->close(); ?>
