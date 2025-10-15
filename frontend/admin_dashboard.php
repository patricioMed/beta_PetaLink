<?php
include '../backend/security.php';  
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: "Poppins", sans-serif; }
body { display:flex; height:100vh; background:#1a0026; color:white; overflow:hidden; }

/* Sidebar */
.sidebar {
  width: 260px;
  background: #11001a; 
  color: white;
  display: flex;
  flex-direction: column;
  padding: 25px 15px;
  border-right: 2px solid #660066;
}
.sidebar h3 {
  font-size: 20px; 
  text-align:center; 
  color:#a81ea8ff; 
  margin-bottom:20px; 
  font-weight:600;
}
.sidebar a {
  display:flex; 
  align-items:center; 
  gap:12px; 
  margin:6px 0; 
  padding:12px;
  color:white; 
  font-weight:500; 
  text-decoration:none; 
  border-radius:8px;
  background:#1a0026; 
  border: 1px solid #660066;
  transition: all 0.3s ease;
}
.sidebar a:hover { 
  background:#660066; 
  color:white; 
  border-color:#ff66cc;
  transform: translateX(4px);
}
.logo {
  display:flex;
  justify-content:center;
  align-items:center;
  margin-bottom:20px;
  border:2px solid #660066;
  border-radius:20px;
}
.logo img {
  height:120px;
  border-radius:8px;
}
.logout { 
  margin-top:auto; 
  background:#e74c3c !important; 
  border:none !important;
}
.logout:hover { background:#c0392b !important; }

/* Main Content */
.main {
  flex-grow:1; 
  display:flex; 
  flex-direction:column;
  overflow:hidden;
}
.header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  background:#11001a;
  border-bottom:2px solid #660066;
  padding:10px 20px;
}
.header-left {
  display:flex;
  align-items:center;
  gap:12px;
}
.header-left img {
  height:60px;
  border-radius:10px;
}
.logo-text {
  display:flex;
  flex-direction:column;
  line-height:1.2;
}
.logo-text span:first-child {
  font-size:1.4rem;
  font-weight:700;
  color:#a81ea8ff;
  letter-spacing:1px;
}
.tagline {
  font-size:0.8rem;
  font-weight:400;
  color:#ccc;
  letter-spacing:0.5px;
  margin-top:2px;
}
.header-right input {
  padding:10px 12px;
  background:#1a0026;
  border:1px solid #660066;
  border-radius:8px;
  color:white;
  outline:none;
  width:250px;
}

/* Container */
.container {
  padding:20px;
  flex-grow:1;
  overflow-y:auto;
}

/* Stats Cards */
.stats {
  display:flex;
  flex-wrap:wrap;
  gap:20px;
  margin-bottom:20px;
}
.card {
  flex:1 1 250px;
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  text-align:center;
  padding:20px;
  box-shadow:0 8px 24px rgba(0,0,0,0.3);
  transition:0.3s;
}
.card:hover { transform:scale(1.03); border-color:#ff66cc; }
.card h2 { color:#25d66f; font-size:28px; margin-bottom:6px; }
.card p { color:#ccc; font-size:14px; }

/* Table */
.table-container {
  background:#2a0038;
  border:1px solid #660066;
  border-radius:12px;
  overflow:auto;
  box-shadow:0 8px 24px rgba(0,0,0,0.3);
}
table {
  width:100%;
  border-collapse:collapse;
  min-width:800px;
}
th, td {
  padding:12px;
  text-align:center;
  border-bottom:1px solid #660066;
}
th {
  background:#11001a;
  color:#fff;
  position:sticky;
  top:0;
  z-index:1;
}
tr:hover { background:#3a004e; }

/* Responsive */
@media(max-width:900px){
  body{flex-direction:column;}
  .sidebar{width:100%; flex-direction:row; justify-content:space-around; padding:10px;}
  .sidebar h3{display:none;}
  .sidebar a{flex:1;margin:5px;font-size:14px;padding:10px;}
  .header{flex-direction:column; align-items:flex-start; gap:10px;}
  .header-right input{width:100%;}
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="logo">
    <img src="Images/finalLogo_real.png" alt="Admin Logo">
  </div>
  <h3>Admin Panel</h3>
  <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
  <a href="manage_shopOwners.php"><i class="fas fa-store"></i>Manage Owners</a>
  <a href="manage_customers.php"><i class="fas fa-users"></i>Manage Customers</a>
  <a href="manage_ownerVerification.php"><i class="fas fa-clipboard-check"></i>Verification</a>
  <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- Main Section -->
<div class="main">
  <div class="header">
    <div class="header-left">
      <img src="Images/finalLogo_real.png" alt="PetaLink Logo" />
      <div class="logo-text">
        <span>PETALINK</span>
        <span class="tagline">Powered by petals, driven by links</span>
      </div>
    </div>
    <div class="header-right">
      <input type="text" id="searchInput" placeholder="Search users...">
    </div>
  </div>

  <div class="container">
    <div class="stats">
      <div class="card">
        <h2 id="userCount">0</h2>
        <p>Total Users</p>
      </div>
      <div class="card">
        <h2 id="ownerCount">0</h2>
        <p>Shop Owners</p>
      </div>
      <div class="card">
        <h2 id="customerCount">0</h2>
        <p>Customers</p>
      </div>
    </div>

    <h1>Admin Dashboard</h1>
    <div class="table-container">
      <table id="usersTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Contact</th>
            <th>Address</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
let allUsers = [];

// Fetch stats
fetch('../backend/admin_stats.php')
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('userCount').textContent = data.users;
      document.getElementById('ownerCount').textContent = data.owners;
      document.getElementById('customerCount').textContent = data.customer;
    }
  });

// Fetch users
fetch('../backend/fetch_users.php')
  .then(res => res.json())
  .then(users => {
    allUsers = users;
    renderUsers(users);
  });

function renderUsers(users) {
  const tbody = document.querySelector('#usersTable tbody');
  tbody.innerHTML = '';
  users.forEach(user => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${user.id}</td>
      <td>${user.name}</td>
      <td>${user.email}</td>
      <td>${user.role}</td>
      <td>${user.contact_number}</td>
      <td>${user.address}</td>
    `;
    tbody.appendChild(row);
  });
}

document.getElementById('searchInput').addEventListener('input', () => {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const filtered = allUsers.filter(user =>
    user.name.toLowerCase().includes(search) ||
    user.email.toLowerCase().includes(search) ||
    user.role.toLowerCase().includes(search) ||
    user.contact_number.toLowerCase().includes(search) ||
    user.address.toLowerCase().includes(search)
  );
  renderUsers(filtered);
});
</script>
</body>
</html>
