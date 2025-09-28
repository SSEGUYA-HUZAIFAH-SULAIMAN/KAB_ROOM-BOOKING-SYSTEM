<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_POST['action'] ?? '';
$data = $_POST['data'] ?? [];

switch ($action) {
    case 'add_building':
        $name = $data['name'] ?? '';
        if ($name) {
            $stmt = $conn->prepare("INSERT INTO buildings (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Building added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding building.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Building name is required.']);
        }
        break;

    case 'add_room':
        $buildingId = $data['building_id'] ?? '';
        $roomName = $data['room_name'] ?? '';
        if ($buildingId && $roomName) {
            $stmt = $conn->prepare("INSERT INTO rooms (building_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $buildingId, $roomName);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Room added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding room.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Building and room name are required.']);
        }
        break;

       case 'delete_building':
        $buildingId = $data['building_id'] ?? '';
        if ($buildingId) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("DELETE FROM bookings WHERE room_id IN (SELECT id FROM rooms WHERE building_id = ?)");
                $stmt->bind_param("i", $buildingId);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM rooms WHERE building_id = ?");
                $stmt->bind_param("i", $buildingId);
                $stmt->execute();

                $stmt = $conn->prepare("DELETE FROM buildings WHERE id = ?");
                $stmt->bind_param("i", $buildingId);
                $stmt->execute();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Building and all its rooms deleted successfully.']);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Error deleting building: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Building ID is required.']);
        }
        break;

    case 'delete_room':
        $roomId = $data['room_id'] ?? '';
        if ($roomId) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("DELETE FROM bookings WHERE room_id = ?");
                $stmt->bind_param("i", $roomId);
                $stmt->execute();

                $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
                $stmt->bind_param("i", $roomId);
                $stmt->execute();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Room and its bookings deleted successfully.']);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Error deleting room: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Room ID is required.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>
