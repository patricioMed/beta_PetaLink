<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../backend/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Select Flower Category</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      min-height: 100vh;
      background: #1a0026; /* dark violet base */
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: #fff;
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: #11001a;
      padding: 12px 30px;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      border-bottom: 2px solid #660066;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo img {
      height: 55px;
      border-radius: 8px;
    }

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

    .main-content {
      text-align: center;
      margin-top: 100px;
      padding: 30px;
      background: #2a0038; /* lighter violet box */
      border-radius: 12px;
      border: 1px solid #660066;
    }

    h2 {
      font-size: 2rem;
      margin-bottom: 25px;
      color: white;
      font-weight: 600;
    }

    .category-buttons {
      display: flex;
      justify-content: center;
      gap: 18px;
      flex-wrap: wrap;
    }

    .category-button {
      padding: 12px 26px;
      font-size: 1rem;
      border-radius: 25px;
      background: #1a0026;
      color: #fff;
      font-weight: 500;
      cursor: pointer;
      border: 1px solid #660066;
      transition: all 0.3s ease;
    }

    .category-button:hover {
      background: #660066;
      color: white;
      border-color: #ff66cc;
    }

    @media (max-width: 768px) {
      .category-buttons {
        flex-direction: column;
        gap: 15px;
      }

      .category-button {
        width: 80%;
      }

      .main-content {
        margin-top: 120px;
        padding: 20px;
      }

      .logo-text span:first-child {
        font-size: 1.3rem;
      }

      .tagline {
        font-size: 0.7rem;
      }
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
  <h2>Flower Categories</h2>
  <div class="category-buttons">
    <button onclick="location.href='ownerCategory_anniversary.php'" class="category-button"><i class="fas fa-heart"></i> Anniversary</button>
    <button onclick="location.href='ownerCategory_birthday.php'" class="category-button"><i class="fas fa-birthday-cake"></i> Birthday</button>
    <button onclick="location.href='ownerCategory_valentines.php'" class="category-button"><i class="fas fa-gift"></i> Valentines</button>
    <button onclick="location.href='ownerCategory_sympathy.php'" class="category-button"><i class="fas fa-hand-holding-heart"></i> Sympathy</button>
    <button onclick="location.href='ownerCategory_others.php'" class="category-button"><i class="fas fa-leaf"></i> Others</button>
  </div>
</div>

</body>
</html>
