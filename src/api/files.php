<?php
// Allow CORS for development
header("Access-Control-Allow-Origin: *");

// Get filename from query parameter
$filename = isset($_GET['filename']) ? basename($_GET['filename']) : null;

if ($filename) {
    $file = '/app/uploads/' . $filename;

    // echo getcwd();
    // echo '<br>';
    // echo $file;
    // $files = scandir('.');
    // foreach ($files as $file) {
    //     echo $file . "<br>";
    // }
    // die();
    if (file_exists(filename: $file)) {
        // echo "File is not Empty <br>";
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        http_response_code(404);
        echo "File not found!";
    }
} else {
    http_response_code(400);
    echo "No filename specified!";
}
