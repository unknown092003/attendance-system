<?php
// Database connection
$config = require __DIR__ . '/../../config/database.php';

try {
    $db = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']}",
        $config['username'],
        $config['password'],
        $config['options']
    );
    
    echo "Connected to database successfully.\n";
    
    // Run the migrations
    $migrations = [
        'add_edited_by_admin_to_journals.sql'
    ];
    
    foreach ($migrations as $migration) {
        $sql = file_get_contents(__DIR__ . '/' . $migration);
        
        if ($sql) {
            echo "Running migration: $migration\n";
            $db->exec($sql);
            echo "Migration completed successfully.\n";
        } else {
            echo "Error: Could not read migration file $migration\n";
        }
    }
    
    echo "All migrations completed successfully.\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit;
}