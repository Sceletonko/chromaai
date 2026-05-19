<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>ChromaAi Diagnostic Tool</h3>";

// Check PHP version
echo "PHP Version: " . phpversion() . "<br>";

// Check files
$files = ['db.php', '.env', 'vendor/autoload.php', 'composer.json'];
foreach ($files as $file) {
    echo "File '$file': " . (file_exists(__DIR__ . '/' . $file) ? "<span style='color:green'>Exists</span>" : "<span style='color:red'>Missing</span>") . "<br>";
}

require_once 'db.php';

// Check DB variables (redacted)
echo "<h4>Environment Variables (loaded via get_env_var)</h4>";
$vars = ['DB_TYPE', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'MAIL_HOST', 'GOOGLE_AI_API_KEY'];
foreach ($vars as $v) {
    $val = get_env_var($v);
    echo "$v: " . ($val ? "<span style='color:green'>Set</span>" : "<span style='color:red'>Not set</span>") . "<br>";
}

// Test DB connection
echo "<h4>Database Connection Test</h4>";
try {
    $pdo = get_db_connection();
    echo "<span style='color:green'>Successfully connected to the database!</span><br>";
} catch (Exception $e) {
    echo "<span style='color:red'>Connection failed: " . $e->getMessage() . "</span><br>";
}

echo "<h4>Server Info</h4>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
