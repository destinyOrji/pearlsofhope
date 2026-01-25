<?php
/**
 * Health check endpoint for Render
 */

header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'memory_usage' => memory_get_usage(true),
    'checks' => []
];

// Check if basic files exist
$health['checks']['files'] = [
    'composer.json' => file_exists('composer.json'),
    'public_index' => file_exists('public/index.php'),
    'includes_config' => file_exists('includes/config.php')
];

// Check environment variables
$health['checks']['environment'] = [
    'mongodb_uri' => !empty(getenv('MONGODB_URI')),
    'render' => !empty(getenv('RENDER'))
];

// Check PHP extensions
$health['checks']['extensions'] = [
    'mongodb' => extension_loaded('mongodb'),
    'curl' => extension_loaded('curl'),
    'json' => extension_loaded('json')
];

// Overall health status
$allChecks = true;
foreach ($health['checks'] as $category => $checks) {
    foreach ($checks as $check => $status) {
        if (!$status) {
            $allChecks = false;
            break 2;
        }
    }
}

$health['status'] = $allChecks ? 'healthy' : 'unhealthy';

http_response_code($allChecks ? 200 : 500);
echo json_encode($health, JSON_PRETTY_PRINT);
?>