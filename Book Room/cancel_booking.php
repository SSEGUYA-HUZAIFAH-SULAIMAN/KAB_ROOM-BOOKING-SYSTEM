<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required.']);
    exit();
}

$roomId = $_POST['room_id'] ?? '';
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM bookings WHERE room_id = ? AND user_id = ?");
$stmt->bind_param("is", $roomId, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Booking canceled successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching booking found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error canceling booking.']);
}

$conn->close();
?>
