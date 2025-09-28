<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

$sql = "SELECT * FROM buildings";
$result = $conn->query($sql);

$buildings = [];
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $buildings[] = $row;
  }
}

echo json_encode($buildings);

$conn->close();
?>
