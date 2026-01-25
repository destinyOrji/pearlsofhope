<?php
echo "Debug Login Page<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Parent directory: " . dirname(__DIR__) . "<br>";

// Test if files exist
$config_path = __DIR__ . '/../includes/config.php';
$db_path = __DIR__ . '/../includes/db.php';
$functions_path = __DIR__ . '/../includes/functions.php';

echo "Config file exists: " . (file_exists($config_path) ? 'YES' : 'NO') . " - $config_path<br>";
echo "DB file exists: " . (file_exists($db_path) ? 'YES' : 'NO') . " - $db_path<br>";
echo "Functions file exists: " . (file_exists($functions_path) ? 'YES' : 'NO') . " - $functions_path<br>";

// Try to include config
echo "<br>Trying to include config...<br>";
try {
    require_once $config_path;
    echo "Config included successfully<br>";
} catch (Exception $e) {
    echo "Config error: " . $e->getMessage() . "<br>";
}

// Try to include db
echo "<br>Trying to include db...<br>";
try {
    require_once $db_path;
    echo "DB included successfully<br>";
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage() . "<br>";
}

// Try to include functions
echo "<br>Trying to include functions...<br>";
try {
    require_once $functions_path;
    echo "Functions included successfully<br>";
} catch (Exception $e) {
    echo "Functions error: " . $e->getMessage() . "<br>";
}

echo "<br>All includes loaded successfully!<br>";
?>