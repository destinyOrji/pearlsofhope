<?php
/**
 * Health check endpoint for Vercel deployment
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'environment' => 'production'
];

// Test MongoDB connection if possible
try {
    require_once '../includes/config.php';
    require_once '../includes/db.php';
    
    $mongoTest = testMongoConnection();
    $response['mongodb'] = $mongoTest ? 'connected' : 'disconnected';
} catch (Exception $e) {
    $response['mongodb'] = 'error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>