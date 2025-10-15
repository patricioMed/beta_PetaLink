<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get owner_id
$ownerStmt = $conn->prepare("SELECT owner_id, shop_name FROM flowershopOwners WHERE user_id = ?");
$ownerStmt->bind_param("i", $user_id);
$ownerStmt->execute();
$ownerResult = $ownerStmt->get_result();

if ($ownerRow = $ownerResult->fetch_assoc()) {
    $owner_id = $ownerRow['owner_id'];
    $shop_name = $ownerRow['shop_name'];
} else {
    echo "<p>You are not registered as a flower shop owner.</p>";
    exit();
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Total orders
$orderSql = "SELECT COUNT(*) AS total_orders, SUM(total) AS total_sales 
             FROM checkout_history 
             WHERE owner_id = ?";
$orderParams = [$owner_id];
$orderTypes = "i";

if (!empty($search)) {
    $orderSql .= " AND flowerName LIKE ?";
    $orderTypes .= "s";
    $orderParams[] = "%$search%";
}

$stmtOrders = $conn->prepare($orderSql);
$stmtOrders->bind_param($orderTypes, ...$orderParams);
$stmtOrders->execute();
$orderRes = $stmtOrders->get_result();
$orderData = $orderRes->fetch_assoc();

// Total flowers
$flowerSql = "SELECT COUNT(*) AS total_flowers 
              FROM flowers 
              WHERE owner_id = ?";
$flowerParams = [$owner_id];
$flowerTypes = "i";
if (!empty($search)) {
    $flowerSql .= " AND flowerName LIKE ?";
    $flowerTypes .= "s";
    $flowerParams[] = "%$search%";
}
$stmtFlowers = $conn->prepare($flowerSql);
$stmtFlowers->bind_param($flowerTypes, ...$flowerParams);
$stmtFlowers->execute();
$flowersRes = $stmtFlowers->get_result();
$flowersData = $flowersRes->fetch_assoc();

// Top selling flowers
$topSql = "
    SELECT flowerName, SUM(quantity) AS sold_qty, SUM(total) AS total_earned
    FROM checkout_history 
    WHERE owner_id = ?
";
$topParams = [$owner_id];
$topTypes = "i";
if (!empty($search)) {
    $topSql .= " AND flowerName LIKE ?";
    $topTypes .= "s";
    $topParams[] = "%$search%";
}
$topSql .= " GROUP BY flowerName ORDER BY sold_qty DESC LIMIT 50"; // limit 50 for scrollable table
$stmtTop = $conn->prepare($topSql);
$stmtTop->bind_param($topTypes, ...$topParams);
$stmtTop->execute();
$topRes = $stmtTop->get_result();

// Ratings summary
$ratingSql = "
    SELECT flowerName, AVG(rating) AS avg_rating, COUNT(*) AS reviews
    FROM checkout_history 
    WHERE owner_id = ? AND rating IS NOT NULL
    GROUP BY flowerName
";
$stmtRating = $conn->prepare($ratingSql);
$stmtRating->bind_param("i", $owner_id);
$stmtRating->execute();
$ratingRes = $stmtRating->get_result();
$ratings = [];
while ($row = $ratingRes->fetch_assoc()) {
    $ratings[$row['flowerName']] = ['avg' => round($row['avg_rating'],1), 'reviews' => $row['reviews']];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($shop_name) ?> - Sales Report</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins", sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }

/* Header */
header {
  position: fixed; top:0; left:0; width:100%;
  background:#11001a; padding:12px 30px;
  display:flex; align-items:center; justify-content:space-between;
  border-bottom:2px solid #660066; z-index:1000;
}
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2; }
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.tagline { font-size:0.8rem; font-weight:400; color:#ccc; letter-spacing:0.5px; margin-top:2px; }

/* Search bar */
.search-bar { display:flex; align-items:center; margin-right:20px;}
.search-bar input {
  padding:8px 12px; border:none; border-radius:8px 0 0 8px;
  outline:none; font-size:15px; width:220px;
}
.search-bar button {
  background:#660066; color:white; font-size:15px; border:0.2px solid white;
  padding:7px 13px; border-radius:0 8px 8px 0; cursor:pointer;
}
.search-bar button:hover { background:#a81ea8ff; }

/* Main Content */
.container { max-width:1200px; margin:90px auto 40px; padding:20px; }
.card { background:#2a0038; padding:20px; border-radius:12px; border:1px solid #660066; margin-bottom:20px; }
h2 { color:#ff66cc; margin-bottom:15px; }

/* Table */
.table-container { max-height:400px; overflow-y:auto; border:1px solid #660066; border-radius:12px; }
table { width:100%; border-collapse:collapse; color:white; }
th, td { padding:10px; text-align:center; border-bottom:1px solid #660066; }
th { background:#660066; color:white; position:sticky; top:0; }
tr:hover { background: rgba(255,102,204,0.1); }
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
  <form method="GET" action="shopOwner_reports.php" class="search-bar">
    <input type="text" name="search" placeholder="Search flowers or data..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</header>

<div class="container">
  <div class="card">
    <h2>Summary</h2>
    <p><strong>Total Orders:</strong> <?= $orderData['total_orders'] ?: 0 ?></p>
    <p><strong>Total Sales:</strong> ₱ <?= number_format($orderData['total_sales'] ?: 0, 2) ?></p>
    <p><strong>Total Flower Listings:</strong> <?= $flowersData['total_flowers'] ?: 0 ?></p>
  </div>

  <div class="card">
    <h2>Top Selling Flowers</h2>
    <div class="table-container">
      <table>
        <tr>
          <th>#</th>
          <th>Flower Name</th>
          <th>Total Earned (₱)</th>
          <th>Average Rating</th>
        </tr>
        <?php
        $i = 1;
        while ($row = $topRes->fetch_assoc()) {
            $flowerName = htmlspecialchars($row['flowerName']);
            $avg = isset($ratings[$flowerName]) ? "⭐ ".$ratings[$flowerName]['avg']." ({$ratings[$flowerName]['reviews']} reviews)" : "No ratings";
            echo "<tr>
                    <td>$i</td>
                    <td>$flowerName</td>
                    <td>{$row['sold_qty']}</td>
                    <td>₱ ".number_format($row['total_earned'],2)."</td>
                  </tr>";
            $i++;
        }
        if ($i === 1) echo "<tr><td colspan='5'>No sales yet.</td></tr>";
        ?>
      </table>
    </div>
  </div>
</div>

</body>
</html>
