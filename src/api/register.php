<?php
header("Content-Type: application/json");
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "Invalid input"]);
    http_response_code(400);
    exit;
}

$firstName = $data['firstName'] ?? '';
$lastName = $data['lastName'] ?? '';
$email = $data['emailAddress'] ?? '';
$password = password_hash($data['password'] ?? '', PASSWORD_DEFAULT);
$contact = $data['contactNumber'] ?? '';
$role = $data['userRole'] ?? '';

if (!$firstName || !$lastName || !$email || !$contact || !$role) {
    echo json_encode(["error" => "Required fields are missing."]);
    http_response_code(400);
    exit;
}

$sql = "INSERT INTO user_registration (first_name, last_name, email_address, password, contact_number, user_role)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $firstName, $lastName, $email, $password, $contact, $role);

if ($stmt->execute()) {
    echo json_encode(["message" => "User registered successfully"]);
} else {
    echo json_encode(["error" => $stmt->error]);
    http_response_code(500);
}

$stmt->close();
$conn->close();
?>
