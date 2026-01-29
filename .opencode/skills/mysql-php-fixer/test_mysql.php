<?php
/**
 * Simple MySQL Connection Test for arabclue.com
 * Upload this to your website and run it via browser
 * Usage: https://arabclue.com/test_mysql.php
 */

// Database configuration
$host = 'srv1513.hstgr.io';
$port = 3306;
$database = 'u726786619_arab_db';
$username = 'u726786619_arab_db';
$password = 'Mirxa420$';

// Security: Remove this file after testing!
if (php_sapi_name() !== 'cli') {
    echo '<!DOCTYPE html><html><head><title>MySQL Connection Test</title>';
    echo '<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}';
    echo '.success{color:#28a745;}.error{color:#dc3545;}.info{color:#17a2b8;}</style></head><body>';
}

$message = '';

try {
    // Attempt connection
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    $message .= "✅ Connection Successful!\n\n";
    
    // Get MySQL version
    $result = $pdo->query("SELECT VERSION() as version, NOW() as current_time");
    $row = $result->fetch();
    $message .= "MySQL Version: " . $row['version'] . "\n";
    $message .= "Server Time: " . $row['current_time'] . "\n\n";
    
    // Check tables
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    $message .= "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        $message .= "  - $table\n";
    }
    
    $message .= "\n✅ All tests passed!";
    
} catch (PDOException $e) {
    $message .= "❌ Connection Failed\n\n";
    $message .= "Error Code: " . $e->getCode() . "\n";
    $message .= "Error: " . $e->getMessage() . "\n\n";
    $message .= "Possible causes:\n";
    $message .= "- Incorrect password (check for special characters)\n";
    $message .= "- Database user permissions\n";
    $message .= "- MySQL not allowing remote connections\n";
    $message .= "- Firewall blocking connection\n";
}

// Display result
if (php_sapi_name() !== 'cli') {
    echo '<h1>MySQL Connection Test</h1>';
    echo '<h2>Configuration:</h2>';
    echo '<ul>';
    echo "<li>Host: $host:$port</li>";
    echo "<li>Database: $database</li>";
    echo "<li>User: $username</li>";
    echo '</ul>';
    echo '<h2>Result:</h2>';
    echo '<pre style="background:#f5f5f5;padding:15px;border-radius:5px;">';
    echo htmlspecialchars($message);
    echo '</pre>';
    echo '<p style="color:orange;font-weight:bold;">⚠️ IMPORTANT: Delete this file after testing!</p>';
    echo '</body></html>';
} else {
    echo $message;
}
?>