<?php
$conn = new mysqli("localhost", "root", "patricioMed", "oldSchool");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
