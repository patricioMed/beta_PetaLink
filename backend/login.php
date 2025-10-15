<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$email = $data['email'];
$password = $data['password'];
$servername = "localhost";
$dbname = "project_petalink";
$dbuser = "root";
$dbpass = "patricioMed";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($user_id, $hashedPassword, $role);
    $stmt->fetch();

    if (password_verify($password, $hashedPassword)) {
        // Default session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;

        if ($role === 'owner') {
            // ✅ Check owner status
            $ownerStmt = $conn->prepare("SELECT owner_id, status FROM flowershopowners WHERE user_id = ?");
            $ownerStmt->bind_param("i", $user_id);
            $ownerStmt->execute();
            $ownerStmt->store_result();

            if ($ownerStmt->num_rows === 1) {
                $ownerStmt->bind_result($owner_id, $status);
                $ownerStmt->fetch();

                if ($status !== 'approved') {
                    echo json_encode([
                        'success' => false,
                        'message' => "Your account is not approved yet. Current status: $status"
                    ]);
                    $ownerStmt->close();
                    $stmt->close();
                    $conn->close();
                    exit;
                }

                $_SESSION['owner_id'] = $owner_id;
            }
            $ownerStmt->close();
        }

        echo json_encode([
            'success' => true,
            'role' => $role,
            'owner_id' => $_SESSION['owner_id'] ?? null,
            'message' => 'Login successful'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?>

<?php
// working but not checking if status is approve
// session_start();
// header('Content-Type: application/json');

// $data = json_decode(file_get_contents('php://input'), true);

// if (!isset($data['email']) || !isset($data['password'])) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $email = $data['email'];
// $password = $data['password'];
// $servername = "localhost";
// $dbname = "petalinkKathstone";
// $dbuser = "root";
// $dbpass = "patricioMed";

// $conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows === 1) {
//     $stmt->bind_result($user_id, $hashedPassword, $role);
//     $stmt->fetch();

//     if (password_verify($password, $hashedPassword)) {
//         $_SESSION['user_id'] = $user_id;
//         $_SESSION['role'] = $role;

//         $ownerStmt = $conn->prepare("SELECT owner_id FROM flowershopOwners WHERE user_id = ?");
//         $ownerStmt->bind_param("i", $user_id);
//         $ownerStmt->execute();
//         $ownerStmt->store_result();

//         if ($ownerStmt->num_rows === 1) {
//             $ownerStmt->bind_result($owner_id);
//             $ownerStmt->fetch();
//             $_SESSION['owner_id'] = $owner_id;
//         }

//         echo json_encode([
//             'success' => true,
//             'role' => $role,
//             'owner_id' => $_SESSION['owner_id'] ?? null,
//             'message' => 'Login successful'
//         ]);
//     } else {
//         echo json_encode(['success' => false, 'message' => 'Incorrect password']);
//     }
// } else {
//     echo json_encode(['success' => false, 'message' => 'User not found']);
// }

// $stmt->close();
// $conn->close();
?>


<?php
// originak working code
// session_start();
// header('Content-Type: application/json');

// $data = json_decode(file_get_contents('php://input'), true);

// if (!isset($data['email']) || !isset($data['password'])) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $email = $data['email'];
// $password = $data['password'];

// $servername = "localhost";
// $dbname = "petalinkKathstone";
// $dbuser = "root";
// $dbpass = "patricioMed";

// $conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// // Get user id, password and role
// $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows === 1) {
//     $stmt->bind_result($user_id, $hashedPassword, $role);
//     $stmt->fetch();

//     if (password_verify($password, $hashedPassword)) {
//         $_SESSION['user_id'] = $user_id;
//         $_SESSION['role'] = $role;

//         echo json_encode([
//             'success' => true,
//             'role' => $role, 
//             'message' => 'Login successful'
//         ]);
//     } else {
//         echo json_encode(['success' => false, 'message' => 'Incorrect password']);
//     }
// } else {
//     echo json_encode(['success' => false, 'message' => 'User not found']);
// }

// $stmt->close();
// $conn->close();
?>





<?php
// // login.php
// header('Content-Type: application/json');
// $data = json_decode(file_get_contents('php://input'), true);

// if (!isset($data['username']) || !isset($data['password'])) {
//     echo json_encode(['success' => false, 'message' => 'Invalid input']);
//     exit;
// }

// $username = $data['username'];
// $password = $data['password'];

// // DB connection (edit with your credentials)
// $servername = "localhost";
// $dbname = "oldSchool";
// $dbuser = "root";
// $dbpass = "patricioMed";

// $conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
// if ($conn->connect_error) {
//     echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//     exit;
// }

// // Prevent SQL Injection using prepared statements
// $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
// $stmt->bind_param("s", $username);
// $stmt->execute();
// $stmt->store_result();

// if ($stmt->num_rows === 1) {
//     $stmt->bind_result($hashedPassword);
//     $stmt->fetch();

//     // Verify password (assuming passwords are hashed with password_hash)
//     if (password_verify($password, $hashedPassword)) {
//         echo json_encode(['success' => true]);
//     } else {
//         echo json_encode(['success' => false, 'message' => 'Incorrect password']);
//     }
// } else {
//     echo json_encode(['success' => false, 'message' => 'User not found']);
// }

// $stmt->close();
// $conn->close();
?>
<?php
// session_start();
// $conn = new mysqli("localhost", "root", "patricioMed", "oldSchool");

// $username = $_POST['username'];
// $password = $_POST['password'];

// $sql = "SELECT id, password FROM users WHERE username = ?";
// $stmt = $conn->prepare($sql);
// $stmt->bind_param("s", $username);
// $stmt->execute();
// $result = $stmt->get_result();

// if ($row = $result->fetch_assoc()) {
//   if (password_verify($password, $row['password'])) {
//     $_SESSION['user_id'] = $row['id'];
//     $_SESSION['username'] = $username;
//     header("Location: dashboard.php");
//     exit();
//   } else {
//     echo "Invalid credentials.";
//   }
// } else {
//   echo "User not found.";
// }
?>

