<?php
header('Content-Type: application/json');

// Include database connection
require_once 'db.php';

// Task class to structure the response
class Task
{
    public $id;
    public $taskTitle;
    public $taskType;
    public $taskPriority;
    public $taskStatus;
    public $taskStartDate;
    public $taskEndDate;
    public $projectId;
    public $projectName;
    public $taskAssignedTo;
    public $taskAssignedToName;
    public $taskCreatedBy;
    public $taskCreatedByName;
    public $taskDescription;
    public $file;
}

try {
    // Check if database connection exists
    if (!isset($conn) || $conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }

    // Get parameters from GET request
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $taskAssignedTo = isset($_GET['taskAssignedTo']) ? intval($_GET['taskAssignedTo']) : null;

    // Build the SQL query
    $sql = "SELECT 
                tm_task_id,
                tm_task_title,
                tm_task_description,
                tm_task_type,
                tm_task_priority,
                tm_task_status,
                tm_task_start_date,
                tm_task_end_date,
                tm_task_creation_datetime,
                tm_task_created_by_id,
                tm_task_created_by_name,
                tm_task_assigned_to_id,
                tm_task_assigned_to_name,
                tm_task_project_id,
                tm_task_project_name,
                tm_document
            FROM task WHERE 1=1";

    $params = [];
    $types = "";

    // Add conditions based on parameters
    if ($id !== null) {
        $sql .= " AND tm_task_id = ?";
        $params[] = $id;
        $types .= "i";
    }

    if ($taskAssignedTo !== null) {
        $sql .= " AND tm_task_assigned_to_id = ?";
        $params[] = $taskAssignedTo;
        $types .= "i";
    }

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare statement: ' . $conn->error]);
        exit;
    }

    // Bind parameters if any exist
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to execute query: ' . $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();

    if ($result === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to get result: ' . $conn->error]);
        exit;
    }

    $tasks = [];

    while ($row = $result->fetch_assoc()) {
        $task = new Task();
        $task->id = (int)$row['tm_task_id'];
        $task->taskTitle = $row['tm_task_title'];
        $task->taskType = $row['tm_task_type'];
        $task->taskPriority = $row['tm_task_priority'];
        $task->taskStatus = $row['tm_task_status'];
        $task->taskStartDate = $row['tm_task_start_date'];
        $task->taskEndDate = $row['tm_task_end_date'];
        $task->projectId = $row['tm_task_project_id'] ? (int)$row['tm_task_project_id'] : null;
        $task->projectName = $row['tm_task_project_name'];
        $task->taskAssignedTo = $row['tm_task_assigned_to_id'] ? (int)$row['tm_task_assigned_to_id'] : null;
        $task->taskAssignedToName = $row['tm_task_assigned_to_name'];
        $task->taskCreatedBy = $row['tm_task_created_by_id'] ? (int)$row['tm_task_created_by_id'] : null;
        $task->taskCreatedByName = $row['tm_task_created_by_name'];
        $task->taskDescription = $row['tm_task_description'];

        // Handle file - if tm_document has a value, create a file object, otherwise null
        if (!empty($row['tm_document'])) {
            $task->file = [
                'filename' => $row['tm_document'],
                'url' => '/app/uploads/' . $row['tm_document']
            ];
        } else {
            $task->file = null;
        }

        $tasks[] = $task;
    }

    // Return response based on whether ID was specified
    if ($id !== null) {
        if (empty($tasks)) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
        } else {
            http_response_code(200);
            echo json_encode($tasks[0]); // Return single task
        }
    } else {
        http_response_code(200);
        echo json_encode($tasks); // Return array of tasks
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Fatal error: ' . $e->getMessage()]);
} finally {
    // Close database connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
