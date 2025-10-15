<?php
// admin.php - PETAlink Admin Dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "patricioMed";
$dbname = "project_petalink";

if (!isset($_SESSION['owner_id'])) {
    die("<p style='color:red;'>Error: Admin not logged in. Please log in to continue.</p>");
}

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get counts
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalOwners = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='owner'")->fetch_assoc()['total'];
$totalCustomers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='customer'")->fetch_assoc()['total'];
$totalShops = $conn->query("SELECT COUNT(*) as total FROM flowershopOwners")->fetch_assoc()['total'];

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>PETAlink Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .dashboard { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; text-align: center; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #333; color: white; }
        .btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; }
        .edit { background: #007bff; color: white; }
        .delete { background: #dc3545; color: white; }
    </style>
</head>
<body>

<h1>PETAlink Admin Dashboard</h1>

<div class="dashboard">
    <div class="card">👤 Total Users: <b><?php echo $totalUsers; ?></b></div>
    <div class="card">🏬 Owners: <b><?php echo $totalOwners; ?></b></div>
    <div class="card">🛒 Customers: <b><?php echo $totalCustomers; ?></b></div>
    <div class="card">🌸 Shops: <b><?php echo $totalShops; ?></b></div>
</div>

<h2>Registered Users</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Contact</th>
        <th>Shop</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $users->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo ucfirst($row['role']); ?></td>
        <td><?php echo $row['contact_number']; ?></td>
        <td><?php echo $row['shop_name'] ?: 'N/A'; ?></td>
        <td>
            <button class="btn edit">Edit</button>
            <button class="btn delete">Delete</button>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
<?php $conn->close(); ?>
