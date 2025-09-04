<?php
// api/init.php
// Suppress PHP warnings and notices in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

ob_start();

// Function to clean any output and send JSON
function send_json_response($data)
{
    // Clean any output that might have been generated
    ob_clean();

    // Set JSON header
    header('Content-Type: application/json');

    // Send the JSON response
    echo json_encode($data);

    // End output buffering and send
    ob_end_flush();
    exit();
}

// Register a shutdown function to catch any fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'A server error occurred',
            'debug' => [
                'error' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]
        ]);
        ob_end_flush();
    }
});
