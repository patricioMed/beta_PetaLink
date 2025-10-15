<?php
session_start();

// Ensure the user is logged in and is an owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../backend/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Connect to the database
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the owner info and shop name
$shopSql = "SELECT owner_id, shop_name FROM flowershopowners WHERE user_id = ?";
$stmtShop = $conn->prepare($shopSql);
$stmtShop->bind_param("i", $user_id);
$stmtShop->execute();
$shopResult = $stmtShop->get_result();

if ($shopResult->num_rows === 0) {
    die("No shop found for this owner.");
}

$shop = $shopResult->fetch_assoc();
$owner_id = $shop['owner_id'];
$shop_name = $shop['shop_name'];

// Get filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'All';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build SQL with optional filters
$sql = "
SELECT 
    ch.id AS order_id,
    u.name AS customer_name,
    u.contact_number AS customer_number,
    -- If delivery_address is NULL, use user's address
    COALESCE(ch.delivery_address, u.address) AS customer_address,
    ch.flowerName,
    ch.price,
    ch.quantity,
    ch.total,
    ch.purchase_date,
    ch.payment_method,
    ch.status
FROM checkout_history ch
JOIN users u ON ch.user_id = u.id
WHERE ch.owner_id = ?
";

$params = [$owner_id];
$types = "i";

// Status filter
if ($statusFilter !== 'All') {
    $sql .= " AND ch.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

// Search filter
if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.address OR ch.delivery_address LIKE ? OR u.email LIKE ? OR u.contact_number LIKE ? OR ch.flowerName LIKE ? OR ch.payment_method LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= "ssssss";
}

$sql .= " ORDER BY ch.purchase_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders for <?= htmlspecialchars($shop_name) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins", sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; padding-top:100px; }

