<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$dbname = "project_petalink";
$dbuser = "root";
$dbpass = "patricioMed";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$userCount = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$ownerCount = $conn->query("SELECT COUNT(*) as total FROM flowershopOwners")->fetch_assoc()['total'];

$customerCount = $conn->query("SELECT COUNT(*) as total FROM users WHERE LOWER(role) = 'customer'")->fetch_assoc()['total'];


echo json_encode([
    "success" => true,
    "users" => $userCount,
    "owners" => $ownerCount,
    "customer" => $customerCount
]);

$conn->close();
?>

<?php
// Working 8-19-25
// nakakapag insert user_id sa 3 tables
// ayusin user_id or owner_id bukas 
// kaya ko ito 
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// header('Content-Type: application/json');

// $host = "localhost";
// $user = "root";
// $pass = "patricioMed";
// $dbname = "petalinkKathstone";

// $conn = new mysqli($host, $user, $pass, $dbname);

// if ($conn->connect_error) {
//     echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
//     exit();
// }

// $data = json_decode(file_get_contents("php://input"), true);

// // Basic account info
// $name       = trim($data["name"] ?? "");
// $contact    = trim($data["contact_number"] ?? "");
// $email      = trim($data["email"] ?? "");
// $password   = trim($data["password"] ?? "");
// $role       = trim($data["role"] ?? "");
// $address    = trim($data["address"] ?? "");
// $shop_name  = trim($data["shop_name"] ?? "");

// // Verification documents
// $dti_registration        = trim($data["dti_registration"] ?? "");
// $barangay_clearance      = trim($data["barangay_clearance"] ?? "");
// $business_permit         = trim($data["business_permit"] ?? "");
// $bir_certificate         = trim($data["bir_certificate"] ?? "");
// $fire_safety             = trim($data["fire_safety"] ?? "");
// $sanitary_permit         = trim($data["sanitary_permit"] ?? "");
// $zoning_clearance        = trim($data["zoning_clearance"] ?? "");
// $sss_philhealth_pagibig  = trim($data["sss_philhealth_pagibig"] ?? "");

// if (!$name || !$contact || !$email || !$password || !$role || !$address) {
//     echo json_encode(["success" => false, "message" => "Missing required fields."]);
//     exit();
// }

// if ($role === "owner" && !$shop_name) {
//     echo json_encode(["success" => false, "message" => "Shop name is required for flower shop owners."]);
//     exit();
// }

// $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// // Insert user into `users` table
// $stmt = $conn->prepare("
//     INSERT INTO users (name, contact_number, email, password, role, address, shop_name) 
//     VALUES (?, ?, ?, ?, ?, ?, ?)
// ");
// $stmt->bind_param("sssssss", $name, $contact, $email, $hashedPassword, $role, $address, $shop_name);

// if ($stmt->execute()) {
//     $user_id = $conn->insert_id;

//     if ($role === "owner") {
//         // Check if flowershopOwners record exists
//         $owner = $conn->query("SELECT owner_id FROM flowershopOwners WHERE user_id = $user_id")->fetch_assoc();

//         if ($owner) {
//             $owner_id = $owner['owner_id'];
//             // Update owner info
//             $stmt2 = $conn->prepare("
//                 UPDATE flowershopOwners 
//                 SET shop_name = ?, name = ?, email = ?, contact_number = ?, address = ? 
//                 WHERE user_id = ?
//             ");
//             $stmt2->bind_param("sssssi", $shop_name, $name, $email, $contact, $address, $user_id);
//             $stmt2->execute();
//             $stmt2->close();
//         } else {
//             // Insert new owner
//             $stmt2 = $conn->prepare("
//                 INSERT INTO flowershopOwners (user_id, shop_name, name, email, contact_number, address, status)
//                 VALUES (?, ?, ?, ?, ?, ?, 'pending')
//             ");
//             $stmt2->bind_param("isssss", $user_id, $shop_name, $name, $email, $contact, $address);
//             $stmt2->execute();
//             $owner_id = $conn->insert_id; // assign new owner_id
//             $stmt2->close();
//         }

//         // ✅ Insert verification request with all submitted data
//         $stmt3 = $conn->prepare("
//             INSERT INTO verification (
//                 user_id, owner_id, dti_registration, barangay_clearance, business_permit, bir_certificate, 
//                 fire_safety, sanitary_permit, zoning_clearance, sss_philhealth_pagibig, status
//             )
//             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
//         ");
//         $stmt3->bind_param(
//             "iissssssss",
//             $user_id, $owner_id,
//             $dti_registration, $barangay_clearance, $business_permit, $bir_certificate,
//             $fire_safety, $sanitary_permit, $zoning_clearance, $sss_philhealth_pagibig
//         );
//         $stmt3->execute();
//         $stmt3->close();
//     }

//     echo json_encode(["success" => true, "message" => "Registration and verification submitted successfully."]);
// } else {
//     echo json_encode(["success" => false, "message" => "Email already exists or error occurred."]);
// }

// $conn->close();
?>


<?php
//Working 8-19-25
// ngayon nagana kaso walang owner_id 10/3/25
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// header('Content-Type: application/json');

// $host = "localhost";
// $user = "root";
// $pass = "patricioMed";
// $dbname = "petalinkKathstone";

// $conn = new mysqli($host, $user, $pass, $dbname);

// if ($conn->connect_error) {
//     echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
//     exit();
// }

// $data = json_decode(file_get_contents("php://input"), true);

// $name       = trim($data["name"] ?? "");
// $contact    = trim($data["contact_number"] ?? "");
// $email      = trim($data["email"] ?? "");
// $password   = trim($data["password"] ?? "");
// $role       = trim($data["role"] ?? "");
// $address    = trim($data["address"] ?? "");
// $shop_name  = trim($data["shop_name"] ?? "");

// if (!$name || !$contact || !$email || !$password || !$role || !$address) {
//     echo json_encode(["success" => false, "message" => "Missing required fields."]);
//     exit();
// }

// if ($role === "owner" && !$shop_name) {
//     echo json_encode(["success" => false, "message" => "Shop name is required for flower shop owners."]);
//     exit();
// }

// $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// // Insert user into `users` table
// $stmt = $conn->prepare("
//     INSERT INTO users (name, contact_number, email, password, role, address, shop_name) 
//     VALUES (?, ?, ?, ?, ?, ?, ?)
// ");
// $stmt->bind_param("sssssss", $name, $contact, $email, $hashedPassword, $role, $address, $shop_name);

// if ($stmt->execute()) {
//     $user_id = $conn->insert_id;

//     if ($role === "owner") {
//         // Instead of inserting again, just update the existing flowershopOwners record
//         $stmt2 = $conn->prepare("
//             UPDATE flowershopOwners 
//             SET shop_name = ?, name = ?, email = ?, contact_number = ?, address = ? 
//             WHERE user_id = ?
//         ");
//         $stmt2->bind_param("sssssi", $shop_name, $name, $email, $contact, $address, $user_id);

//         if (!$stmt2->execute()) {
//             echo json_encode(["success" => false, "message" => "Owner created but failed to update shop."]);
//             exit();
//         }
//     }

//     echo json_encode(["success" => true, "message" => "Registration successful03."]);
// } else {
//     echo json_encode(["success" => false, "message" => "Email already exists or error occurred."]);
// }

// $conn->close();
?>
 