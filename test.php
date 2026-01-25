<?php
echo "<h1>✅ PHP is Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>Environment Variables</h2>";
echo "<p>MONGODB_URI: " . (getenv('MONGODB_URI') ? '✅ Set' : '❌ Not Set') . "</p>";
echo "<p>RENDER: " . (getenv('RENDER') ?: '❌ Not Set') . "</p>";

echo "<h2>Quick Links</h2>";
echo '<p><a href="/public/index.php">Homepage</a></p>';
echo '<p><a href="/public/contact.php">Contact</a></p>';
echo '<p><a href="/admin/login.php">Admin</a></p>';
?>