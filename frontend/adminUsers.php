<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "patricioMed";
$dbname = "project_petalink";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT id, name, email, role, shop_name, created_at FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin Panel</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #f4f4f4; }
        a { text-decoration: none; color: blue; }
    </style>
</head>
<body>
    <h2>👥 Manage Users</h2>
    <a href="admin.php">⬅ Back to Dashboard</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Shop Name</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= ucfirst($row['role']) ?></td>
            <td><?= $row['shop_name'] ?: '-' ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="admin_user_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">🗑 Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
