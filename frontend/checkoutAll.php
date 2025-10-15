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

$selected_ids = $_POST['selected_purchases'] ?? [];

if (empty($selected_ids)) {
    echo "<p>No items selected for checkout.</p>";
    exit();
}

$in  = str_repeat('?,', count($selected_ids) - 1) . '?';
$types = str_repeat('i', count($selected_ids));
$sql = "
SELECT p.id, p.flowerName, p.price, p.quantity, p.total, p.purchase_date,
       p.flower_id, p.owner_id, fso.shop_name
FROM purchases p
LEFT JOIN flowershopowners fso ON p.owner_id = fso.owner_id
WHERE p.user_id = ? AND p.id IN ($in)
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i" . $types, $user_id, ...$selected_ids);
$stmt->execute();
$result = $stmt->get_result();

$purchases = [];
while ($row = $result->fetch_assoc()) {
    $purchases[$row['id']] = $row;
}
$purchases = array_values($purchases);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout_modal'])) {
    $payment_method = $_POST['payment_method'] ?? 'COD';
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    if (empty($delivery_address)) {
        $getAddr = $conn->prepare("SELECT address FROM users WHERE id = ?");
        $getAddr->bind_param("i", $user_id);
        $getAddr->execute();
        $getAddr->bind_result($user_address);
        $getAddr->fetch();
        $getAddr->close();
        $delivery_address = !empty($user_address) ? $user_address : 'No address provided';
    }

    $insert = $conn->prepare("
        INSERT INTO checkout_history
        (user_id, owner_id, flower_id, flowerName, price, quantity, total, purchase_date, payment_method, status, delivery_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'Pending', ?)
    ");

    $updatePurchase = $conn->prepare("
        UPDATE purchases SET flower_id = ?, owner_id = ? WHERE id = ? AND user_id = ?
    ");

    foreach ($purchases as $row) {
        $insert->bind_param(
            "iiisdisss",
            $user_id,
            $row['owner_id'],
            $row['flower_id'],
            $row['flowerName'],
            $row['price'],
            $row['quantity'],
            $row['total'],
            $payment_method,
            $delivery_address
        );
        $insert->execute();

        $updatePurchase->bind_param("iiii", $row['flower_id'], $row['owner_id'], $row['id'], $user_id);
        $updatePurchase->execute();
    }

    $delete = $conn->prepare("DELETE FROM purchases WHERE user_id = ? AND id IN ($in)");
    $delete->bind_param("i" . $types, $user_id, ...$selected_ids);
    $delete->execute();

    $insert->close();
    $updatePurchase->close();
    $delete->close();
    $stmt->close();
    $conn->close();

    header("Location: checkoutPending.php?checkout=success");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>PetaLink - Checkout</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }
header { position: fixed; top: 0; left: 0; width: 100%; background: #11001a; padding: 12px 30px; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #660066; flex-wrap: wrap; z-index: 1000; }
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.brand-text { font-family:"Great Vibes", cursive; font-size:1.8rem; color:#a81ea8ff; font-weight:400; }
.logo-text { display:flex; flex-direction:column; line-height:1.2;}
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.tagline { font-size:0.8rem; font-weight:400; color:#ccc; letter-spacing:0.5px; margin-top:2px; }
.icons { display:flex; align-items:center; gap:15px; }
.icons a, .icons i { color:white; text-decoration:none; font-size:18px; transition:color 0.3s; }
.icons a:hover, .icons i:hover { color:white; }
.navbar { position: fixed; top:80px; left:0; width:100%; display: flex; justify-content: center; background: #f3e9ff; padding: 10px 0; z-index: 999; }
.navbar a { margin:0 15px; text-decoration:none; color:#5b2c6f; font-weight:600; font-size:14px; position: relative; }
.navbar a:hover { color:#a81ea8ff; }
.navbar a::after { content:""; position:absolute; left:0; bottom:-3px; width:0; height:2px; background-color:#a81ea8ff; transition:width 0.3s; }
.navbar a:hover::after { width:100%; }
.picFlower { background-image: url("Images/finalLogo_real.png"); background-position: center; width: 100%; height: 280px; position: relative; display: flex; align-items: center; justify-content: center; margin-top: 120px; }
.picFlower::after { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.picOverlay { position: relative; text-align:center; color:white; z-index:1; }
.picOverlay h1 { font-size: 2rem; margin-bottom:10px; font-weight:600; }
.picOverlay p { font-size:1rem; }
.back-btn { margin:20px 30px 10px 30px; background: rgba(102,0,102,0.7); width: 75px; padding: 5px; font-weight:bold; border-radius: 20px; cursor: pointer; }
.back-btn a { text-decoration:none; color:white; font-size:16px; display:flex; align-items:center; }
.back-btn a i { margin-right:6px; }
.container { max-width:1000px; margin:30px auto; background:#2a0038; padding:30px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.3); }
table { width:100%; border-collapse: collapse; margin-bottom:20px; color:white; }
th, td { padding:12px; border:1px solid #660066; text-align:center; }
th { background:#660066; color:white; }
tr:nth-child(even) { background: rgba(255,255,255,0.05); }
select, button, input[type="text"] { padding:10px; border-radius:20px; border:none; font-weight:bold; }
button { background:#28a745; color:white; cursor:pointer; transition:transform 0.2s; text-align:right; }
button:hover { transform:scale(1.05); }
input[type="text"] { width: 100%; margin-top: 10px; background: rgba(255,255,255,0.1); color: white; padding-left: 15px; }
@media (max-width:768px) { .picFlower { height:180px; } .container { margin:20px; padding:20px; } }
/* Modal styles */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); 
  align-items:center; justify-content:center; z-index:2000; }
/* Updated modal styles */
.modal-content {
  background: white; /* White background */
  padding: 30px;
  border-radius: 12px;
  text-align: center;
  color: black; /* Text color dark for white background */
  max-width: 400px;
  width: 90%;
}

.modal-buttons button {
  width: 90px;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  padding: 8px;
  cursor: pointer;
  text-align: center;
  color: white;
  margin-top: 15px;
}
#modalConfirm { background-color: #28a745; } /* Confirm button green */
#modalCancel { background-color: #d43a3a; }    /* Cancel button red */
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
    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
    <a href="purchaseList.php" title="Cart"><i class="fas fa-shopping-bag"></i></a>
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
    <h1>Confirm Your Checkout</h1>
    <p>Review your selected items before proceeding</p>
  </div>
</div>

<div class="back-btn">
  <a href="purchaseList.php"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="container">
<form id="checkoutForm" method="POST">
<table>
<tr>
  <th>Flower</th>
  <th>Price</th>
  <th>Quantity</th>
  <th>Total</th>
  <th>Date</th>
</tr>
<?php foreach ($purchases as $row): ?>
<tr>
  <td><?= htmlspecialchars($row['flowerName']) ?></td>
  <td>₱<?= number_format($row['price'], 2) ?></td>
  <td><?= $row['quantity'] ?></td>
  <td>₱<?= number_format($row['total'], 2) ?></td>
  <td><?= $row['purchase_date'] ?></td>
</tr>
<input type="hidden" name="selected_purchases[]" value="<?= $row['id'] ?>">
<?php endforeach; ?>
</table>

<label for="payment_method"><strong>Mode of Delivery :</strong></label>
<select name="payment_method" id="payment_method" required>
  <option value="COD">Cash on Delivery</option>
  <option value="Pick Up">Pick Up</option>
</select>

<label for="delivery_address" style="display:block; margin-top:15px;"><strong>Delivery Address :</strong></label>
<input type="text" id="delivery_address" name="delivery_address" placeholder="Enter delivery address (leave blank to keep current)" />

<br><br>
<div style="text-align: right;">
    <button type="button" id="openModalBtn">Confirm Checkout</button>
</div>
</form>
</div>

<!-- Modal -->
<div class="modal" id="checkoutModal">
  <div class="modal-content">
    <h2>Confirm Checkout?</h2>
    <p>Are you sure you want to proceed with this checkout?</p>
    <div class="modal-buttons">
      <button id="modalConfirm">Yes</button>
      <button id="modalCancel">Cancel</button>
    </div>
  </div>
</div>

<div class="modal" id="pendingModal">
  <div class="modal-content">
    <h2>Order Pending</h2>
    <p id="pendingMessage">Your order is pending. Please wait for <span id="shopName"></span> to call to confirm your order. Thank you for understanding.</p>
    <div class="modal-buttons">
      <button id="pendingOk">OK</button>
    </div>
  </div>
</div>

<script>
const modal = document.getElementById('checkoutModal');
const openBtn = document.getElementById('openModalBtn');
const cancelBtn = document.getElementById('modalCancel');
const confirmBtn = document.getElementById('modalConfirm');
const form = document.getElementById('checkoutForm');

const pendingModal = document.getElementById('pendingModal');
const pendingOk = document.getElementById('pendingOk');
const shopNameSpan = document.getElementById('shopName');

// Replace this with dynamic shop name from PHP if needed
const shopName = "<?= htmlspecialchars($row['shop_name'] ?? 'Unknown Shop') ?>"; 
shopNameSpan.textContent = shopName;

// Open checkout confirmation modal
openBtn.addEventListener('click', () => modal.style.display = 'flex');

// Cancel checkout modal
cancelBtn.addEventListener('click', () => modal.style.display = 'none');

// Confirm checkout → show pending modal
confirmBtn.addEventListener('click', (e) => {
    e.preventDefault(); // prevent immediate form submit
    modal.style.display = 'none'; // hide confirmation modal
    pendingModal.style.display = 'flex'; // show pending modal
});

// Submit form only when OK button is clicked
pendingOk.addEventListener('click', () => {
    pendingModal.style.display = 'none';
    
    // Append hidden input to indicate modal confirmation
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'confirm_checkout_modal';
    input.value = '1';
    form.appendChild(input);
    
    form.submit(); // now submit
});

// Close modal if user clicks outside
window.addEventListener('click', e => {
    if (e.target === modal) modal.style.display = 'none';
    if (e.target === pendingModal) pendingModal.style.display = 'none';
});
</script>


</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
