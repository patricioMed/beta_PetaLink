<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id  = $_SESSION['user_id'];
$owner_id = $_SESSION['owner_id'] ?? null;

if (!$owner_id) {
    die("No owner account found for this verification.");
}

// Database connection
$conn = new mysqli("localhost", "root", "patricioMed", "project_petalink");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// User-specific upload directory
$userDir = "../frontend/verification/" . $user_id . "/"; 
if (!file_exists($userDir)) {
    mkdir($userDir, 0777, true);
}

// Allowed file extensions
$allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];

// Fields in your form
$fields = [
    "dti_registration",
    "barangay_clearance",
    "business_permit",
    "bir_certificate",
    "fire_safety",
    "sanitary_permit",
    "zoning_clearance",
    "sss_philhealth_pagibig"
];

$filePaths = [];

// Handle file uploads
foreach ($fields as $field) {
    $filePaths[$field] = null;

    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES[$field]['tmp_name'];
        $fileName = basename($_FILES[$field]['name']);
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedTypes)) {
            $newFileName = $field . "_" . time() . "." . $fileExt;
            $targetPath = $userDir . $newFileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $filePaths[$field] = $targetPath;
            }
        }
    }
}

// Insert into verification table including owner_id
$sql = "INSERT INTO verification (
            user_id, owner_id, dti_registration, barangay_clearance, business_permit, bir_certificate,
            fire_safety, sanitary_permit, zoning_clearance, sss_philhealth_pagibig, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iissssssss",
    $user_id,
    $owner_id,
    $filePaths["dti_registration"],
    $filePaths["barangay_clearance"],
    $filePaths["business_permit"],
    $filePaths["bir_certificate"],
    $filePaths["fire_safety"],
    $filePaths["sanitary_permit"],
    $filePaths["zoning_clearance"],
    $filePaths["sss_philhealth_pagibig"]
);

if ($stmt->execute()) {
    // Clear owner_id from session after successful use
    unset($_SESSION['owner_id']);

    // echo "<script>alert('Verification documents submitted successfully!'); window.location.href='accountUpgrade.php';</script>";
    echo "Verification documents submitted successfully!";
    
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

<?php
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.html");
//     exit();
// }

// $user_id = $_SESSION['user_id'];

// // Database connection
// $conn = new mysqli("localhost", "root", "patricioMed", "petalinkKathstone");
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// // User-specific upload directory
// $userDir = "../frontend/verification/" . $user_id . "/"; 
// if (!file_exists($userDir)) {
//     mkdir($userDir, 0777, true);
// }

// // Allowed file extensions
// $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];

// // Fields in your form
// $fields = [
//     "dti_registration",
//     "barangay_clearance",
//     "business_permit",
//     "bir_certificate",
//     "fire_safety",
//     "sanitary_permit",
//     "zoning_clearance",
//     "sss_philhealth_pagibig"
// ];

// $filePaths = [];

// // Handle file uploads
// foreach ($fields as $field) {
//     $filePaths[$field] = null;

//     if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
//         $fileTmp  = $_FILES[$field]['tmp_name'];
//         $fileName = basename($_FILES[$field]['name']);
//         $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

//         if (in_array($fileExt, $allowedTypes)) {
//             $newFileName = $field . "_" . time() . "." . $fileExt;
//             $targetPath = $userDir . $newFileName;

//             if (move_uploaded_file($fileTmp, $targetPath)) {
//                 $filePaths[$field] = $targetPath;
//             }
//         }
//     }
// }

// // Insert into verification table
// $sql = "INSERT INTO verification (
//             user_id, dti_registration, barangay_clearance, business_permit, bir_certificate,
//             fire_safety, sanitary_permit, zoning_clearance, sss_philhealth_pagibig, status
//         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

// $stmt = $conn->prepare($sql);
// $stmt->bind_param(
//     "issssssss",
//     $user_id,
//     $filePaths["dti_registration"],
//     $filePaths["barangay_clearance"],
//     $filePaths["business_permit"],
//     $filePaths["bir_certificate"],
//     $filePaths["fire_safety"],
//     $filePaths["sanitary_permit"],
//     $filePaths["zoning_clearance"],
//     $filePaths["sss_philhealth_pagibig"]
// );

// if ($stmt->execute()) {
//     echo "<script>alert('Verification documents submitted successfully!'); window.location.href='accountUpgraded.php';</script>";
// } else {
//     echo "Error: " . $stmt->error;
// }

// $stmt->close();
// $conn->close();
?>
