<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "root";
$pass = "patricioMed";
$dbname = "project_petalink";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Get form data
$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$contact  = $_POST['contact'] ?? '';
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'customer';
$shop_name= $_POST['shop_name'] ?? null;
$address  = $_POST['address'] ?? null;

// Insert into users table
$stmt = $conn->prepare("INSERT INTO users (name, email, contact, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $contact, $password, $role);
if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "User registration failed"]);
    exit();
}
$user_id = $stmt->insert_id;
$stmt->close();

// If role = owner, insert into flowershopOwners
if ($role === "owner" && $shop_name && $address) {

    // Step 1: Get coordinates from OpenStreetMap Nominatim API
    $encodedAddress = urlencode($address);
    $nominatimUrl = "https://nominatim.openstreetmap.org/search?format=json&q=$encodedAddress";

    $opts = [
        "http" => [
            "header" => "User-Agent: PetaLinkApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $geoResponse = file_get_contents($nominatimUrl, false, $context);
    $geoData = json_decode($geoResponse, true);

    // Step 2: Assign coordinates (float conversion to avoid 0.0 issue)
    if (!empty($geoData) && isset($geoData[0])) {
        $latitude = (float)$geoData[0]["lat"];
        $longitude = (float)$geoData[0]["lon"];
    } else {
        $latitude = null;
        $longitude = null;
    }

    // Step 3: Insert into flowershopOwners
    $stmt2 = $conn->prepare("
        INSERT INTO flowershopOwners (user_id, shop_name, name, email, contact_number, address, latitude, longitude)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            shop_name = VALUES(shop_name), 
            name = VALUES(name), 
            email = VALUES(email), 
            contact_number = VALUES(contact_number), 
            address = VALUES(address), 
            latitude = VALUES(latitude), 
            longitude = VALUES(longitude)
    ");
    $stmt2->bind_param("isssssdd", $user_id, $shop_name, $name, $email, $contact, $address, $latitude, $longitude);

    if (!$stmt2->execute()) {
        echo json_encode(["status" => "error", "message" => "Owner registration failed"]);
        exit();
    }
    $stmt2->close();
}

$conn->close();

echo json_encode(["status" => "success", "message" => "Registration successful"]);
?>
