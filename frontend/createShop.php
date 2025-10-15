<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../backend/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $shop_name = trim($_POST['shop_name']);

    if (!empty($shop_name)) {
        $conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if user already has a shop entry
        $check = $conn->prepare("SELECT owner_id FROM flowershopOwners WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "You already have a flower shop registered.";
        } else {
            $stmt = $conn->prepare("INSERT INTO flowershopOwners (user_id, shop_name) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $shop_name);

            if ($stmt->execute()) {
                $success = "Flower shop created successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $check->close();
        $conn->close();
    } else {
        $error = "Shop name is required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Flower Shop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        input[type="text"], button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .msg {
            margin-top: 10px;
            font-weight: bold;
        }
        .msg.success { color: green; }
        .msg.error { color: red; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Your Flower Shop</h2>
        <form method="POST" action="">
            <input type="text" name="shop_name" placeholder="Enter Shop Name" required>
            <button type="submit">Create Shop</button>
        </form>
        <?php if ($success): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
