<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../backend/login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

$sql = "SELECT p.id, p.flowerName, p.price, p.quantity, p.total, p.purchase_date,
               p.flower_id, p.owner_id
        FROM purchases p
        WHERE p.user_id = ?";
if (!empty($search)) {
    $sql .= " AND (p.flowerName LIKE '%$search%' OR p.price LIKE '%$search%' OR p.purchase_date LIKE '%$search%')";
}
$sql .= " ORDER BY p.purchase_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$purchases = [];
while ($row = $result->fetch_assoc()) {
    $purchases[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>PetaLink - Cart History</title>
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
.search-bar button:hover{background:#660066;}
.navbar {
  position: fixed;       /* Make it fixed */
  top: 80px;         /* Below the header (header height ~80px) */
  left: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  background: #f3e9ff;
  padding: 10px 0;
  z-index: 999;          /* Above main content */
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
.picFlower{background-image:url("./Images/finalLogo_real.png");background-position:center;width:100%;height:310px;position:relative;display:flex;align-items:center;justify-content:center;margin-top:120px;}
.picFlower::after{content:"";position:absolute;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.4);}
.picOverlay{position:relative;text-align:center;color:white;z-index:1;}
.picOverlay h1{font-size:2.2rem;margin-bottom:10px;font-weight:600;}
.back-btn{margin:20px 30px 10px 30px;background:rgba(102,0,102,0.7);width:75px;padding:5px;font-weight:bold;border-radius:20px;cursor:pointer;}
.back-btn a{text-decoration:none;color:white;font-size:16px;display:flex;align-items:center;}
.back-btn a i{margin-right:6px;}
.orders-btn{display:inline-block;margin-top:20px;padding:12px 25px;background-color:#28a745;color:white;font-weight:bold;text-decoration:none;border-radius:30px;transition:all 0.3s ease;box-shadow:0 4px 10px rgba(0,0,0,0.3);}
main{max-width:1100px;margin:30px auto;padding:20px;background:rgba(42,0,56,0.8);border-radius:15px;box-shadow:0 8px 24px rgba(0,0,0,0.3);}
h2{text-align:center;margin-bottom:25px;color:white;}
.table-wrapper{max-height:450px;overflow-y:auto;border-radius:12px;}
table{width:100%;border-collapse:collapse;background:rgba(255,255,255,0.05);}
thead{background:#660066;color:white;position:sticky;top:0;}
th,td{padding:12px 15px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.2);}
tbody tr:nth-child(even){background:rgba(255,255,255,0.05);}
.action-btn{background:#d43a3a;border:none;padding:8px 15px;color:#fff;border-radius:8px;cursor:pointer;font-weight:bold;transition:background 0.3s;}
.checkout-all{display:flex;align-items:center;justify-content:space-between;margin-top:20px;padding:15px;background:rgba(255,255,255,0.1);border-radius:12px;}
.total-amount{font-size:18px;font-weight:bold;color:white;}
.checkout-all button{padding:10px 25px;background:#28a745;color:white;border:none;border-radius:10px;font-weight:bold;cursor:pointer;}
.modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;justify-content:center;align-items:center;z-index:9999;}
.modal{background:#f3e9ff;padding:25px 30px;border-radius:12px;text-align:center;box-shadow:0 10px 25px rgba(0,0,0,0.3);max-width:400px;}
.modal h3{margin-bottom:20px;color:#333;}
.modal-buttons{display:flex;justify-content:center;gap:15px;}
.modal-buttons button{padding:8px 20px;border:none;border-radius:8px;font-weight:bold;cursor:pointer;}
.confirm-btn{background:#28a745;color:white;}
.cancel-btn{background:#d43a3a;color:white;}
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
    <form method="GET" action="purchaseList.php" class="search-bar">
      <input type="text" name="search" placeholder="Search history..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <a href="notification.php" id="notifBell" title="Notification" style="position:relative;">
      <i class="fas fa-bell"></i>
      <!-- Always show badge -->
      <span id="notifCount" style="
        position:absolute;
        top:-6px;
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

<div class="picFlower">
  <div class="picOverlay">
    <h1>Cart History</h1>
  </div>
</div>
<!-- Navigation Links -->
<div class="navbar">
  <a href="anniversary.php">Anniversary</a>
  <a href="birthday.php">Birthday</a>
  <a href="valentines.php">Valentines</a>
  <a href="sympathy.php">Sympathy</a>
  <a href="others.php">Others</a>
</div>

<div class="back-btn">
  <!-- <a onclick="window.history.back();"><i class="fas fa-arrow-left"></i> Back</a> -->
  <a href="home.php"><i class="fas fa-arrow-left"></i> Back</a>
</div>  

<main>
<h2>Your Add to Cart History</h2>

<?php if(count($purchases) > 0): ?>
<form action="checkoutAll.php" method="POST" id="checkout-form">
  <div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>Select</th>
        <th>Flower Name</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Total</th>
        <th>Add to Cart Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($purchases as $row): ?>
      <tr>
        <td>
          <input type="checkbox" class="select-item" name="selected_purchases[]" value="<?= $row['id'] ?>" data-total="<?= $row['total'] ?>">
        </td>
        <td><?= htmlspecialchars($row['flowerName']) ?></td>
        <td>₱ <?= number_format($row['price'],2) ?></td>
        <td><?= intval($row['quantity']) ?></td>
        <td>₱ <?= number_format($row['total'],2) ?></td>
        <td><?= $row['purchase_date'] ?></td>
        <td><button type="button" class="action-btn" onclick="confirmDelete(<?= $row['id'] ?>)">Delete</button></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <div class="checkout-all">
    <div class="total-amount">Total: ₱ <span id="totalSelected">0.00</span></div>
    <button type="button" onclick="confirmCheckout()">Checkout</button>
  </div>
</form>
<?php else: ?>
<p style="padding:20px; text-align:center; color:#ccc;">You have not made any Add to Cart yet.</p>
<?php endif; ?>
<div style="text-align: right;">
    <a href="checkoutPending.php" class="orders-btn">Orders</a>
</div>

</main>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <h3>Are you sure you want to delete this item?</h3>
    <div class="modal-buttons">
      <button class="confirm-btn" id="confirmDelete">Yes</button>
      <button class="cancel-btn" onclick="closeModal('deleteModal')">Cancel</button>
    </div>
  </div>
</div>

<!-- Checkout Modal -->
<div class="modal-overlay" id="checkoutModal">
  <div class="modal">
    <h3>Are you sure you want to checkout selected items?</h3>
    <div class="modal-buttons">
      <button class="confirm-btn" id="confirmCheckoutBtn">Yes</button>
      <button class="cancel-btn" onclick="closeModal('checkoutModal')">Cancel</button>
    </div>
  </div>
</div>

<form id="deleteForm" method="POST" action="deletePurchase.php" style="display:none;">
  <input type="hidden" name="id" id="deleteId">
</form>

<script>
let deleteId = null;
function confirmDelete(id){
  deleteId = id;
  document.getElementById('deleteModal').style.display='flex';
}
document.getElementById('confirmDelete').addEventListener('click', function(){
  if(deleteId){
    document.getElementById('deleteId').value = deleteId;
    document.getElementById('deleteForm').submit();
  }
});
function confirmCheckout(){
  const checked = document.querySelectorAll('.select-item:checked');
  if(checked.length === 0){
    alert("Please select at least one item to checkout.");
    return;
  }
  document.getElementById('checkoutModal').style.display='flex';
}
document.getElementById('confirmCheckoutBtn').addEventListener('click', function(){
  document.getElementById('checkout-form').submit();
});
function closeModal(id){document.getElementById(id).style.display='none';}
const checkboxes = document.querySelectorAll('.select-item');
const totalDisplay = document.getElementById('totalSelected');
function updateTotal(){
  let total = 0;
  checkboxes.forEach(cb => { if(cb.checked){ total += parseFloat(cb.dataset.total); } });
  totalDisplay.textContent = total.toFixed(2);
}
checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
updateTotal();
</script>
<script src="JS/notification.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
