<?php
$servername = "127.0.0.1:3309";
$username = "root";
$password = "";
$dbname = "kab_booking_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
