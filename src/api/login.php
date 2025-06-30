<?php
header("Content-Type: application/json");
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Email and password are required"], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Query to find user by email
$sql = "SELECT * FROM user WHERE email_address = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(["message" => "User not found"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate password (use password_hash for storing, but this is an example)
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid password"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Generate response with user object, excluding password
unset($user['password']);
echo json_encode(["user" => $user], JSON_UNESCAPED_UNICODE);
http_response_code(200);

$conn->close();
?>
