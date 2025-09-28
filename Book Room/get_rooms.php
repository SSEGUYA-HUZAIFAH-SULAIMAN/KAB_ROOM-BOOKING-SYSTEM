<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

$buildingId = $_GET['building_id'] ?? null;

if (!$buildingId) {
    echo json_encode(['error' => 'Building ID is required.']);
    exit();
}

$rooms_sql = "SELECT * FROM rooms WHERE building_id = ?";
$rooms_stmt = $conn->prepare($rooms_sql);
$rooms_stmt->bind_param("i", $buildingId);
$rooms_stmt->execute();
$rooms_result = $rooms_stmt->get_result();

$rooms = [];
if ($rooms_result->num_rows > 0) {
    while($room_row = $rooms_result->fetch_assoc()) {

        $bookings_sql = "SELECT * FROM bookings WHERE room_id = ?";
        $bookings_stmt = $conn->prepare($bookings_sql);
        $bookings_stmt->bind_param("i", $room_row['id']);
        $bookings_stmt->execute();
        $bookings_result = $bookings_stmt->get_result();

        $bookings = [];
        while ($booking_row = $bookings_result->fetch_assoc()) {
            $bookings[] = $booking_row;
        }

        $room_row['bookings'] = $bookings;
        $rooms[] = $room_row;
    }
}

echo json_encode($rooms);

$conn->close();
?>
