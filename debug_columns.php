<?php
// Debug script to check users table structure
require_once __DIR__ . '/config/database.php';

$config = require __DIR__ . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']}",
    $config['username'],
    $config['password'],
    $config['options']
);

// Get table structure
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Users table structure:</h2>";
echo "<pre>";
foreach ($columns as $column) {
    echo $column['Field'] . " - " . $column['Type'] . "\n";
}
echo "</pre>";

// Also check if there are any users
$stmt = $pdo->query("SELECT * FROM users LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Sample user data:</h2>";
echo "<pre>";
print_r($user);
echo "</pre>";
?>