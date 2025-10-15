<?php
// if ($_SERVER["REQUEST_METHOD"] === "POST") {
//     $flower = $_POST['flowerName'];
//     $price = floatval($_POST['price']);
//     $quantity = intval($_POST['quantity']);
//     $total = $price * $quantity;

//     echo "<h2>Thank you for your purchase!</h2>";
//     echo "<p>You ordered <strong>$quantity</strong> of <strong>$flower</strong>.</p>";
//     echo "<p>Total: <strong>$" . number_format($total, 2) . "</strong></p>";
//     echo "<a href='index.php'>← Back to Galleryw</a>";
//     echo "<a href='purchaseList.php'>← Purchases</a>";
// } else {
//     header("Location: index.php");
//     exit();
// }

// session_start();

// if (!isset($_SESSION['user_id'])) {
//     header("Location: loginCustomers.php");
//     exit();
// }

// if ($_SERVER["REQUEST_METHOD"] === "POST") {
//     $user_id = $_SESSION['user_id'];
//     $flower = $_POST['flowerName'];
//     $price = floatval($_POST['price']);
//     $quantity = intval($_POST['quantity']);
//     $total = $price * $quantity;

//     // DB connection
//     $conn = new mysqli("localhost", "root", "patricioMed", "petalinkKathstone");
//     if ($conn->connect_error) {
//         die("Connection failed: " . $conn->connect_error);
//     }

//     // Insert purchase with user_id and flower_id
//     $stmt = $conn->prepare("INSERT INTO purchases (user_id, flowerName, price, quantity, total, purchase_date) VALUES (?, ?, ?, ?, ?, NOW())");
//     $stmt->bind_param("isddi", $user_id, $flower, $price, $quantity, $total);

//     if ($stmt->execute()) {
//         // Redirect to purchase list page (showing all purchases for user)
//         header("Location: purchaseList.php");
//         exit();
//     } else {
//         echo "<h2>Error processing purchase. Please try again.</h2>";
//         echo "<a href='shop.php'>← Back to Gallery</a>";
//     }

//     $stmt->close();
//     $conn->close();
// } else {
//     header("Location: shop.php");
//     exit();
// }
?>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginCustomers.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id  = $_SESSION['user_id'];
    $flower   = $_POST['flowerName'];
    $price    = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $total    = $price * $quantity;

    // DB connection
    $conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ✅ Get flower_id + owner_id from flowers table
  $owner_id = intval($_POST['owner_id']);
$flower_id = null;

// Lookup flower by name + owner_id
$lookup = $conn->prepare("SELECT flower_id FROM flowers WHERE flowerName = ? AND owner_id = ? LIMIT 1");
$lookup->bind_param("si", $flower, $owner_id);
$lookup->execute();
$lookup->bind_result($flower_id);
$lookup->fetch();
$lookup->close();


    if (!$flower_id || !$owner_id) {
        echo "<h2>Error: Could not find flower details.</h2>";
        echo "<a href='shop.php'>← Back to Gallery</a>";
        exit();
    }

    // ✅ Insert purchase with user_id, flower_id, owner_id
    $stmt = $conn->prepare("INSERT INTO purchases 
        (user_id, flowerName, price, quantity, total, purchase_date, flower_id, owner_id) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("isddiis", $user_id, $flower, $price, $quantity, $total, $flower_id, $owner_id);

    if ($stmt->execute()) {
        // Redirect to purchase list page
        header("Location: purchaseList.php");
        exit();
    } else {
        echo "<h2>Error processing purchase. Please try again.</h2>";
        echo "<a href='shop.php'>← Back to Gallery</a>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: shop.php");
    exit();
}
?>
