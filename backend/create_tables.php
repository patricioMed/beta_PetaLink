<?php
$host = 'localhost';
$user = 'root';
$password = 'patricioMed';
$database = 'project_petalink';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table
$conn->query("
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `contact_number` VARCHAR(20) NOT NULL,
  `address` VARCHAR(255),
  `role` ENUM('admin', 'owner', 'customer') NOT NULL DEFAULT 'customer',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);
");

// Create flowershopOwners table
$conn->query("
CREATE TABLE IF NOT EXISTS `flowershopOwners` (
  `owner_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `contact_number` VARCHAR(20) NOT NULL,
  `address` TEXT,
  `verified` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
");

// Create trigger (note: triggers cannot be created with multi_query + DELIMITER)
$triggerSQL = "
CREATE TRIGGER insert_flowershop_owner
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
  IF NEW.role = 'owner' THEN
    INSERT INTO flowershopOwners (`user_id`, `name`, `email`, `contact_number`, `address`)
    VALUES (NEW.id, NEW.name, NEW.email, NEW.contact_number, NEW.address);
  END IF;
END;
";

// Run trigger creation
if ($conn->query($triggerSQL) === TRUE) {
    echo "Trigger created successfully.";
} else {
    echo "Trigger creation failed: " . $conn->error;
}

$conn->close();
?>
