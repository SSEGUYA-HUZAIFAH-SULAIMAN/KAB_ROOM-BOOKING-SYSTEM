<?php

session_start();
include 'db_config.php';

header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? ''; 

if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit();
}

$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = ? AND role = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

   // if ($user['password'] === $password) {
      if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        echo json_encode(['success' => true, 'role' => $user['role'], 'userId' => $user['username']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect Username/Password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found/role is incorrect.']);
}

$stmt->close();
$conn->close();

?>
