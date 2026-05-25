<?php
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$cacheDir = __DIR__ . '/cache/';

try {
    // Check if cache directory exists
    if (!is_dir($cacheDir)) {
        echo json_encode(['success' => false, 'message' => 'Cache directory not found']);
        exit;
    }

    // Get all JSON files in cache directory
    $jsonFiles = glob($cacheDir . '*.json');
    $deletedCount = 0;

    foreach ($jsonFiles as $file) {
        if (is_file($file) && unlink($file)) {
            $deletedCount++;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Successfully deleted $deletedCount cache files"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' + $e->getMessage()]);
}
?>