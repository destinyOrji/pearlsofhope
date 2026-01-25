<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>PHP Test - Pearls of Hope</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        h2 { color: #007bff; margin-top: 30px; }
        .status { padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        a { color: #007bff; text-decoration: none; padding: 8px 15px; background: #e9ecef; border-radius: 5px; display: inline-block; margin: 5px; }
        a:hover { background: #007bff; color: white; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>✅ PHP Test Results</h1>";

// Basic PHP info
echo "<div class='status success'>";
echo "<strong>✅ PHP is Working!</strong><br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Built-in PHP Server') . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "</div>";

// Environment Variables
echo "<h2>Environment Variables</h2>";
echo "<table>";
echo "<tr><th>Variable</th><th>Status</th><th>Value</th></tr>";

$envVars = [
    'MONGODB_URI' => getenv('MONGODB_URI'),
    'MONGODB_DATABASE' => getenv('MONGODB_DATABASE'),
    'ADMIN_USERNAME' => getenv('ADMIN_USERNAME'),
    'ADMIN_PASSWORD' => getenv('ADMIN_PASSWORD') ? '***SET***' : '',
    'SESSION_SECRET' => getenv('SESSION_SECRET') ? '***SET***' : '',
    'RENDER' => getenv('RENDER'),
    'PORT' => getenv('PORT')
];

foreach ($envVars as $key => $value) {
    $status = $value ? '✅ Set' : '❌ Not Set';
    $displayValue = $value ? (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) : 'Not Set';
    echo "<tr><td>$key</td><td>$status</td><td>$displayValue</td></tr>";
}
echo "</table>";

// File System Check
echo "<h2>File System Check</h2>";
echo "<table>";
echo "<tr><th>File/Directory</th><th>Status</th></tr>";

$files = [
    'composer.json' => file_exists('composer.json'),
    'vendor/' => is_dir('vendor'),
    'public/' => is_dir('public'),
    'public/index.php' => file_exists('public/index.php'),
    'public/contact.php' => file_exists('public/contact.php'),
    'admin/' => is_dir('admin'),
    'includes/' => is_dir('includes'),
    'uploads/' => is_dir('uploads')
];

foreach ($files as $file => $exists) {
    $status = $exists ? '✅ Exists' : '❌ Missing';
    echo "<tr><td>$file</td><td>$status</td></tr>";
}
echo "</table>";

// PHP Extensions
echo "<h2>PHP Extensions</h2>";
echo "<table>";
echo "<tr><th>Extension</th><th>Status</th></tr>";

$extensions = ['mongodb', 'curl', 'json', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅ Loaded' : '❌ Not Loaded';
    echo "<tr><td>$ext</td><td>$status</td></tr>";
}
echo "</table>";

// Quick Links
echo "<h2>Quick Links</h2>";
echo '<a href="/public/index.php">Homepage</a>';
echo '<a href="/public/contact.php">Contact</a>';
echo '<a href="/public/activities.php">Activities</a>';
echo '<a href="/admin/login.php">Admin Login</a>';
echo '<a href="/debug.php">Debug Info</a>';

echo "</div></body></html>";
?>