<?php
header(header: "Content-Type: application/json");

include 'db.php';

class User {
    public string $firstName;
    public string $lastName;
    public string $emailAddress;
    public string $contactNumber;
    public string $userRole;
}

$sql = "SELECT id, first_name, last_name, email_address, contact_number, user_role FROM user_registration";
$result = $conn->query($sql);

$users = [];

while ($row = $result->fetch_assoc()) {
    $user = new User();
    $user->firstName = $row['first_name'];
    $user->lastName = $row['last_name'];
    $user->emailAddress = $row['email_address'];
    $user->contactNumber = $row['contact_number'];
    $user->userRole = $row['user_role'];
    $users[] = $user;
}

echo json_encode($users);
$conn->close();
?>
