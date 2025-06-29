<?php
header("Content-Type: application/json");

require_once 'db.php';

class User
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public string $emailAddress;
    public string $contactNumber;
    public string $userRole;
}

function fetchUsers(mysqli $conn, ?int $id = null): array|User|null {
    $sql = "SELECT id, first_name, last_name, email_address, contact_number, user_role FROM user";
    
    if ($id !== null) {
        $sql .= " WHERE id = ? LIMIT 1";
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare SQL statement.']);
        return null;
    }

    if ($id !== null) {
        $stmt->bind_param("i", $id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to execute query.']);
        return null;
    }

    if ($id !== null) {
        $row = $result->fetch_assoc();
        if (!$row) {
            return null;
        }
        return mapToUser($row);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = mapToUser($row);
    }

    return $users;
}

function mapToUser(array $row): User {
    $user = new User();
    $user->id = (int) $row['id'];
    $user->firstName = $row['first_name'];
    $user->lastName = $row['last_name'];
    $user->emailAddress = $row['email_address'];
    $user->contactNumber = $row['contact_number'];
    $user->userRole = $row['user_role'];
    return $user;
}

// Get ID from request if available and numeric
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;

// Fetch users
$data = fetchUsers($conn, $id);

// Output result
if ($data === null) {
    http_response_code(404);
    echo json_encode(['message' => 'User not found or error occurred.']);
} else {
    echo json_encode($data);
}

$conn->close();
