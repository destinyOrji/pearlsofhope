<?php
/**
 * Debug file to check server configuration
 */

echo "<h1>🔍 Server Debug Information</h1>";

echo "<h2>PHP Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "<br>";

echo "<h2>Environment Variables</h2>";
echo "MONGODB_URI: " . (getenv('MONGODB_URI') ? 'Set ✅' : 'Not Set ❌') . "<br>";
echo "MONGODB_DATABASE: " . (getenv('MONGODB_DATABASE') ?: 'Not Set') . "<br>";
echo "RENDER: " . (getenv('RENDER') ?: 'Not Set') . "<br>";

echo "<h2>File System Check</h2>";
echo "Current Directory: " . getcwd() . "<br>";
echo "Index.php exists: " . (file_exists('index.php') ? 'Yes ✅' : 'No ❌') . "<br>";
echo "Public directory exists: " . (is_dir('public') ? 'Yes ✅' : 'No ❌') . "<br>";
echo "Public/index.php exists: " . (file_exists('public/index.php') ? 'Yes ✅' : 'No ❌') . "<br>";
echo "Composer.json exists: " . (file_exists('composer.json') ? 'Yes ✅' : 'No ❌') . "<br>";
echo "Vendor directory exists: " . (is_dir('vendor') ? 'Yes ✅' : 'No ❌') . "<br>";

echo "<h2>MongoDB Extension</h2>";
echo "MongoDB extension loaded: " . (extension_loaded('mongodb') ? 'Yes ✅' : 'No ❌') . "<br>";

echo "<h2>Error Log</h2>";
$errorLog = error_get_last();
if ($errorLog) {
    echo "Last Error: " . $errorLog['message'] . "<br>";
    echo "File: " . $errorLog['file'] . "<br>";
    echo "Line: " . $errorLog['line'] . "<br>";
} else {
    echo "No recent errors ✅<br>";
}

echo "<h2>Quick Links</h2>";
echo '<a href="/public/index.php">Try Public Homepage</a><br>';
echo '<a href="/public/contact.php">Try Contact Page</a><br>';
echo '<a href="/admin/login.php">Try Admin Login</a><br>';

phpinfo();
?>