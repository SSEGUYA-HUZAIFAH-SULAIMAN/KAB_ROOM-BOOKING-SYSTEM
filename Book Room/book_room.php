<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required.']);
    exit();
}

$roomId = $_POST['room_id'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$purpose = $_POST['purpose'] ?? '';
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];


if ($userRole === 'coordinator') {
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT room_id) AS booked_rooms FROM bookings WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['booked_rooms'] >= 3) {
        echo json_encode(['success' => false, 'message' => 'Coordinators can book a maximum of 3 rooms.']);
        exit();
    }
}

// Check for existing bookings to prevent conflicts
$stmt = $conn->prepare("SELECT * FROM bookings WHERE room_id = ? AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))");
$stmt->bind_param("issss", $roomId, $startTime, $startTime, $endTime, $endTime);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This room is already booked for the specified time.']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO bookings (room_id, user_id, start_time, end_time, purpose) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $roomId, $userId, $startTime, $endTime, $purpose);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Room booked successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error booking room.']);
}

$conn->close();
?>
