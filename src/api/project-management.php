<?php
header("Content-Type: application/json");
require_once 'db.php'; // mysqli connection in $conn

$method = $_SERVER['REQUEST_METHOD'];

// Read and decode the JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Function to respond with JSON
function respond($status, $message) {
    http_response_code($status);
    echo json_encode(["message" => $message]);
    exit;
}

switch ($method) {
    case 'POST':
        // Required fields for POST
        $requiredFields = [
            'projectName', 'projectStartDate', 'projectEndDate',
            'projectDescription', 'projectCreatedBy', 'projectAssignedTo'
        ];

        // Check for missing fields
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                respond(400, "Missing required field: $field");
            }
        }

        // Prepare and bind insert query
        $stmt = $conn->prepare("
            INSERT INTO project (
                project_name, project_start_date, project_end_date,
                project_description, project_created_by, project_assigned_to
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssii",
            $input['projectName'],
            $input['projectStartDate'],
            $input['projectEndDate'],
            $input['projectDescription'],
            $input['projectCreatedBy'],
            $input['projectAssignedTo']
        );

        if ($stmt->execute()) {
            respond(201, "Project created successfully");
        } else {
            respond(500, "Error creating project: " . $stmt->error);
        }

        break;

    case 'PUT':
        // Project ID is required for update
        if (!isset($input['id']) || !is_numeric($input['id'])) {
            respond(400, "Project ID is required for update");
        }

        $projectId = intval($input['id']);
        unset($input['id']); // Remove ID from fields to update

        // Mapping from JSON keys to DB fields
        $fieldMap = [
            'projectName' => 'project_name',
            'projectStartDate' => 'project_start_date',
            'projectEndDate' => 'project_end_date',
            'projectDescription' => 'project_description',
            'projectCreatedBy' => 'project_created_by',
            'projectAssignedTo' => 'project_assigned_to'
        ];

        // Build dynamic query
        $fields = [];
        $params = [];
        $types = '';

        foreach ($fieldMap as $jsonKey => $dbField) {
            if (isset($input[$jsonKey])) {
                $fields[] = "$dbField = ?";
                $params[] = $input[$jsonKey];
                $types .= is_int($input[$jsonKey]) ? 'i' : 's';
            }
        }

        if (empty($fields)) {
            respond(400, "No fields to update");
        }

        $sql = "UPDATE project SET " . implode(', ', $fields) . " WHERE project_id = ?";
        $types .= 'i';
        $params[] = $projectId;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            respond(200, "Project updated successfully");
        } else {
            respond(500, "Error updating project: " . $stmt->error);
        }

        break;

    default:
        respond(405, "Method Not Allowed");
}

$conn->close();
