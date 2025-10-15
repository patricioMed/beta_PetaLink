    <?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../backend/loginCustomers.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id  = $_SESSION['user_id'];
$order_id = isset($_POST['flower_id']) ? intval($_POST['flower_id']) : 0; // order id from checkout_history
$rating   = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : "";

if ($order_id > 0 && $rating >= 1 && $rating <= 5 && !empty($feedback)) {
    // Update checkout_history with rating and feedback
    $sql = "UPDATE checkout_history 
            SET rating = ?, feedback = ? 
            WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $rating, $feedback, $order_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Thank you for your feedback!";
    } else {
        $_SESSION['message'] = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "⚠️ Invalid rating or feedback.";
}

$conn->close();

// Redirect back to Completed Orders
header("Location: checkoutToCompleted.php");
exit();

    
    
    // session_start();
    // if (!isset($_SESSION['user_id'])) {
    //     die("Unauthorized");
    // }

    // $conn = new mysqli("localhost", "root", "patricioMed", "petalinkKathstone");
    // if ($conn->connect_error) {
    //     die("Connection failed: " . $conn->connect_error);
    // }
    // $user_id = $_SESSION['user_id'];
    // $flower_id = intval($_POST['flower_id']);
    // $category = $_POST['category'];
    // $rating = intval($_POST['rating']);
    // $feedback = trim($_POST['feedback']);

    // // Prevent duplicate rating by same user for same product
    // $check_sql = "SELECT rating_id FROM ratings WHERE user_id = ? AND flower_id = ? AND flower_category = ?";
    // $stmt = $conn->prepare($check_sql);
    // $stmt->bind_param("iis", $user_id, $flower_id, $category);
    // $stmt->execute();
    // $stmt->store_result();

    // if ($stmt->num_rows > 0) {
    //     echo "You already rated this product.";
    //     exit();
    // }

    // $stmt = $conn->prepare("INSERT INTO ratings (user_id, flower_category, flower_id, rating, feedback) VALUES (?, ?, ?, ?, ?)");
    // $stmt->bind_param("isiis", $user_id, $category, $flower_id, $rating, $feedback);

    // if ($stmt->execute()) {
    //     header("Location: details.php?id=$flower_id");
    // } else {
    //     echo "Error: " . $stmt->error;
    // }
    ?>
