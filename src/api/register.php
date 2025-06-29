<?php
header("Content-Type: application/json");
require_once 'db.php'; // Include your DB connection

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

function respond($message, $status = 200) {
    http_response_code($status);
    echo json_encode(['message' => $message]);
    exit;
}

if (!is_array($data)) {
    respond("Invalid JSON format", 400);
}

switch ($method) {
    case 'POST':
        $requiredFields = ['firstName', 'lastName', 'emailAddress', 'password', 'contactNumber', 'userRole'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                respond("Field '$field' is required.", 400);
            }
        }

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['emailAddress'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT); // Encrypt password
        $contact = $data['contactNumber'];
        $role = $data['userRole'];

        $sql = "INSERT INTO user (first_name, last_name, email_address, password, contact_number, user_role)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            respond("Database error: " . $conn->error, 500);
        }

        $stmt->bind_param("ssssss", $firstName, $lastName, $email, $password, $contact, $role);

        if ($stmt->execute()) {
            respond("User created successfully", 201);
        } else {
            respond("Failed to create user: " . $stmt->error, 500);
        }

        break;

    case 'PUT':
        if (empty($data['id'])) {
            respond("Field 'id' is required for update.", 400);
        }

        $id = $data['id'];
        $fields = ['firstName', 'lastName', 'emailAddress', 'password', 'contactNumber', 'userRole'];
        $updates = [];
        $params = [];
        $types = "";

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $dbField = match ($field) {
                    'firstName' => 'first_name',
                    'lastName' => 'last_name',
                    'emailAddress' => 'email_address',
                    'password' => 'password',
                    'contactNumber' => 'contact_number',
                    'userRole' => 'user_role',
                };

                $updates[] = "$dbField = ?";
                $value = $field === 'password' ? password_hash($data[$field], PASSWORD_DEFAULT) : $data[$field];
                $params[] = $value;
                $types .= "s";
            }
        }

        if (empty($updates)) {
            respond("No fields to update.", 400);
        }

        $sql = "UPDATE user SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            respond("Database error: " . $conn->error, 500);
        }

        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                respond("User updated successfully", 200);
            } else {
                respond("No changes made or user not found.", 404);
            }
        } else {
            respond("Failed to update user: " . $stmt->error, 500);
        }

        break;

    default:
        respond("Unsupported HTTP method", 405);
}
?>
