<?php
// session_start();

// if (!isset($_SESSION['role'])) {
//     die("<p style='color:red;'>Error: User not logged in. Please log in to continue.</p>");
// }

// if ($_SESSION['role'] === 'owner' && isset($_SESSION['owner_id'])) {
//     $owner_id = $_SESSION['owner_id'];
// } elseif ($_SESSION['role'] === 'customer' && isset($_SESSION['user_id'])) {
//     $user_id = $_SESSION['user_id'];
// } else {
//     die("<p style='color:red;'>Error: Invalid session. Please log in again.</p>");
// }

// $conn = new mysqli("localhost", "root", "patricioMed", "petalinkKathstone");
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?>
<?php
session_start();

if (!isset($_SESSION['role'])) {
    die("<p style='color:red;'>Error: User not logged in. Please log in to continue.</p>");
}

if ($_SESSION['role'] === 'owner' && isset($_SESSION['owner_id'])) {
    $owner_id = $_SESSION['owner_id'];

} elseif ($_SESSION['role'] === 'customer' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

} elseif ($_SESSION['role'] === 'admin' && isset($_SESSION['user_id'])) {
    $admin_id = $_SESSION['user_id'];

} else {
    die("<p style='color:red;'>Error: Invalid session. Please log in again.</p>");
}

$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
