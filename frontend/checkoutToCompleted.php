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

// Fetch completed orders with shop details + quantity
$sql = "SELECT ch.id, ch.owner_id, ch.flowerName, ch.total, ch.payment_method, ch.status, 
               ch.purchase_date, ch.rating, ch.feedback, ch.quantity,
               fo.shop_name, fo.address
        FROM checkout_history ch
        JOIN flowershopowners fo ON ch.owner_id = fo.owner_id
        WHERE ch.user_id = ? AND ch.status = 'completed'";
if(!empty($search)){
    $sql .= " AND (
        ch.flowerName LIKE '%$search%' OR
        ch.quantity LIKE '%$search%' OR
        ch.total LIKE '%$search%' OR
        ch.payment_method LIKE '%$search%' OR
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
<title>Completed Orders - Peta Link</title>
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
.status-complete{color:#00ff00;font-weight:bold;}
.feedback-box{margin:8px 0;padding:8px;border-radius:8px;background:#330033;color:white;text-align:left;}
.feedback-form{margin-top:10px;text-align:left;}
.feedback-form textarea{width:100%;padding:8px;border-radius:8px;border:none;}
.star-rating{direction:rtl;display:inline-flex;font-size:1.2em;cursor:pointer;}
.star-rating span{color:#ccc;transition:color .2s;}
.star-rating span:hover,
.star-rating span:hover~span,
.star-rating .selected,
.star-rating .selected~span{color:gold;}
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
button.submit-feedback{background:#660066;color:white;padding:6px 10px;border:none;border-radius:8px;margin-top:5px;cursor:pointer;}
button.submit-feedback:hover{background:#a81ea8ff;}
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); align-items:center; justify-content:center; z-index:2000; }
.modal-content { background:white; padding:30px; border-radius:12px; text-align:center; color:black; max-width:400px; width:90%; }
.modal-buttons button { background:#28a745; color:white; border:none; border-radius:8px; padding:8px 16px; cursor:pointer; font-weight:bold; margin-top: 15px; }

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
    <form method="GET" action="checkoutToCompleted.php" class="search-bar">
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
  <a href="checkoutPreparing.php">Preparing</a>
  <a href="checkoutToReceive.php">Pick-up/Out-for-Delivery</a>
  <a href="checkoutToCompleted.php" style="border-bottom: 3px solid red; text-decoration: none;">Completed</a>
</div>

<div class="picFlower">
  <div class="picOverlay">
    <h1>Completed Orders</h1>
    <p>All your successfully completed purchases</p>
  </div>
</div>

<div class="back-btn">
  <a href="anniversary.php"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<main>
<h2>Your Completed Orders</h2>

<?php if ($result->num_rows > 0): ?>
  <div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>Flower</th>
        <th>Quantity</th>
        <th>Total</th>
        <th>Shop Name</th>
        <th>Shop Address</th>
        <th>Payment</th>
        <th>Status</th>
        <th>Date</th>
        <th>Feedback</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['flowerName']) ?></td>
          <td><?= (int)$row['quantity'] ?></td>
          <td>₱<?= number_format($row['total'],2) ?></td>
          <td><a class="shop-link" href="shopLocation.php?owner_id=<?= urlencode($row['owner_id'] ?? '') ?>"><?= htmlspecialchars($row['shop_name']) ?></a></td>
          <td><?= htmlspecialchars($row['address']) ?></td>
          <td><?= htmlspecialchars($row['payment_method']) ?></td>
          <td class="status-complete"><?= htmlspecialchars($row['status']) ?></td>
          <td><?= htmlspecialchars($row['purchase_date']) ?></td>
          <td>
            <?php if (!empty($row['rating']) && !empty($row['feedback'])): ?>
              <div class="feedback-box">
                <strong>Ratings: </strong> ⭐ <?= htmlspecialchars($row['rating']) ?>/5
                <br>
                <strong>Feedback: </strong> <?= htmlspecialchars($row['feedback']) ?>
              </div>
            <?php else: ?>
              <form action="submit_rating.php" method="POST" class="feedback-form">
                <input type="hidden" name="flower_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="rating" id="rating-<?= $row['id'] ?>" required>
                <div class="star-rating" data-target="rating-<?= $row['id'] ?>">
                  <span data-value="5">★</span>
                  <span data-value="4">★</span>
                  <span data-value="3">★</span>
                  <span data-value="2">★</span>
                  <span data-value="1">★</span>
                </div>
                <textarea name="feedback" placeholder="Write your feedback..." required></textarea>
                <button type="submit" class="submit-feedback">Submit</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  </div>
<?php else: ?>
  <p class="no-orders">No completed orders.</p>
<?php endif; ?>

<div class="modal" id="feedbackModal">
  <div class="modal-content">
    <img src="Images/finalLogo_real.png" alt="PetaLink Logo" 
         style="max-width:80px; height:auto; display:block; margin:0 auto 15px auto;" />
    <h2 style="color: black">Thank you for your feedback!</h2>
    <p>Your response has been submitted successfully. We appreciate your time and effort in helping us improve.</p>
    <div class="modal-buttons">
      <button id="feedbackOk">OK</button>
    </div>
  </div>
</div>


</main>

<script>
document.querySelectorAll('.star-rating span').forEach(star=>{
  star.addEventListener('click',function(){
    const value=this.getAttribute('data-value');
    const target=this.parentElement.getAttribute('data-target');
    document.getElementById(target).value=value;
    this.parentElement.querySelectorAll('span').forEach(s=>s.classList.remove('selected'));
    this.classList.add('selected');
    let next=this.nextElementSibling;
    while(next){next.classList.add('selected');next=next.nextElementSibling;}
  });
});
</script>
<script>
// Show modal after feedback submission
document.querySelectorAll('.feedback-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default submit to show modal first

    // Submit the form via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', this.action, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = () => {
      if (xhr.status === 200) {
        document.getElementById('feedbackModal').style.display = 'flex';
      }
    };
    xhr.send(new URLSearchParams(new FormData(this)).toString());
  });
});

// Close modal on OK click
document.getElementById('feedbackOk').addEventListener('click', () => {
  document.getElementById('feedbackModal').style.display = 'none';
  // Optionally reload the page to show updated feedback
  location.reload();
});

// Close modal if clicked outside
window.addEventListener('click', e => {
  if (e.target === document.getElementById('feedbackModal')) {
    document.getElementById('feedbackModal').style.display = 'none';
    location.reload();
  }
});
</script>
<script src="JS/notification.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
