<?php
header("Content-Type: application/json");
require_once 'db.php';

try {
    $stmt = $conn->prepare("
        INSERT INTO task (
            tm_task_title, tm_task_description, tm_task_type, tm_task_priority,
            tm_task_status, tm_task_start_date, tm_task_end_date,
            tm_task_created_by_id, tm_task_created_by_name,
            tm_task_assigned_to_id, tm_task_assigned_to_name,
            tm_task_project_id, tm_task_project_name, tm_document
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $filename = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '/app/uploads/';
        $filename = basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename);
    }

    $stmt->bind_param(
        "sssssssisisiss",
        $_POST['taskTitle'],
        $_POST['taskDescription'],
        $_POST['taskType'],
        $_POST['taskPriority'],
        $_POST['taskStatus'],
        $_POST['taskstartDate'],
        $_POST['taskEndDate'],
        $_POST['taskCreatedBy'],
        $_POST['taskCreatedByName'],
        $_POST['taskAssignedTo'],
        $_POST['taskAssignedToName'],
        $_POST['projectId'],
        $_POST['projectName'],
        $filename
    );

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Task created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to save task."]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}