/* Header */
header {
  position: fixed; top:0; left:0; width:100%;
  background: #11001a; padding:12px 30px;
  display:flex; align-items:center; justify-content:space-between;
  border-bottom: 2px solid #660066; z-index:1000;
}
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2; }
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.logo-text .tagline { font-size:0.8rem; font-weight:400; color:#ccc; letter-spacing:0.5px; margin-top:2px; }

/* Search bar */
.search-bar { display:flex; align-items:center; margin-right:20px; }
.search-bar input {
  padding:8px 12px; border:none; border-radius:8px 0 0 8px; outline:none; font-size:15px; width:220px;
}
.search-bar button {
  background:#660066; color:white; font-size:15px; border:0.2px solid white; padding:7px 13px; border-radius:0 8px 8px 0; cursor:pointer;
}
.search-bar button:hover { background:#a81ea8ff; }

/* Orders Container */
.orders-container {
  max-width:1200px; margin:30px auto; padding:20px;
  background:#2a0038; border-radius:12px; border:1px solid #660066;
}
h2 { font-size:2rem; font-weight:600; text-align:center; margin-bottom:20px; color:white; }

/* Filters */
.filter-bar { margin-bottom:15px; text-align:center; }
.filter-bar select, .filter-bar input, .filter-bar button {
  padding:6px 12px; border-radius:20px; border:1px solid #660066;
  font-size:0.9rem; margin:0 5px; background:#1a0026; color:white;
}
.filter-bar button { background:#660066; border-color:#ff66cc; cursor:pointer; }
.filter-bar button:hover { background:#a81ea8ff; }

/* Table */
.table-wrapper { max-height:450px; overflow-y:auto; overflow-x:auto; border-radius:12px; border:1px solid #660066; }
table { width:100%; border-collapse:collapse; }
th, td { padding:12px; text-align:center; border-bottom:1px solid #660066; font-size:0.95rem; }
th { background:#660066; color:#fff; font-weight:600; position:sticky; top:0; }
tr:nth-child(even) { background:#3a003f; }
tr:hover { background:#11001a; transition:0.3s; }

/* Status select */
.status-select { padding:6px 10px; border-radius:20px; border:1px solid #660066; font-size:0.9rem; background:#1a0026; color:white; }

/* Scrollbar */
.table-wrapper::-webkit-scrollbar { width:10px; }
.table-wrapper::-webkit-scrollbar-thumb { background:#660066; border-radius:5px; }
.table-wrapper::-webkit-scrollbar-track { background:#2a0038; border-radius:5px; }

/* Modal */
.modal {
  display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%;
  background:rgba(0,0,0,0.6); justify-content:center; align-items:center;
}
.modal-content {
  background:#2a0038; color:white; padding:20px; border-radius:12px; width:350px; text-align:center;
  border:1px solid #660066;
}
.modal-buttons { margin-top:20px; display:flex; justify-content:center; gap:15px; }
.modal-btn { padding:10px 18px; border:none; border-radius:6px; cursor:pointer; font-size:0.95rem; }
.confirm-btn { background:#660066; color:white; }
.cancel-btn { background:#6c757d; color:white; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let modal = null;
function showModal(message) {
    modal = document.getElementById("statusModal");
    modal.querySelector(".modal-message").textContent = message;
    modal.style.display = "flex";
}
function closeModal() {
    if(modal) modal.style.display = "none";
}

function updateStatus(orderId, selectElem) {
    var newStatus = selectElem.value;
    $.post('updateOrderStatus.php', { order_id: orderId, status: newStatus }, function(response){
        showModal(response);
    });
}
</script>
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
  <form method="GET" action="orders.php" class="search-bar">
    <input type="text" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</header>

<div class="orders-container">
  <h2>Orders for <?= htmlspecialchars($shop_name) ?></h2>

  <!-- Filters -->
  <div class="filter-bar">
    <form method="GET" action="orders.php">
      <label for="statusFilter"><strong>Status:</strong></label>
      <select name="status" id="statusFilter">
        <option value="All" <?= $statusFilter=='All'?'selected':'' ?>>All</option>
        <option value="Pending" <?= $statusFilter=='Pending'?'selected':'' ?>>Pending</option>
        <option value="Confirmed/Preparing" <?= $statusFilter=='Confirmed/Preparing'?'selected':'' ?>>Confirmed/Preparing</option>
        <option value="Completed" <?= $statusFilter=='Completed'?'selected':'' ?>>Completed</option>
        <option value="Pick Up" <?= $statusFilter=='Pick Up'?'selected':'' ?>>Pick Up</option>
        <option value="Out of Delivery" <?= $statusFilter=='Out of Delivery'?'selected':'' ?>>Out of Delivery</option>
      </select>

      <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Search</button>
    </form>
  </div>

  <?php if ($result->num_rows > 0): ?>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Contact</th>
          <th>Address</th>
          <th>Flower</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
          <th>Purchase Date</th>
          <th>Mode of Delivery</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['order_id'] ?></td>
          <td><?= htmlspecialchars($row['customer_name']) ?></td>
          <td><?= htmlspecialchars($row['customer_number']) ?></td>
          <td><?= htmlspecialchars($row['customer_address']) ?></td>
          <td><?= htmlspecialchars($row['flowerName']) ?></td>
          <td>₱<?= number_format($row['price'],2) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td>₱<?= number_format($row['total'],2) ?></td>
          <td><?= $row['purchase_date'] ?></td>
          <td><?= htmlspecialchars($row['payment_method']) ?></td>
          <td>
            <select class="status-select" onchange="updateStatus(<?= $row['order_id'] ?>, this)">
              <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
              <option value="Confirmed/Preparing" <?= $row['status']=='Confirmed/Preparing'?'selected':'' ?>>Confirmed/Preparing</option>
              <option value="Out for delivery" <?= $row['status']=='Out for delivery'?'selected':'' ?>>Out for delivery</option>
              <option value="Pick Up" <?= $row['status']=='Pick Up'?'selected':'' ?>>Pick Up</option>
              <option value="Completed" <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
            </select>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <p style="text-align:center; color:#ccc; margin-top:20px;">No orders found for this filter.</p>
  <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div class="modal" id="statusModal">
  <div class="modal-content">
    <h3>Status Update</h3>
    <p class="modal-message"></p>
    <div class="modal-buttons">
      <button class="modal-btn confirm-btn" onclick="closeModal()">OK</button>
    </div>
  </div>
</div>

</body>
</html>
<?php
$stmt->close();
$stmtShop->close();
$conn->close();
?>
