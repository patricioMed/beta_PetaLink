<?php
// // accountUpgrade.php
// // PUTANGINA GUMANA DIN SA WAKAS WAHHHHHHHHHHHHHHHHHHHH
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// DB connection
$host = "localhost";
$user = "root";
$pass = "patricioMed";
$dbname = "project_petalink";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Input data
$data = json_decode(file_get_contents("php://input"), true);

$user_id   = $_SESSION['user_id'] ?? null; // logged-in customer
$name      = trim($data["name"] ?? "");
$contact   = trim($data["contact_number"] ?? "");
$email     = trim($data["email"] ?? "");
$password  = trim($data["password"] ?? "");
$address   = trim($data["address"] ?? "");
$role      = trim($data["role"] ?? "customer");
$shop_name = isset($data["shop_name"]) ? trim($data["shop_name"]) : null;

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "Not logged in as customer."]);
    exit();
}

// Validation
if (!$name || !$contact || !$email || !$password || !$address) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit();
}

if ($role === "owner" && !$shop_name) {
    echo json_encode(["success" => false, "message" => "Shop name is required for owners."]);
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// ✅ Step 1: Insert into users table
$stmt = $conn->prepare("
    INSERT INTO users (name, contact_number, email, password, role, address, shop_name) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("sssssss", $name, $contact, $email, $hashedPassword, $role, $address, $shop_name);

if ($stmt->execute()) {
    $new_user_id = $conn->insert_id;
    $stmt->close();

    $owner_id = null;

    // ✅ Step 2: If role is owner, update or insert into flowershopowners
    if ($role === "owner") {
        $check = $conn->prepare("SELECT owner_id FROM flowershopowners WHERE user_id=?");
        $check->bind_param("i", $new_user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            // Insert new row
            $stmt2 = $conn->prepare("
                INSERT INTO flowershopowners (user_id, shop_name, name, email, contact_number, address, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt2->bind_param("isssss", $new_user_id, $shop_name, $name, $email, $contact, $address);
            $stmt2->execute();
            $owner_id = $conn->insert_id;
            $stmt2->close();
        } else {
            // ✅ Update only shop_name for existing row
            $check->bind_result($owner_id);
            $check->fetch();
            $check->close();

            $update = $conn->prepare("UPDATE flowershopowners SET shop_name=? WHERE owner_id=?");
            $update->bind_param("si", $shop_name, $owner_id);
            $update->execute();
            $update->close();
        }

        $_SESSION['owner_id'] = $owner_id;
    }

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully.",
        "user_id" => $new_user_id,
        "owner_id" => $owner_id
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
}

$conn->close();
?>


<?php
// accountUpgrade.php
// problem - NO inserted shop_name only
// session_start();
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

// $user_id    = $_SESSION['user_id'] ?? null; // logged-in customer
// $name       = trim($data["name"] ?? "");
// $contact    = trim($data["contact_number"] ?? "");
// $email      = trim($data["email"] ?? "");
// $password   = trim($data["password"] ?? "");
// $address    = trim($data["address"] ?? "");
// $shop_name  = trim($data["shop_name"] ?? "");

// if (!$user_id) {
//     echo json_encode(["success" => false, "message" => "Not logged in as customer."]);
//     exit();
// }

// if (!$name || !$contact || !$email || !$password || !$address || !$shop_name) {
//     echo json_encode(["success" => false, "message" => "All fields are required to create an owner account."]);
//     exit();
// }

// $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// // ✅ Step 1: Insert a NEW user as owner (only if email not already taken)
// $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND role='owner'");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows > 0) {
//     echo json_encode(["success" => false, "message" => "Owner account with this email already exists."]);
//     $stmt->close();
//     $conn->close();
//     exit();
// }
// $stmt->close();

// $stmt = $conn->prepare("
//     INSERT INTO users (name, contact_number, email, password, role, address, shop_name) 
//     VALUES (?, ?, ?, ?, 'owner', ?, ?)
// ");
// $stmt->bind_param("ssssss", $name, $contact, $email, $hashedPassword, $address, $shop_name);

// if ($stmt->execute()) {
//     $new_owner_user_id = $conn->insert_id;
//     $stmt->close();

//     // ✅ Step 2: Check if already exists in flowershopowners
//     $check = $conn->prepare("SELECT owner_id FROM flowershopowners WHERE user_id=?");
//     $check->bind_param("i", $new_owner_user_id);
//     $check->execute();
//     $check->store_result();

//     if ($check->num_rows === 0) {
//         // Insert new owner row
//         $stmt2 = $conn->prepare("
//             INSERT INTO flowershopowners (user_id, name, email, contact_number, address, status, shop_name)
//             VALUES (?, ?, ?, ?, ?, ?, 'pending')
//         ");
//         $stmt2->bind_param("isssss", $new_owner_user_id, $name, $email, $contact, $address, $shop_name);
//         $stmt2->execute();
//         $owner_id = $conn->insert_id;
//         $stmt2->close();

//         $_SESSION['owner_id'] = $owner_id;
//     } else {
//         // Already exists, fetch existing owner_id
//         $check->bind_result($owner_id);
//         $check->fetch();
//         $_SESSION['owner_id'] = $owner_id;
//     }
//     $check->close();

//     echo json_encode([
//         "success" => true,
//         "message" => "Owner account created successfully.",
//         "owner_user_id" => $new_owner_user_id,
//         "owner_id" => $_SESSION['owner_id']
//     ]);
// } else {
//     echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
// }

// $conn->close();
?>


<?php
// accountUpgrade.php
// problem - it duplicate insert flowershopowners table only
// session_start();
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

// $logged_in_user = $_SESSION['user_id'] ?? null; // the current customer
// $shop_name  = trim($data["shop_name"] ?? "");
// $address    = trim($data["address"] ?? "");
// $name       = trim($data["name"] ?? "");
// $email      = trim($data["email"] ?? "");
// $contact    = trim($data["contact_number"] ?? "");
// $password   = trim($data["password"] ?? ""); // password for the new owner account

// if (!$logged_in_user) {
//     echo json_encode(["success" => false, "message" => "Not logged in."]);
//     exit();
// }

// if (!$name || !$email || !$contact || !$password || !$shop_name || !$address) {
//     echo json_encode(["success" => false, "message" => "Missing required fields."]);
//     exit();
// }

// $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// // ✅ Insert new owner account into `users`
// $stmt = $conn->prepare("
//     INSERT INTO users (name, contact_number, email, password, role, address, shop_name) 
//     VALUES (?, ?, ?, ?, 'owner', ?, ?)
// ");
// $stmt->bind_param("ssssss", $name, $contact, $email, $hashedPassword, $address, $shop_name);

// if ($stmt->execute()) {
//     $owner_user_id = $conn->insert_id;
//     $stmt->close();

//     // ✅ Insert into flowershopowners table
//     $stmt2 = $conn->prepare("
//         INSERT INTO flowershopowners (user_id, shop_name, name, email, contact_number, address, status) 
//         VALUES (?, ?, ?, ?, ?, ?, 'pending')
//     ");
//     $stmt2->bind_param("isssss", $owner_user_id, $shop_name, $name, $email, $contact, $address);
//     $stmt2->execute();
//     $owner_id = $conn->insert_id;
//     $stmt2->close();

//     $_SESSION['owner_id'] = $owner_id;

//     echo json_encode([
//         "success" => true,
//         "message" => "New owner account created successfully.",
//         "owner_user_id" => $owner_user_id,
//         "owner_id" => $owner_id
//     ]);
// } else {
//     echo json_encode(["success" => false, "message" => "Email already exists or error occurred: " . $stmt->error]);
// }

// $conn->close();
?>






<?php
// accountUpgrade.php
// updates the logged-in user and updates the role into onwner can logged in to the same email and password
// session_start();
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

// $user_id    = $_SESSION['user_id'] ?? null; // logged-in user
// $shop_name  = trim($data["shop_name"] ?? "");
// $address    = trim($data["address"] ?? "");

// if (!$user_id) {
//     echo json_encode(["success" => false, "message" => "Not logged in."]);
//     exit();
// }

// if (!$shop_name) {
//     echo json_encode(["success" => false, "message" => "Shop name is required for flower shop owners."]);
//     exit();
// }

// // ✅ Update user role to 'owner' and set shop_name/address
// $stmt = $conn->prepare("UPDATE users SET role='owner', shop_name=?, address=? WHERE id=?");
// $stmt->bind_param("ssi", $shop_name, $address, $user_id);
// if (!$stmt->execute()) {
//     echo json_encode(["success" => false, "message" => "Failed to update user: " . $stmt->error]);
//     exit();
// }
// $stmt->close();

// // ✅ Check if already exists in flowershopowners
// $check = $conn->prepare("SELECT owner_id FROM flowershopowners WHERE user_id=?");
// $check->bind_param("i", $user_id);
// $check->execute();
// $result = $check->get_result();
// if ($result->num_rows === 0) {
//     // Get user details
//     $u = $conn->prepare("SELECT name, email, contact_number, address FROM users WHERE id=?");
//     $u->bind_param("i", $user_id);
//     $u->execute();
//     $u->bind_result($name, $email, $contact, $addr);
//     $u->fetch();
//     $u->close();

//     // Insert into flowershopowners
//     $stmt2 = $conn->prepare("
//         INSERT INTO flowershopowners (user_id, shop_name, name, email, contact_number, address, status) 
//         VALUES (?, ?, ?, ?, ?, ?, 'pending')
//     ");
//     $stmt2->bind_param("isssss", $user_id, $shop_name, $name, $email, $contact, $addr);
//     $stmt2->execute();
//     $owner_id = $conn->insert_id;
//     $stmt2->close();
    
//     $_SESSION['owner_id'] = $owner_id;
// } else {
//     $row = $result->fetch_assoc();
//     $_SESSION['owner_id'] = $row['owner_id'];
// }
// $check->close();

// echo json_encode([
//     "success" => true,
//     "message" => "Account upgraded to owner successfully.",
//     "owner_id" => $_SESSION['owner_id']
// ]);

// $conn->close();
?>












<?php
// accountUpgrade.php
// nakakapag insert na ng mga id's
// session_start();
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
//         // Insert new owner
//         $stmt2 = $conn->prepare("
//             INSERT INTO flowershopOwners (user_id, shop_name, name, email, contact_number, address, status)
//             VALUES (?, ?, ?, ?, ?, ?, 'pending')
//         ");
//         $stmt2->bind_param("isssss", $user_id, $shop_name, $name, $email, $contact, $address);
//         $stmt2->execute();
//         $owner_id = $conn->insert_id;
//         $stmt2->close();

//         // ✅ Store the owner_id in session for verification step
//         $_SESSION['owner_id'] = $owner_id;
//     }

//     echo json_encode([
//         "success" => true,
//         "message" => "Registration successful.",
//         "owner_id" => $_SESSION['owner_id'] ?? null
//     ]);
// } else {
//     echo json_encode(["success" => false, "message" => "Email already exists or error occurred."]);
// }

// $conn->close();
?>

<?php
// Working 8-19-25
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
//         // Check if a flowershopOwners record already exists
//         $owner = $conn->query("SELECT owner_id FROM flowershopOwners WHERE user_id = $user_id")->fetch_assoc();

//         if ($owner) {
//             $owner_id = $owner['owner_id'];
//             // Update existing owner info
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
//             $owner_id = $conn->insert_id; // assign the new owner_id
//             $stmt2->close();
//         }

//         // ✅ Insert verification request with owner_id
//         $stmt3 = $conn->prepare("
//             INSERT INTO verification (user_id, owner_id, status)
//             VALUES (?, ?, 'pending')
//         ");
//         $stmt3->bind_param("ii", $user_id, $owner_id);
//         $stmt3->execute();
//         $stmt3->close();
//     }

//     echo json_encode(["success" => true, "message" => "Registration successful."]);
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