<?php
// Check if MOA column exists
require_once __DIR__ . '/config/database.php';

$config = require __DIR__ . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']}",
    $config['username'],
    $config['password'],
    $config['options']
);

// Check table structure
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Users table columns:</h2>";
foreach ($columns as $column) {
    echo $column['Field'] . " - " . $column['Type'] . "<br>";
}

// Check if MOA column exists
$stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'moa'");
$moaExists = $stmt->rowCount() > 0;

if (!$moaExists) {
    echo "<br><strong>MOA column does not exist! Adding it...</strong><br>";
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN moa TINYINT(1) DEFAULT 0 AFTER supervisor");
        echo "MOA column added successfully!";
    } catch (PDOException $e) {
        echo "Error adding MOA column: " . $e->getMessage();
    }
} else {
    echo "<br><strong>MOA column exists!</strong>";
}
?>