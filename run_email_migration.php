<?php
// Run the email migration
require_once __DIR__ . '/config/database.php';

$config = require __DIR__ . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']}",
    $config['username'],
    $config['password'],
    $config['options']
);

try {
    $sql = file_get_contents(__DIR__ . '/database/migrations/add_email_column.sql');
    $pdo->exec($sql);
    echo "Email column added successfully!";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Email column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>