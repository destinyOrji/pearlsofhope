<?php
/**
 * Root index file - handles all routing for Render deployment
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Get the requested URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Remove query string
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Remove leading slash
    $path = ltrim($path, '/');
    
    // If no path specified or root, show homepage
    if (empty($path) || $path === 'index.php') {
        if (file_exists(__DIR__ . '/public/index.php')) {
            require_once __DIR__ . '/public/index.php';
        } else {
            echo '<h1>Welcome to Pearls of Hope</h1>';
            echo '<p><a href="/public/index.php">Homepage</a></p>';
            echo '<p><a href="/public/contact.php">Contact</a></p>';
            echo '<p><a href="/admin/login.php">Admin</a></p>';
        }
        exit;
    }
    
    // Handle test and debug files
    if ($path === 'test.php' || $path === 'debug.php') {
        if (file_exists(__DIR__ . '/' . $path)) {
            require_once __DIR__ . '/' . $path;
            exit;
        }
    }
    
    // Check if the requested file exists in public directory
    $publicFile = __DIR__ . '/public/' . $path;
    if (file_exists($publicFile) && is_file($publicFile)) {
        if (pathinfo($publicFile, PATHINFO_EXTENSION) === 'php') {
            require_once $publicFile;
            exit;
        }
        
        // For other files, serve them directly
        $mimeType = mime_content_type($publicFile) ?: 'application/octet-stream';
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
    
    // 404 - File not found
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Page Not Found - Pearls of Hope</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc3545; margin-bottom: 20px; }
            p { color: #666; margin-bottom: 20px; }
            a { color: #007bff; text-decoration: none; padding: 10px 20px; background: #e9ecef; border-radius: 5px; display: inline-block; margin: 5px; }
            a:hover { background: #007bff; color: white; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404 - Page Not Found</h1>
            <p>The page you are looking for could not be found.</p>
            <p>
                <a href="/">Home</a>
                <a href="/public/contact.php">Contact</a>
                <a href="/admin/login.php">Admin</a>
            </p>
        </div>
    </body>
    </html>';
    
} catch (Exception $e) {
    // Error handling
    http_response_code(500);
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Server Error - Pearls of Hope</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc3545; }
            p { color: #666; }
            a { color: #007bff; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Server Error</h1>
            <p>We are experiencing technical difficulties. Please try again later.</p>
            <p><a href="/test.php">Test Page</a> | <a href="/debug.php">Debug Info</a></p>
        </div>
    </body>
    </html>';
}
?>