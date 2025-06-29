<?php
header("Content-Type: application/json");

require_once 'db.php';

class Project
{
    public int $id;
    public string $projectName;
    public string $projectStartDate;
    public string $projectEndDate;
    public string $projectDescription;
    public int $projectCreatedBy;
    public int $projectAssignedTo;
}

function fetchProjects(mysqli $conn, ?int $id = null): array|Project|null {
    $sql = "SELECT project_id, project_name, project_start_date, project_end_date, project_description, project_created_by, project_assigned_to FROM project";
    
    if ($id !== null) {
        $sql .= " WHERE project_id = ? LIMIT 1";
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
        return mapToProject($row);
    }

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = mapToProject($row);
    }

    return $projects;
}

function mapToProject(array $row): Project {
    $project = new Project();
    $project->id = (int) $row['project_id'];
    $project->projectName = $row['project_name'];
    $project->projectStartDate = $row['project_start_date'];
    $project->projectEndDate = $row['project_end_date'];
    $project->projectDescription = $row['project_description'];
    $project->projectCreatedBy = $row['project_created_by'];
    $project->projectAssignedTo = $row['project_assigned_to'];
    return $project;
}

// Get ID from request if available and numeric
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;

// Fetch projects
$data = fetchProjects($conn, $id);

// Output result
if ($data === null) {
    http_response_code(404);
    echo json_encode(['message' => 'Project not found or error occurred.']);
} else {
    echo json_encode($data);
}

$conn->close();
