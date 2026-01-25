<?php
/**
 * Root index file - redirects to public directory
 * This handles routing for Render deployment
 */

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// If no path specified, redirect to public/index.php
if (empty($path) || $path === 'index.php') {
    require_once __DIR__ . '/public/index.php';
    exit;
}

// Check if the requested file exists in public directory
$publicFile = __DIR__ . '/public/' . $path;

if (file_exists($publicFile) && is_file($publicFile)) {
    // If it's a PHP file, include it
    if (pathinfo($publicFile, PATHINFO_EXTENSION) === 'php') {
        require_once $publicFile;
        exit;
    }
    
    // For other files (CSS, JS, images), serve them directly
    $mimeType = mime_content_type($publicFile);
    header('Content-Type: ' . $mimeType);
    readfile($publicFile);
    exit;
}

// Check if it's an admin route
if (strpos($path, 'admin/') === 0) {
    $adminFile = __DIR__ . '/' . $path;
    if (file_exists($adminFile) && is_file($adminFile)) {
        require_once $adminFile;
        exit;
    }
}

// If file not found, try to serve from public directory
$fallbackFile = __DIR__ . '/public/' . $path;
if (file_exists($fallbackFile) && is_file($fallbackFile)) {
    require_once $fallbackFile;
    exit;
}

// If still not found, show 404
http_response_code(404);
echo '<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #333; }
        p { color: #666; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for could not be found.</p>
    <p><a href="/">Return to Home</a></p>
</body>
</html>';
?>