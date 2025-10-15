<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch shop data if it exists
$sql = "SELECT * FROM flowershopOwners WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$shop = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Shop | PetaLink</title>
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
  justify-content: flex-start;
  border-bottom: 2px solid #660066;
  z-index: 1000;
}
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2; }
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.tagline { font-size:0.8rem; font-weight:400; color:#ccc; letter-spacing:0.5px; margin-top:2px; }

/* Main Container */
.main-container { display:flex; flex-direction:column; align-items:center; padding-top:100px; }

/* Right Container / Form */
.login-container {
  background: #2a0038;
  border: 1px solid #660066;
  border-radius: 12px;
  padding: 30px 25px;
  width: 420px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
.login-container h2 { font-family:"Great Vibes",cursive; font-size:32px; color:#ff66cc; margin-bottom:25px; }

input[type="text"], input[type="url"], input[type="file"], input[type="tel"] {
  width:100%; padding:12px; margin-bottom:12px; border-radius:8px;
  background:#1a0026; border:1px solid #660066; color:white; outline:none; font-size:14px;
}
input::placeholder { color:#ccc; }

button {
  width:100%; padding:12px; border:none; border-radius:30px; background:#660066;
  color:white; font-size:16px; cursor:pointer; font-weight:600; transition:0.3s;
}
button:hover { background:#a81ea8ff; transform:scale(1.05); }

.preview { margin-top:10px; }
.preview img { width:120px; height:120px; border-radius:10px; object-fit:cover; border:2px solid #ff66cc; }

.map-link { display:block; color:#25d66f; text-decoration:underline; margin-top:8px; }

@media screen and (max-width:900px){
  .login-container { width:90%; margin:0 auto; }
}
</style>
</head>
<body>

<header>
  <div class="logo">
    <img src="Images/PetaLink_logo.png" alt="PetaLink Logo" />
    <div class="logo-text">
      <span>PETALINK</span>
      <span class="tagline">Powered by petals, driven by links</span>
    </div>
  </div>
</header>

<div class="main-container">
  <div class="login-container">
    <h2>Manage Shop</h2>
    <form action="updateShop.php" method="POST" enctype="multipart/form-data">
      <!-- <input type="text" name="shop_name" placeholder="Shop Name" required
        value="<?= htmlspecialchars($shop['shop_name'] ?? '') ?>" />
      <input type="text" name="address" placeholder="Shop Address" required
        value="<?= htmlspecialchars($shop['address'] ?? '') ?>" />
      <input type="tel" name="contact_number" placeholder="Contact Number" required
        value="<?= htmlspecialchars($shop['contact_number'] ?? '') ?>" pattern="^(09|\+639)\d{9}$" /> -->
      <h5 style="color:white; text-align:left; margin-bottom:5px;">Google Map Location Link</h5>
        <input type="url" name="map_link" placeholder="Google Map Location Link"
        value="<?= htmlspecialchars($shop['map_link'] ?? '') ?>" required />
        <h5 style="color:white; text-align:left; margin-bottom:5px;">Provide Shop Image</h5>
      <input type="file" name="shop_image" accept="image/*"/>
  
      <?php if (!empty($shop['shop_image'])): ?>
        <div class="preview">
          <img src="<?= htmlspecialchars($shop['shop_image']) ?>" alt="Shop Image"/>
        </div>
      <?php endif; ?>

      <?php if (!empty($shop['map_link'])): ?>
        <a href="<?= htmlspecialchars($shop['map_link']) ?>" target="_blank" class="map-link">
          View on Google Maps <i class="fa-solid fa-location-dot"></i>
        </a>
      <?php endif; ?>

      <button type="submit">Save Changes</button>
    </form>
  </div>
</div>

</body>
</html>
