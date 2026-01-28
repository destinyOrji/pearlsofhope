<?php
/**
 * Configuration file for NGO Website
 * Handles both local development and production (Render) environments
 */

// Prevent multiple inclusions
if (defined('NGO_CONFIG_LOADED')) {
    return;
}
define('NGO_CONFIG_LOADED', true);

// Environment detection
$isProduction = isset($_ENV['RENDER']) || isset($_SERVER['RENDER']) || 
                isset($_ENV['RAILWAY']) || isset($_SERVER['RAILWAY']) ||
                isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) ||
                (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false) ||
                (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false) ||
                (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false);

// Load Composer autoloader
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php'
];

foreach ($autoloadPaths as $autoloadPath) {
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        break;
    }
}

// MongoDB Configuration - Use environment variables in production
if ($isProduction) {
    define('MONGODB_URI', $_ENV['MONGODB_URI'] ?? $_SERVER['MONGODB_URI'] ?? '');
    define('MONGODB_DATABASE', $_ENV['MONGODB_DATABASE'] ?? $_SERVER['MONGODB_DATABASE'] ?? 'ngo_website');
} else {
    // Local development settings
    $mongoPassword = 'destinyorji18_db';
    define('MONGODB_URI', 'mongodb+srv://destinyorji18_db_user:' . $mongoPassword . '@cluster0.jvboeyk.mongodb.net/ngo_website?retryWrites=true&w=majority&appName=Cluster0');
    define('MONGODB_DATABASE', 'ngo_website');
}

// Admin Configuration
define('ADMIN_USERNAME', $_ENV['ADMIN_USERNAME'] ?? $_SERVER['ADMIN_USERNAME'] ?? 'admin');
define('ADMIN_PASSWORD', $_ENV['ADMIN_PASSWORD'] ?? $_SERVER['ADMIN_PASSWORD'] ?? 'admin123');

// Session Configuration
define('SESSION_SECRET', $_ENV['SESSION_SECRET'] ?? $_SERVER['SESSION_SECRET'] ?? 'your-default-secret-key-change-this');

// Application Configuration
define('SITE_NAME', 'Pearls of Hope');
define('APP_VERSION', '1.0.0');
define('IS_PRODUCTION', $isProduction);

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Session Settings
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour

// Error Reporting
if ($isProduction) {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

if ($isProduction) {
    ini_set('session.cookie_secure', 1); // HTTPS only in production
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Africa/Lagos');
?>