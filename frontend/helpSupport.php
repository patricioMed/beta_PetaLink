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
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Help & Support | PETALINK</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

.icons { display:flex; align-items:center; gap:15px; }
.icons a, .icons i { color:white; text-decoration:none; font-size:18px; transition:color 0.3s; }
.icons a:hover, .icons i:hover { color:white; }

/* Content */
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
  padding:40px; 
  border-radius:20px; 
  box-shadow:0 8px 24px rgba(0,0,0,0.3); 
  text-align:justify;
}
h2 { text-align:center; margin-bottom:20px; color:#ff66cc; font-size:1.8rem; }
h3 { margin-top:25px; margin-bottom:10px; color:#ff99ff; font-size:1.3rem; }

p {
  line-height:1.6; 
  margin-bottom:15px;
  color:#f0e6f6;
}
.contact-info {
  margin-top:30px;
  padding-top:15px;
  border-top:1px solid #660066;
  text-align:center;
}
.contact-info p { margin:5px 0; font-size:1rem; }
.contact-info strong { color:#ff66cc; }

@media (max-width:600px){
  main{padding:20px;}
  h2{font-size:1.3rem;}
  h3{font-size:1.1rem;}
  p{font-size:0.9rem;}
}
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
    <h2>Help & Support</h2>
    <h3>People Behind PETALINK</h3>
    <p>
      PETALINK was built through the teamwork and dedication of passionate individuals who shared the same goal of helping local flower shops in Dagupan City embrace digital innovation. Leading the project is <strong>Kathleen Charm Q. Daroy</strong>, who serves as the Project Manager, ensuring that every stage of development runs smoothly and effectively.
    </p>
    <p>
      The platform’s functionality and design were made possible by <strong>Patrick Medrano</strong> and <strong>Mark Ezekiel Zareno</strong>, our skilled Developers who turned ideas into reality through their technical expertise. Supporting the team is <strong>Rahm Soriano</strong>, the Documenter, who carefully organized and documented every detail of the project’s progress.
    </p>
    <p>
      Together, they brought PETALINK to life—a platform built with collaboration, creativity, and purpose.
    </p>

   <div class="faq-section" style="margin-top:40px; background:#2a0038; border:1px solid #660066; border-radius:12px; padding:30px 40px; max-width:900px; margin:auto; color:white; box-shadow:0 8px 24px rgba(0,0,0,0.3);">
  <h2 style="color:#a81ea8ff; text-align:center; margin-bottom:20px;">Frequently Asked Questions</h2>
  <ol style="line-height:1.8;">
    <li>
      <strong>How will I know if my order is being prepared?</strong>
      <p>Once you place your order, the shop owner will give you a call to confirm your purchase and let you know that your order is being prepared.</p>
    </li>
    <li>
      <strong>How will you assure me that the shop is legit?</strong>
      <p>We make sure every shop is verified by requiring sellers to submit valid documents before being accepted on our platform.</p>
    </li>
    <li>
      <strong>How do I place an order?</strong>
      <p>Simply add your chosen item to the cart, then proceed to checkout to complete your order.</p>
    </li>
    <li>
      <strong>Once I place my order, can I make changes?</strong>
      <p>If your order is still pending, the shop owner will contact you first for confirmation. During this call, you can request changes or suggest personalized additions—like a greeting card or special note.</p>
    </li>
  </ol>
  <div class="contact-info" style="margin-top:20px; line-height:1.6;">
    <p><strong>Developer Team:</strong> Kathstones</p>
    <p><strong>Email:</strong> kathstones.dev@gmail.com</p>
    <p><strong>Call us:</strong> 09127658976</p>
    <p><strong>Support Hours:</strong> 8:00 AM - 8:00 PM</p>
  </div>
</div>

  </main>
</div>

</body>
</html>
