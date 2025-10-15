<?php
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
//         // --- Get Lat/Lon from OSM ---
//         $lat = null;
//         $lon = null;

//         $encodedAddress = urlencode($address);
//         $geoUrl = "https://nominatim.openstreetmap.org/search?format=json&q={$encodedAddress}";

//         $geoResponse = file_get_contents($geoUrl);
//         if ($geoResponse) {
//             $geoData = json_decode($geoResponse, true);
//             if (!empty($geoData[0])) {
//                 $lat = $geoData[0]['lat'];
//                 $lon = $geoData[0]['lon'];
//             }
//         }

//         // Update flowershopOwners with lat/lon
//         $stmt2 = $conn->prepare("
//             UPDATE flowershopowners 
//             SET shop_name = ?, name = ?, email = ?, contact_number = ?, address = ?, latitude = ?, longitude = ? 
//             WHERE user_id = ?
//         ");
//         $stmt2->bind_param("sssssssi", $shop_name, $name, $email, $contact, $address, $lat, $lon, $user_id);

//         if (!$stmt2->execute()) {
//             echo json_encode(["success" => false, "message" => "Owner created but failed to update shop."]);
//             exit();
//         }
//     }

//     echo json_encode(["success" => true, "message" => "Registration successful."]);
// } else {
//     echo json_encode(["success" => false, "message" => "Email already exists or error occurred."]);
// }

// $conn->close();
?>

<?php
//Working 8-19-25
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "patricioMed";
$dbname = "project_petalink";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$name       = trim($data["name"] ?? "");
$contact    = trim($data["contact_number"] ?? "");
$email      = trim($data["email"] ?? "");
$password   = trim($data["password"] ?? "");
$role       = trim($data["role"] ?? "");
$address    = trim($data["address"] ?? "");
$shop_name  = trim($data["shop_name"] ?? "");

if (!$name || !$contact || !$email || !$password || !$role || !$address) {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit();
}

