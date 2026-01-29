<?php
/**
 * MySQL Connection Test Script
 * Tests connection to remote MySQL database
 */

$host = 'srv1513.hstgr.io';
$port = 3306;
$database = 'u726786619_arab_db';
$username = 'u726786619_arab_db';
$password = 'Mirxa420$';

echo "Testing MySQL Connection...\n";
echo "Host: $host:$port\n";
echo "Database: $database\n";
echo "User: $username\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

try {
    // Create connection with detailed error reporting
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    echo "Attempting to connect...\n";
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "✅ SUCCESS: Connection established!\n\n";
    
    // Test simple query
    echo "Testing query execution...\n";
    $result = $pdo->query("SELECT VERSION() as version, NOW() as current_time");
    $row = $result->fetch();
    
    echo "MySQL Version: " . $row['version'] . "\n";
    echo "Server Time: " . $row['current_time'] . "\n\n";
    
    // Check database tables
    echo "Checking database tables...\n";
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n✅ All tests completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ CONNECTION FAILED\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    
    // Provide troubleshooting tips
    echo "\nTroubleshooting Tips:\n";
    echo "1. Verify hostname and port are correct\n";
    echo "2. Check if database user has correct permissions\n";
    echo "3. Verify password (special characters may need escaping)\n";
    echo "4. Check if MySQL allows remote connections from your IP\n";
    echo "5. Ensure database name is correct\n";
    
    exit(1);
}
?>