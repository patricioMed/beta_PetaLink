<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Notification count
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
$new_notifs = $res && $res->num_rows > 0 ? $res->fetch_assoc()['total_new'] : 0;
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>About - PETALINK</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Poppins",sans-serif;}
body{
  background:#1a0026;
  color:white;
  min-height:100vh;
  display:flex;
  flex-direction:column;
}
header{
  position:fixed;
  top:0;left:0;
  width:100%;
  background:#11001a;
  padding:12px 30px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  border-bottom:2px solid #660066;
  flex-wrap:wrap;
  z-index:1000;
}
.logo{display:flex;align-items:center;gap:12px;}
.logo img{height:55px;border-radius:8px;}
.logo-text{display:flex;flex-direction:column;line-height:1.2;}
.logo-text span:first-child{font-size:1.6rem;font-weight:700;color:#a81ea8ff;letter-spacing:1px;}
.tagline{font-size:0.8rem;font-weight:400;color:#ccc;letter-spacing:0.5px;margin-top:2px;}
.icons{display:flex;align-items:center;gap:15px;}
.icons a,.icons i{color:white;text-decoration:none;font-size:18px;transition:color 0.3s;}
.icons a:hover,.icons i:hover{color:white;}
.content-wrapper{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:flex-start;
  padding:30px;
  margin-top:100px;
}
main{
  max-width:900px;
  width:100%;
  background:rgba(42,0,56,0.85);
  padding:40px;
  border-radius:20px;
  box-shadow:0 8px 24px rgba(0,0,0,0.3);
}
h1{
  text-align:center;
  color:#ff66cc;
  font-size:2rem;
  margin-bottom:25px;
}
h2{
  color:#ff66cc;
  margin-top:25px;
  margin-bottom:10px;
}
p{
  text-align:justify;
  line-height:1.7;
  color:#f2f2f2;
  margin-bottom:15px;
}
section{
  margin-bottom:25px;
}
@media(max-width:600px){
  main{padding:20px;}
  h1{font-size:1.4rem;}
  h2{font-size:1.1rem;}
}
</style>
</head>
<body>

<header>
  <div class="logo">
    <img src="Images/finalLogo_real.png" alt="PETALINK Logo">
    <div class="logo-text">
      <span>PETALINK</span>
      <span class="tagline">Powered by petals, driven by links</span>
    </div>
  </div>
  <div class="icons">
    <a href="notification.php" id="notifBell" title="Notification" style="position:relative;">
      <i class="fas fa-bell"></i>
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
    <h1>PETALINK: An Online Customer Digital Portal for Flower Shop Owners in Dagupan City</h1>

    <section>
      <p>PETALINK is an innovative online customer digital portal designed for flower shop owners in Dagupan City, created to connect florists and customers in one convenient digital space. It provides a seamless, secure, and user-friendly system where customers can browse flower shops, place orders, and freely compare different shops, prices, and floral designs to find the best option for their needs. It also promotes legitimacy by ensuring that transactions are safe, verified, and transparent, giving customers confidence in every purchase. With its goal to modernize the local floral industry, PETALINK serves as a bridge between tradition and technology—helping Dagupan’s flower shops bloom brighter, thrive digitally, and stay beautifully connected with their customers.</p>
    </section>

    <section>
      <h2>Where Did We Start?</h2>
      <p>PETALINK began as a project developed to fulfill a course requirement, but what started as an academic task soon grew into a meaningful and inspiring idea. During its creation, the developers saw the real potential and importance of helping local flower shop owners in Dagupan City embrace digital transformation. Motivated by the growing need for convenience, accessibility, and innovation in the floral industry, the team became genuinely interested in bringing the concept to life beyond the classroom. From a simple course project, PETALINK evolved into a purposeful platform aimed at supporting local businesses and connecting communities through technology.</p>
    </section>

    <section>
      <h2>Our Mission</h2>
      <p>Our mission is to empower flower shop owners in Dagupan City by providing a reliable and innovative online platform that connects them with customers, enhances business efficiency, and promotes trust in every transaction. We aim to support local entrepreneurs in embracing digital solutions that help their businesses grow and flourish in the modern marketplace.</p>
    </section>

    <section>
      <h2>Our Vision</h2>
      <p>Our vision is to become the leading digital hub for flower shops in Dagupan City and beyond—a platform where technology and creativity bloom together. We envision a connected floral community where every transaction is smooth, every customer is satisfied, and every flower shop thrives in the digital era.</p>
    </section>

    <section>
      <h2>What We Offer</h2>
      <p>PETALINK offers an all-in-one digital platform designed to connect flower shop owners and customers in Dagupan City. Through our online portal, customers can conveniently browse different flower shops, compare prices, explore various floral designs, and place orders anytime, anywhere. Shop owners, on the other hand, can easily register their businesses, manage their flower listings and services, and reach more customers through our system. PETALINK also ensures smooth, safe, and transparent transactions, creating a trusted space where customers can confidently shop and flower businesses can grow and thrive.</p>
    </section>
  </main>
</div>

</body>
</html>