if ($role === "owner" && !$shop_name) {
    echo json_encode(["success" => false, "message" => "Shop name is required for flower shop owners."]);
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert user into `users` table
$stmt = $conn->prepare("
    INSERT INTO users (name, contact_number, email, password, role, address, shop_name) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("sssssss", $name, $contact, $email, $hashedPassword, $role, $address, $shop_name);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;

    if ($role === "owner") {
        // Instead of inserting again, just update the existing flowershopOwners record
        $stmt2 = $conn->prepare("
            UPDATE flowershopOwners 
            SET shop_name = ?, name = ?, email = ?, contact_number = ?, address = ? 
            WHERE user_id = ?
        ");
        $stmt2->bind_param("sssssi", $shop_name, $name, $email, $contact, $address, $user_id);

        if (!$stmt2->execute()) {
            echo json_encode(["success" => false, "message" => "Owner created but failed to update shop."]);
            exit();
        }
    }

    echo json_encode(["success" => true, "message" => "Registration successful03."]);
} else {
    echo json_encode(["success" => false, "message" => "Email already exists or error occurred."]);
}

$conn->close();
?>


<?php
//Working old original code
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

// $name = trim($data["name"] ?? "");
// $contact = trim($data["contact_number"] ?? "");
// $email = trim($data["email"] ?? "");
// $password = trim($data["password"] ?? "");
// $role = trim($data["role"] ?? "");
// $address = trim($data["address"] ?? "");
// $shop_name = trim($data["shop_name"] ?? "");

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
// $stmt = $conn->prepare("INSERT INTO users (name, contact_number, email, password, role, address, shop_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
// $stmt->bind_param("sssssss", $name, $contact, $email, $hashedPassword, $role, $address, $shop_name);

// if ($stmt->execute()) {
//     $user_id = $conn->insert_id;

//     // If owner, also insert into flower shop table // adding 1 row in table
//         if ($role === "owner") {
//             $stmt2 = $conn->prepare("INSERT INTO flowershopOwners (user_id, name, email, contact_number, address, shop_name) VALUES (?, ?, ?, ?, ?, ?)");
//             $stmt2->bind_param("isssss", $user_id, $name, $email, $contact, $address, $shop_name);
//             if (!$stmt2->execute()) {
//                 echo json_encode(["success" => false, "message" => "Owner created but failed to create shop."]);
//                 exit();
//             }
//         }

//     echo json_encode(["success" => true, "message" => "Registration successful."]);
// } else {
//     echo json_encode(["success" => false, "message" => "Email already exists or error occurred."]);
// }
// $conn->close();
?>








<?php
// works AGAIN TEST
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Content-Type: application/json");
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Read JSON input
// $data = json_decode(file_get_contents('php://input'), true);

// // Validate input
// if (
//     !isset($data['email']) || 
//     !isset($data['password']) || 
//     !isset($data['name']) || 
//     !isset($data['contact_number']) || 
//     !isset($data['role']) || 
//     !isset($data['address'])
// ) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $email = trim($data['email']);
// $password = trim($data['password']);
// $name = trim($data['name']);
// $contact = trim($data['contact_number']);
// $role = trim($data['role']);
// $address = trim($data['address']);

// // Validate length
// if (strlen($email) < 3 || strlen($password) < 6 || strlen($address) < 3) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input length.']);
//     exit;
// }

// // Connect to DB
// $conn = new mysqli("localhost", "root", "patricioMed", "petalinkKathstone");
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// // Check if email exists
// $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows > 0) {
//     echo json_encode(['success' => false, 'message' => 'Email already exists.']);
//     $stmt->close();
//     $conn->close();
//     exit;
// }
// $stmt->close();

// // Insert user
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// $stmt = $conn->prepare("INSERT INTO users (email, password, name, contact_number, address, role) VALUES (?, ?, ?, ?, ?, ?)");
// $stmt->bind_param("ssssss", $email, $hashedPassword, $name, $contact, $address, $role);

// if ($stmt->execute()) {
//     echo json_encode(['success' => true, 'message' => 'User registered successfully']);
// } else {
//     echo json_encode(['success' => false, 'message' => 'Registration failed']);
// }

// $stmt->close();
// $conn->close();
?>



<?php
// works 7/24/25
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Content-Type: application/json");
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Read JSON input
// $data = json_decode(file_get_contents('php://input'), true);

// // Validate input
// if (
//     !isset($data['email']) || 
//     !isset($data['password']) || 
//     !isset($data['name']) || 
//     !isset($data['contact_number']) || 
//     !isset($data['role']) || 
//     !isset($data['address'])
// ) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $email = trim($data['email']);
// $password = trim($data['password']);
// $name = trim($data['name']);
// $contact = trim($data['contact_number']);
// $role = trim($data['role']);
// $address = trim($data['address']);

// // Validate length
// if (strlen($email) < 3 || strlen($password) < 6 || strlen($address) < 3) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input length.']);
//     exit;
// }

// // Connect to DB
// $conn = new mysqli("localhost", "root", "patricioMed", "petalinkKathstone");
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// // Check if email exists
// $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows > 0) {
//     echo json_encode(['success' => false, 'message' => 'Email already exists.']);
//     $stmt->close();
//     $conn->close();
//     exit;
// }
// $stmt->close();

// // Insert user
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// $stmt = $conn->prepare("INSERT INTO users (email, password, name, contact_number, address, role) VALUES (?, ?, ?, ?, ?, ?)");
// $stmt->bind_param("ssssss", $email, $hashedPassword, $name, $contact, $address, $role);

// if ($stmt->execute()) {
//     echo json_encode(['success' => true, 'message' => 'User registered successfully']);
// } else {
//     echo json_encode(['success' => false, 'message' => 'Registration failed']);
// }

// $stmt->close();
// $conn->close();
?>


<?php
// original code works
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Content-Type: application/json");
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Read raw JSON input
// $data = json_decode(file_get_contents('php://input'), true);

// // Validate input
// if (
//     !isset($data['email']) || 
//     !isset($data['password']) || 
//     !isset($data['name']) || 
//     !isset($data['contact_number']) || 
//     !isset($data['role']) ||
//     !isset($data['address'])
// ) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $email = trim($data['email']);
// $password = trim($data['password']);
// $name = trim($data['name']);
// $contact = trim($data['contact_number']);
// $role = trim($data['role']);
// $address = trim($data['address']);

// // Check input length
// if (strlen($email) < 3 || strlen($password) < 6 || strlen($address) < 3) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input length.']);
//     exit;
// }

// // DB connection
// $conn = new mysqli("localhost", "root", "patricioMed", "petalinkKathstone");
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// // Check for duplicate email
// $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $stmt->store_result();
// if ($stmt->num_rows > 0) {
//     echo json_encode(['success' => false, 'message' => 'Email already exists.']);
//     $stmt->close();
//     $conn->close();
//     exit;
// }
// $stmt->close();

// // Insert new user
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// $stmt = $conn->prepare("INSERT INTO users (email, password, name, contact_number, address, role) VALUES (?, ?, ?, ?, ?, ?)");
// $stmt->bind_param("ssssss", $email, $hashedPassword, $name, $contact, $address, $role);

// if ($stmt->execute()) {
//     echo json_encode(['success' => true]);
// } else {
//     echo json_encode(['success' => false, 'message' => 'Registration failed.']);
// }

// $stmt->close();
// $conn->close();
?>




<?php
// header('Content-Type: application/json');
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Get JSON data from request
// $data = json_decode(file_get_contents('php://input'), true);

// // Validate input
// if (!isset($data['email']) || !isset($data['password'])) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $email = trim($data['email']);
// $password = trim($data['password']);

// // Validate input lengths
// if (strlen($email) < 3 || strlen($password) < 6) {
//     echo json_encode(['success' => false, 'message' => 'Username must be at least 3 chars and password at least 6 chars']);
//     exit;
// }

// // DB credentials (make sure these match your MySQL setup)
// $servername = "localhost";
// $dbname = "oldSchool";
// $dbuser = "root";
// $dbpass = "patricioMed";

// // Connect to MySQL
// $conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// // Check for existing user
// $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows > 0) {
//     echo json_encode(['success' => false, 'message' => 'Username already taken']);
//     $stmt->close();
//     $conn->close();
//     exit;
// }
// $stmt->close();

// // Insert new user
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
// $stmt->bind_param("ss", $email, $hashedPassword);

// if ($stmt->execute()) {
//     echo json_encode(['success' => true]);
// } else {
//     echo json_encode(['success' => false, 'message' => 'Registration failed.']);
// }

// $stmt->close();
// $conn->close();
?>
<?php
// SHOW PASSWORD INPUT **
// header('Content-Type: application/json');
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Get JSON data from request
// $data = json_decode(file_get_contents('php://input'), true);

// // Validate input
// if (!isset($data['username']) || !isset($data['password'])) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $username = trim($data['username']);
// $password = trim($data['password']);

// // Validate input lengths
// if (strlen($username) < 3 || strlen($password) < 6) {
//     echo json_encode(['success' => false, 'message' => 'Username must be at least 3 chars and password at least 6 chars']);
//     exit;
// }

// // DB credentials
// $servername = "localhost";
// $dbname = "oldSchool";
// $dbuser = "root";
// $dbpass = "patricioMed";

// // Connect to MySQL
// $conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// // Check if username already exists
// $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
// $stmt->bind_param("s", $username);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows > 0) {
//     echo json_encode(['success' => false, 'message' => 'Username already taken']);
//     $stmt->close();
//     $conn->close();
//     exit;
// }
// $stmt->close();

// // Insert user with plain text password
// $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
// $stmt->bind_param("ss", $username, $password);

// if ($stmt->execute()) {
//     echo json_encode(['success' => true]);
// } else {
//     echo json_encode(['success' => false, 'message' => 'Registration failed.']);
// }

// $stmt->close();
// $conn->close();
?>

