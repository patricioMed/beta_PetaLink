<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PETA Link - Business Verification</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
    body { 
      background:#1a0026; 
      color:white; 
      min-height:100vh; 
      display:flex; 
      flex-direction:column; 
    }

    /* Header */
    header {
      flex-shrink:0;
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

    /* Content */
    .content-wrapper { 
      flex:1;
      display:flex; 
      justify-content:center; 
      align-items:flex-start; 
      padding:20px; 
      margin-top:100px; 
    }
    main {
      max-width:700px; 
      width:100%; 
      background:rgba(42,0,56,0.85); 
      padding:30px; 
      border-radius:20px; 
      box-shadow:0 8px 24px rgba(0,0,0,0.3); 
    }

    h2 { text-align:center; margin-bottom:25px; color:#ff66cc; }

    .form-group { margin-bottom:18px; }
    .form-group label {
      display:block; 
      margin-bottom:6px; 
      font-weight:600; 
      color:#ff66cc;
    }
    input[type="file"] {
      width:100%; 
      padding:10px; 
      border-radius:8px; 
      border:1px solid #660066; 
      background:rgba(255,255,255,0.1); 
      color:white; 
    }
    input[type="file"]::-webkit-file-upload-button {
      background:#660066;
      color:white;
      border:none;
      border-radius:20px;
      padding:8px 14px;
      cursor:pointer;
      transition: background 0.3s;
    }
    input[type="file"]::-webkit-file-upload-button:hover {
      background:#ff66cc;
    }

    button {
      display:block;
      width:100%;
      padding:12px;
      background:#660066;
      color:white;
      border:none;
      border-radius:25px;
      font-size:16px;
      font-weight:bold;
      cursor:pointer;
      transition:background 0.3s, transform 0.2s;
      margin-top:10px;
    }
    button:hover {
      background:#ff66cc;
      transform:scale(1.05);
    }

    /* Responsive */
    @media (max-width:600px){
      .logo img{height:45px;}
      .logo-text span:first-child{font-size:1.2rem;}
      .tagline{font-size:0.7rem;}
      h2{font-size:1.2rem;}
      main{padding:20px;}
    }
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
</header>

<div class="content-wrapper">
  <main>
    <h2>Business Verification - Upload Permits</h2>
    <form action="accountVerification.php" method="POST" enctype="multipart/form-data">
      
      <!-- <div class="form-group">
        <label>DTI/SEC Registration</label>
        <input type="file" name="dti_registration" required>
      </div> -->
      
      <div class="form-group">
        <label>Barangay Clearance</label>
        <input type="file" name="barangay_clearance" required>
      </div>
      
      <div class="form-group">
        <label>Mayor’s / Business Permit</label>
        <input type="file" name="business_permit" required>
      </div>
      
      <!-- <div class="form-group">
        <label>BIR Certificate of Registration</label>
        <input type="file" name="bir_certificate" required>
      </div> -->
      
      <!-- <div class="form-group">
        <label>Fire Safety Inspection Certificate</label>
        <input type="file" name="fire_safety" required>
      </div> -->
      
      <!-- <div class="form-group">
        <label>Sanitary Permit</label>
        <input type="file" name="sanitary_permit" required>
      </div> -->
      
      <!-- <div class="form-group">
        <label>Zoning Clearance</label>
        <input type="file" name="zoning_clearance">
      </div> -->
      
      <div class="form-group">
        <label>SSS / PhilHealth / Pag-IBIG Registration (if applicable)</label>
        <input type="file" name="sss_philhealth_pagibig">
      </div>
      
      <button type="submit">Submit Verification</button>
    </form>
  </main>
</div>
<script>
document.querySelector("form").addEventListener("submit", function(e) {
    e.preventDefault(); // Stop normal form submit

    let formData = new FormData(this);

    fetch("accountVerification.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert("Upload successful! ✅\n\n" + data);
    })
    .catch(error => {
        alert("Error uploading files ❌");
        console.error(error);
    });
});
</script>


</body>
</html>
