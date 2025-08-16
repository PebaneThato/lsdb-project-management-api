<?php
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed. Only POST requests are accepted.'
        ]);
        exit;
    }

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON decode fails, try to get from $_POST
    if ($input === null) {
        $input = $_POST;
    }

    // Validate required fields (all except commentId)
    $requiredFields = [
        'commentContent',
        'commentAddedById', 
        'commentAddedByName',
        'taskId',
        'commentDateTime'
    ];

    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || trim($input[$field]) === '') {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missingFields)
        ]);
        exit;
    }

    // Additional validation
    if (!is_numeric($input['commentAddedById'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'commentAddedById must be a valid number'
        ]);
        exit;
    }

    if (!is_numeric($input['taskId'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'taskId must be a valid number'
        ]);
        exit;
    }

    // Validate datetime format (assuming MySQL datetime format: YYYY-MM-DD HH:MM:SS)
    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $input['commentDateTime']);
    if (!$datetime || $datetime->format('Y-m-d H:i:s') !== $input['commentDateTime']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'commentDateTime must be in valid format (YYYY-MM-DD HH:MM:SS)'
        ]);
        exit;
    }

    // Prepare SQL statement
    $sql = "INSERT INTO comment (comment_content, comment_added_by_id, comment_added_by_name, task_id, comment_datetime) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to prepare SQL statement: ' . $conn->error
        ]);
        exit;
    }

    // Bind parameters
    $stmt->bind_param(
        "sisss", 
        $input['commentContent'],
        $input['commentAddedById'],
        $input['commentAddedByName'],
        $input['taskId'],
        $input['commentDateTime']
    );

    // Execute the statement
    if ($stmt->execute()) {
        $insertedId = $conn->insert_id;
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Comment saved successfully',
            'data' => [
                'commentId' => $insertedId,
                'commentContent' => $input['commentContent'],
                'commentAddedById' => $input['commentAddedById'],
                'commentAddedByName' => $input['commentAddedByName'],
                'taskId' => $input['taskId'],
                'commentDateTime' => $input['commentDateTime']
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save comment: ' . $stmt->error
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
} finally {
    // Close database connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>