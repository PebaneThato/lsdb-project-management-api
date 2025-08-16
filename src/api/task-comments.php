<?php
// Set content type to JSON
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'error' => 'Method not allowed',
        'message' => 'Only GET requests are supported'
    ]);
    exit;
}

try {
    // Include database connection
    require_once 'db.php';
    
    // Validate taskId parameter
    if (!isset($_GET['taskId']) || empty(trim($_GET['taskId']))) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'error' => 'Missing parameter',
            'message' => 'taskId parameter is required and cannot be empty'
        ]);
        exit;
    }
    
    $taskId = trim($_GET['taskId']);
    
    // Validate that task_id is numeric (assuming it's an integer)
    if (!is_numeric($taskId)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid parameter',
            'message' => 'task_id must be a valid number'
        ]);
        exit;
    }
    
    // Prepare SQL query to fetch comments for the given task_id
    $sql = "SELECT 
                comment_id,
                comment_content,
                comment_added_by_id,
                comment_added_by_name,
                task_id,
                comment_datetime
            FROM comment 
            WHERE task_id = ? 
            ORDER BY comment_datetime DESC";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    // Bind parameter (assuming task_id is integer)
    $stmt->bind_param("i", $taskId);
    
    // Execute query
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    // Get result
    $result = $stmt->get_result();
    
    // Check if any comments found
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'error' => 'No data found',
            'message' => 'No comments found for the specified task_id',
            'task_id' => $taskId
        ]);
        exit;
    }
    
    // Fetch all comments and map database fields to interface fields
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'commentId' => $row['comment_id'],
            'commentContent' => $row['comment_content'],
            'commentAddedById' => $row['comment_added_by_id'],
            'commentAddedByName' => $row['comment_added_by_name'],
            'taskId' => $row['task_id'],
            'commentDatetime' => $row['comment_datetime']
        ];
    }
    
    // Close statement
    $stmt->close();
    
    // Return successful response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Comments retrieved successfully',
        'task_id' => $taskId,
        'count' => count($comments),
        'data' => $comments
    ]);
    
} catch (mysqli_sql_exception $e) {
    // Database specific errors
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => 'An error occurred while accessing the database'
    ]);
    
} catch (Exception $e) {
    // General errors
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => 'An unexpected error occurred'
    ]);
    
} finally {
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>