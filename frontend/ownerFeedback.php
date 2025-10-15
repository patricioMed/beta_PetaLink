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

// Get owner_id and shop name
$stmt = $conn->prepare("SELECT owner_id, shop_name FROM flowershopOwners WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $owner_id = $row['owner_id'];
    $shopName = $row['shop_name'];
} else {
    echo "<p>You are not registered as a flower shop owner.</p>";
    exit();
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%$search%";

// Fetch flowers purchased
$flowers_sql = "
    SELECT DISTINCT ch.flowerName
    FROM checkout_history ch
    JOIN users u ON ch.user_id = u.id
    WHERE ch.owner_id = ?
";
if ($search !== '') {
    $flowers_sql .= " AND (ch.flowerName LIKE ? OR u.name LIKE ? OR u.address LIKE ?)";
}

$stmt2 = $conn->prepare($flowers_sql);
if ($search !== '') {
    $stmt2->bind_param("isss", $owner_id, $search_param, $search_param, $search_param);
} else {
    $stmt2->bind_param("i", $owner_id);
}
$stmt2->execute();
$flowers_result = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ratings & Feedbacks - <?= htmlspecialchars($shopName) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins", sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }

header {
  position: fixed; top:0; left:0; width:100%;
  background: #11001a; padding:12px 30px;
  display:flex; justify-content:space-between; align-items:center;
  border-bottom:2px solid #660066; z-index:1000;
}
h2{
  margin-top:80px; 
  margin-bottom:20px;
  text-align:center; 
  color:white;
}
.logo { display:flex; align-items:center; gap:12px; }
.logo img { height:55px; border-radius:8px; }
.logo-text { display:flex; flex-direction:column; line-height:1.2; }
.logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
.tagline { font-size:0.8rem; font-weight:400; color:#ccc; margin-top:2px; }

.search-bar { display:flex; align-items:center; }
.search-bar input {
  padding:8px 12px; border:none; border-radius:8px 0 0 8px; outline:none; font-size:15px; width:220px;
}
.search-bar button {
  background:#660066; color:white; font-size:15px; border:0.2px solid white; padding:7px 13px; border-radius:0 8px 8px 0; cursor:pointer;
}
.search-bar button:hover { background:#a81ea8ff; }

main {
  margin-top:100px; padding:30px; max-width:1100px; margin:auto;
}

.flower-section {
  margin-bottom:30px;
  padding:20px;
  background:#2a0038;
  border-radius:12px;
  border:1px solid #660066;
}
.flower-section h3 {
  color:#fff; margin-bottom:10px; font-size:1.5rem;
}

.table-container {
  max-height:250px;
  overflow-y:auto;
  border:1px solid #660066;
  border-radius:12px;
  background:#1a0026;
  padding:5px;
}
.table-container::-webkit-scrollbar { width:8px; }
.table-container::-webkit-scrollbar-thumb { background:#660066; border-radius:4px; }
.table-container::-webkit-scrollbar-track { background:#2a0038; }

table { width:100%; border-collapse:collapse; }
thead { background:#660066; color:white; position:sticky; top:0; }
th, td { padding:12px; border-bottom:1px solid #660066; font-size:0.95rem; text-align:left; }
tr:nth-child(even) { background: rgba(255,255,255,0.05); }
tr:hover { background: rgba(255,102,204,0.1); transition:0.3s; }
.rating { color:#ff9800; font-weight:bold; }
.total-sold {
  margin-top:10px;
  font-size:1rem;
  color:#fff;
  font-weight:600;
  margin-bottom: 8px;
}

@media (max-width:768px) {
  .search-bar input { width:150px; }
  th, td { font-size:0.85rem; padding:8px; }
  .flower-section h3 { font-size:1.2rem; }
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
  <form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search flowers, customers or addresses..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</header>

<main>
<h2>Ratings & Feedbacks for <?= htmlspecialchars($shopName) ?></h2>

<?php
if ($flowers_result->num_rows > 0) {
    while ($flower = $flowers_result->fetch_assoc()) {
        $flowerName = $flower['flowerName'];

        // Average rating
        $rating_sql = "
            SELECT COUNT(*) as total_reviews, SUM(rating) as total_points
            FROM checkout_history
            WHERE flowerName = ? AND owner_id = ?
        ";
        $stmt3 = $conn->prepare($rating_sql);
        $stmt3->bind_param("si", $flowerName, $owner_id);
        $stmt3->execute();
        $rating_result = $stmt3->get_result();
        $rating_data = $rating_result->fetch_assoc();
        $total_reviews = $rating_data['total_reviews'];
        $total_points = $rating_data['total_points'] ?? 0;

        // ✅ Get total pieces sold for this flower by this shop
        $sold_sql = "
            SELECT SUM(quantity) AS total_sold
            FROM checkout_history
            WHERE flowerName = ? AND owner_id = ?
        ";
        $stmtSold = $conn->prepare($sold_sql);
        $stmtSold->bind_param("si", $flowerName, $owner_id);
        $stmtSold->execute();
        $sold_result = $stmtSold->get_result();
        $sold_data = $sold_result->fetch_assoc();
        $total_sold = $sold_data['total_sold'] ?: 0;

        // Feedbacks
        $feedback_sql = "
            SELECT ch.rating, ch.feedback, u.name, u.address, ch.purchase_date
            FROM checkout_history ch
            JOIN users u ON ch.user_id = u.id
            WHERE ch.flowerName = ? AND ch.owner_id = ? AND ch.feedback IS NOT NULL
        ";
        if ($search !== '') {
            $feedback_sql .= " AND (u.name LIKE ? OR u.address LIKE ? OR ch.flowerName LIKE ?) ";
        }
        $feedback_sql .= " ORDER BY ch.purchase_date DESC";

        $stmt4 = $conn->prepare($feedback_sql);
        if ($search !== '') {
            $stmt4->bind_param("sisss", $flowerName, $owner_id, $search_param, $search_param, $search_param);
        } else {
            $stmt4->bind_param("si", $flowerName, $owner_id);
        }
        $stmt4->execute();
        $feedback_result = $stmt4->get_result();

        echo "<div class='flower-section'>";
        echo "<h3>$flowerName</h3>";
        if ($total_reviews > 0) {
            // echo "<p>⭐ Average Rating: " . round($total_points / $total_reviews, 1) . "/5 ($total_reviews reviews)</p>";
        } else {
            echo "<p>No ratings yet</p>";
        }

        // ✅ Display total pieces sold
        echo "<p class='total-sold'> Total Pieces Sold: $total_sold</p>";

        echo "<div class='table-container'>";
        echo "<table>";
        echo "<thead><tr><th>Customer</th><th>Address</th><th>Rating</th><th>Feedback</th><th>Date</th></tr></thead><tbody>";

        if ($feedback_result->num_rows > 0) {
            while ($fb = $feedback_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($fb['name']) . "</td>
                        <td>" . htmlspecialchars($fb['address']) . "</td>
                        <td class='rating'>⭐ " . $fb['rating'] . "/5</td>
                        <td>" . htmlspecialchars($fb['feedback']) . "</td>
                        <td>" . $fb['purchase_date'] . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No feedback available</td></tr>";
        }

        echo "</tbody></table>";
        echo "</div>"; // table-container
        echo "</div>"; // flower-section
    }
} else {
    echo "<p style='text-align:center; color:#ccc;'>No feedbacks found for your shop yet.</p>";
}
$conn->close();
?>
</main>
</body>
</html>
