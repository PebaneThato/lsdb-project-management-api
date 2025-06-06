<?php
header("Content-Type: application/json");
include 'db.php';

$sql = "SELECT id, first_name, last_name, email_address, contact_number, user_role FROM user_registration";
$result = $conn->query($sql);

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
$conn->close();
?>
