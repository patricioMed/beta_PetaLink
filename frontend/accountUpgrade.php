<?php
include '../backend/security.php';

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PetaLink Register</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap"
      rel="stylesheet"
    />
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }

      body {
        background: #1a0026;
        color: white;
        min-height: 100vh;
        overflow-x: hidden;
        position: relative;
      }

      /* Header */
      header {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        background: #11001a;
        padding: 12px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid #660066;
        flex-wrap: wrap;
        z-index: 1000;
      }
      .logo { display:flex; align-items:center; gap:12px; }
      .logo img { height:55px; border-radius:8px; }
      .logo-text { display:flex; flex-direction:column; line-height:1.2;}
      .logo-text span:first-child { font-size:1.6rem; font-weight:700; color:#a81ea8ff; letter-spacing:1px; }
      .tagline { font-size:0.8rem; font-weight:400; color:#ccc; letter-spacing:0.5px; margin-top:2px; }

      /* Icons */
      .icons { display:flex; align-items:center; gap:15px; flex-wrap:wrap; }
      .icons a { color:white; text-decoration:none; font-size:18px; transition:color 0.3s; }
      .icons a:hover { color:#ff66cc; }

      /* Back button */
      .back-btn{
        position:fixed;
        top:100px;
        left:20px; 
        z-index:1001;
        background: rgba(102,0,102,0.7);
        padding:8px 14px;
        border-radius:25px;
        font-size:14px;
        cursor: pointer;
      }
      .back-btn a{
        color:white;
        text-decoration:none; 
        font-weight:bold;
      }

      /* Falling petals and shapes */
      .petal,
      .shape {
        position: absolute;
        top: -50px;
        border-radius: 50% 30% 50% 30%;
        animation: fall linear infinite;
        filter: blur(4px);
        opacity: 0.8;
      }
      .petal { background: rgba(255, 182, 193, 0.8); }
      .shape {
        background: rgba(255, 220, 220, 0.6);
        width: 20px;
        height: 20px;
        transform: rotate(45deg);
      }
      @keyframes fall {
        0% { transform: translateY(0) rotate(0deg) scale(1); opacity: 1; }
        100% { transform: translateY(110vh) rotate(360deg) scale(0.8); opacity: 0.8; }
      }

      /* Main layout */
      .main-container {
        display: flex;
        min-height: 100vh;
        width: 100%;
        padding-top: 80px;
      }
      .left-container, .right-container {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      .left-container { padding-left: 120px; padding-bottom: 40px; }
      .right-container { padding-right: 160px; }

      /* Logo wrapper */
      .logo-wrapper { display: flex; flex-direction: column; justify-content: center; align-items: center; }
      .logo-container img { height: 330px; width: 330px; border-radius: 12px; }
      .logo-text { text-align: left; }
      .logo-text h1 { font-family: "Great Vibes", cursive; text-align: center; font-size: 40px; color: #ff66cc; }
      .logo-text p { font-size: 16px; color: #fff; margin: 0; }

      /* Register Card */
      .login-container {
        background: #2a0038;
        border: 1px solid #660066;
        border-radius: 12px;
        padding: 30px 25px;
        width: 400px;
        text-align: center;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        z-index: 10;
      }
      .login-container h2 { font-family: "Great Vibes", cursive; font-size: 32px; color: #ff66cc; margin-bottom: 25px; }

      /* Form Grid */
      .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
      .form-grid input, .form-grid select {
        width: 100%; padding: 10px 12px; border-radius: 8px;
        background: #1a0026; border: 1px solid #660066; color: white; outline: none; font-size: 14px;
      }
      input::placeholder { color: #ccc; }

      .password-wrapper { position: relative; }
      .password-wrapper input { padding: 12px 40px 12px 12px; }
      .toggle-eye {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        width: 22px; height: 22px; cursor: pointer; opacity: 0.7; color: white;
      }
      .toggle-eye:hover { opacity: 1; color: #25d66f; }

      button {
        width: 100%; padding: 12px; margin-top: 12px;
        border: none; border-radius: 30px;
        background: #660066; color: white; font-size: 16px; cursor: pointer; font-weight: 600; transition: 0.3s;
      }
      button:hover { background: #a81ea8ff; transform: scale(1.05); }

      .loginHere { color: #ff66cc; text-decoration: underline; }
      .loginHere:hover { color: #25d66f; }

      .error-message { margin-top: 10px; color: #ff4444; font-size: 14px; }
      .success-message { margin-top: 10px; color: #25d66f; font-size: 14px; }

      /* Responsive */
      @media screen and (max-width: 900px) {
        .main-container { flex-direction: column; padding: 0 20px; }
        .left-container, .right-container { padding: 0; }
        .logo-container img { height: 200px; width: 200px; }
        .login-container { width: 90%; padding: 25px 20px; }
        .login-container h2 { font-size: 28px; }
      }
      @media screen and (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }
        .login-container h2 { font-size: 24px; }
      }
    </style>
  </head>
  <body>
    <!-- Petals -->
    <!-- <div class="petal"></div><div class="petal"></div><div class="petal"></div><div class="petal"></div><div class="petal"></div>
    <div class="shape"></div><div class="shape"></div><div class="shape"></div><div class="shape"></div> -->

    <header>
      <div class="logo">
        <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
        <div class="logo-text">
          <span>PETALINK</span>
          <span class="tagline">Powered by petals, driven by links</span>
        </div>
      </div>
      <div class="icons">
       <a href="notification.php" id="notifBell" title="Notification" style="position:relative;">
      <i class="fas fa-bell"></i>
      <!-- Always show badge -->
      <span id="notifCount" style="
        position:absolute;
        top:-5px;
        right:-9px;
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

    <div class="main-container">
      <!-- Left -->
      <div class="left-container">
        <div class="logo-wrapper">
          <div class="logo-container">
            <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
          </div>
          <div class="logo-text">
            <h1>Peta Link</h1>
            <p>Powered by petals, driven by links</p>
          </div>
        </div>
      </div>

      <!-- Right: Form -->
      <div class="right-container">
        <div class="login-container">
          <h2>Register</h2>
          <form id="registerForm">
            <div class="form-grid" id="mainGrid">
              <input type="text" id="name" placeholder="Full Name" required />
              <input type="text" id="contact" placeholder="Contact Number" required pattern="^(09|\+639)\d{9}$" />
              <input
  type="text"
  id="address"
  placeholder="Street, Barangay, City (e.g., Bartolome St, Bonuan Gueset, Dagupan City)"
  title="Street, Barangay, City (e.g., Bartolome St, Bonuan Gueset, Dagupan City"
  required
  pattern="^[a-zA-Z0-9\s]+,\s*[a-zA-Z\s]+,\s*[a-zA-Z\s]+$"
/>

              <input type="email" id="email" placeholder="Email" required />
              <div class="password-wrapper">
                <input type="password" id="password" placeholder="Password" required
                title="Use at least 8 characters, including number, uppercase, lowercase & special symbol."
                  pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=!]).{8,}" />
                <img src="Images/hiddenEye.png" class="toggle-eye" onclick="togglePassword('password', this)" />
              </div>
              <div class="password-wrapper">
                <input type="password" id="confirmPassword" placeholder="Confirm Password" required />
                <img src="Images/hiddenEye.png" class="toggle-eye" onclick="togglePassword('confirmPassword', this)" />
              </div>
              <!-- <select id="role" required>
                <option value="">Select Role</option>
                <option value="owner">Shop Owner</option>
              </select>
              <input type="text" id="shopName" placeholder="Shop Name" style="display: none" /> -->
               <input type="hidden" id="role" value="owner" />
               <input type="text" id="shopName" placeholder="Shop Name" required/>
               
            </div>
            <!-- <small style="color: #25d66f; display: block; margin-top: 5px">
              FOR THE PASSWORD :
            </small>
            <small style="color: #ccc; display: block; margin-top: 5px">
              Use at least 8 characters, including number, uppercase, lowercase & special symbol.
            </small> -->
            <button type="submit">Register</button>
            <p id="error" class="error-message"></p>
            <p id="success" class="success-message"></p>
          </form>
        </div>
      </div>
    </div>

    <script>
      function togglePassword(fieldId, icon) {
        const field = document.getElementById(fieldId);
        const isHidden = field.type === "password";
        field.type = isHidden ? "text" : "password";
        icon.src = isHidden ? "Images/showEye.png" : "Images/hiddenEye.png";
      }

      // Handle Shop Owner selection
      const roleSelect = document.getElementById("role");
      const shopInput = document.getElementById("shopName");
      const mainGrid = document.getElementById("mainGrid");

      roleSelect.addEventListener("change", function () {
        if (this.value === "owner") {
          shopInput.style.display = "block";
          shopInput.required = true;
          mainGrid.style.gridTemplateColumns = "1fr 1fr";
        } else {
          shopInput.style.display = "none";
          shopInput.required = false;
        }
      });
    </script>
    <script src="accountUpgrade.js"></script>
 <!-- <script src="JS/notification.js"></script> -->
  </body>
</html>
