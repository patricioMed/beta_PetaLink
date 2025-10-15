<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: loginCustomers.html");
    exit();
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $shop_name = $_POST['shop_name'];
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    $password = $_POST['password'];

    // Update users table
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sqlUser = "UPDATE users SET name=?, email=?, contact_number=?, address=?, password=? WHERE id=?";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bind_param("sssssi", $name, $email, $contact_number, $address, $hashedPassword, $user_id);
    } else {
        $sqlUser = "UPDATE users SET name=?, email=?, contact_number=?, address=?, shop_name=? WHERE id=?";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bind_param("sssssi", $name, $email, $contact_number, $address, $shop_name, $user_id);
    }
    $stmtUser->execute();
    $stmtUser->close();

    // Update flowershopOwners table
    $sqlShop = "UPDATE flowershopOwners SET name=?, shop_name=?, longitude=?, latitude=? WHERE user_id=?";
    $stmtShop = $conn->prepare($sqlShop);
    $stmtShop->bind_param("ssssi", $name, $shop_name, $longitude, $latitude, $user_id);
    $stmtShop->execute();
    $stmtShop->close();

    // Refresh the page to show updated data
    header("Location: editOwnerProfile.php");
    exit();
}

// Fetch owner profile info
$sql = "
    SELECT u.name, u.email, u.contact_number, u.address,
           f.shop_name, f.longitude, f.latitude
    FROM users u
    JOIN flowershopOwners f ON u.id = f.user_id
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $owner = $result->fetch_assoc();
} else {
    echo "Owner profile not found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Owner Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins", sans-serif; }
body { background:#1a0026; color:white; min-height:100vh; }

/* Header */
header {
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  background: #11001a;
  padding: 12px 30px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  border-bottom: 2px solid #660066;
  z-index: 1000;
}
.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}
.logo img { height: 55px; border-radius: 8px; }
.logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}
.logo-text span:first-child {
  font-size: 1.6rem;
  font-weight: 700;
  color: #a81ea8ff;
  letter-spacing: 1px;
}
.tagline {
  font-size: 0.8rem;
  font-weight: 400;
  color: #ccc;
  letter-spacing: 0.5px;
  margin-top: 2px;
}

/* Main Content */
.main-content { width: 95%; max-width: 700px; margin: 100px auto 50px auto; padding: 20px; }

/* Card Style */
.card {
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  padding:25px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  transition: transform 0.3s;
}
.card:hover { transform: translateY(-5px); }

h2 { text-align: center; margin-bottom: 20px; color:#ff66cc; font-weight:600; }

.info { margin-bottom: 15px; }
.info label { font-weight:bold; display:block; margin-bottom:5px; color:#fff; }
.info p { padding:10px; background:#1a0026; border-radius:8px; border:1px solid #660066; }

/* Buttons */
button, .form-btn { 
  background:#660066;
  color:white; 
  border:none; 
  padding:12px 25px; 
  border-radius:30px; 
  cursor:pointer; 
  font-weight:500; 
  transition: 0.3s; 
}
button:hover, .form-btn:hover { 
  background:#a81ea8ff; 
}

.back-btn { display:inline-block; margin-bottom:20px; padding:10px 20px; color:white; background:#1a0026; text-decoration:none; border-radius:25px; font-weight:600; border:1px solid #660066; transition:0.3s; }
.back-btn:hover { background:#660066; color:white; border-color:#ff66cc; }

#edit-form input[type="text"], 
#edit-form input[type="email"], 
#edit-form input[type="password"] { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #660066; background: #1a0026; color:white; margin-bottom: 15px; }
#edit-form input:focus { border-color: #ff66cc; outline:none; background:#2a0038; }

.password-wrapper { position: relative; }
.toggle-eye { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 22px; height: 22px; cursor: pointer; opacity: 0.7; }
.toggle-eye:hover { opacity: 1; }

@media (max-width: 768px) {
    .main-content { padding: 15px; }
    .logo-text span:first-child { font-size: 1.4rem; }
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
</header>

<div class="main-content">
    <div class="card" id="display-section">
        <h2>Owner Profile</h2>
        <div class="info"><label>Name:</label><p><?= htmlspecialchars($owner['name']) ?></p></div>
        <div class="info"><label>Email:</label><p><?= htmlspecialchars($owner['email']) ?></p></div>
        <div class="info"><label>Contact Number:</label><p><?= htmlspecialchars($owner['contact_number']) ?></p></div>
        <div class="info"><label>Address:</label><p><?= htmlspecialchars($owner['address']) ?></p></div>
        <div class="info"><label>Shop Name:</label><p><?= htmlspecialchars($owner['shop_name']) ?></p></div>
        <div class="info"><label>Longitude:</label><p><?= htmlspecialchars($owner['longitude']) ?></p></div>
        <div class="info"><label>Latitude:</label><p><?= htmlspecialchars($owner['latitude']) ?></p></div>
        <div class="info"><label>Password:</label><p>********</p></div>
        <div style="text-align:center;"><button id="edit-btn">Edit Profile</button></div>
    </div>

    <form method="POST" id="edit-form" class="card" style="display:none;">
        <h2>Edit Profile</h2>
        <div class="info"><label>Name:</label><input type="text" name="name" value="<?= htmlspecialchars($owner['name']) ?>" required></div>
        <div class="info"><label>Email:</label><input type="email" name="email" value="<?= htmlspecialchars($owner['email']) ?>" required></div>
        <div class="info"><label>Contact Number:</label><input type="text" name="contact_number" value="<?= htmlspecialchars($owner['contact_number']) ?>" required></div>
        <div class="info"><label>Address:</label><input type="text" name="address" value="<?= htmlspecialchars($owner['address']) ?>"></div>
        <div class="info"><label>Shop Name:</label><input type="text" name="shop_name" value="<?= htmlspecialchars($owner['shop_name']) ?>" required></div>
        <div class="info"><label>Longitude:</label><input type="text" name="longitude" value="<?= htmlspecialchars($owner['longitude']) ?>"></div>
        <div class="info"><label>Latitude:</label><input type="text" name="latitude" value="<?= htmlspecialchars($owner['latitude']) ?>"></div>
        <div class="info">
            <label>New Password (leave blank to keep current):</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Enter new password" />
                <img src="Images/hiddenEye.png" id="togglePassword" class="toggle-eye" alt="Toggle Password" />
            </div>
        </div>
        <div style="text-align:center;">
            <button type="submit">Save</button>
            <button type="button" id="cancel-btn">Cancel</button>
        </div>
    </form>
</div>

<script>
document.getElementById("edit-btn").onclick = function () {
    document.getElementById("display-section").style.display = "none";
    document.getElementById("edit-form").style.display = "block";
};
document.getElementById("cancel-btn").onclick = function () {
    document.getElementById("edit-form").style.display = "none";
    document.getElementById("display-section").style.display = "block";
};

const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");
togglePassword.addEventListener("click", () => {
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);
    togglePassword.src = type === "password" ? "Images/hiddenEye.png" : "Images/showEye.png";
});
</script>

</body>
</html>
