<?php
// Test file to verify feedback feature
session_start();

// Database connection
$host = 'localhost';
$db   = 'attendance_system';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<h2>Database Connection: ✅ Success</h2>";
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Test 1: Check if journal_feedback table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'journal_feedback'");
    $tableExists = $stmt->fetch();
    if ($tableExists) {
        echo "<h3>✅ journal_feedback table exists</h3>";
    } else {
        echo "<h3>❌ journal_feedback table does not exist</h3>";
    }
} catch (Exception $e) {
    echo "<h3>❌ Error checking table: " . $e->getMessage() . "</h3>";
}

// Test 2: Check table structure
try {
    $stmt = $pdo->query("DESCRIBE journal_feedback");
    $columns = $stmt->fetchAll();
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<h3>❌ Error checking table structure: " . $e->getMessage() . "</h3>";
}

// Test 3: Check if there are any journals to test with
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM daily_journals");
    $result = $stmt->fetch();
    echo "<h3>📊 Total journal entries: " . $result['count'] . "</h3>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, user_id, date FROM daily_journals LIMIT 3");
        $sampleJournals = $stmt->fetchAll();
        echo "<h3>Sample journal entries:</h3>";
        echo "<ul>";
        foreach ($sampleJournals as $journal) {
            echo "<li>ID: " . $journal['id'] . ", User: " . $journal['user_id'] . ", Date: " . $journal['date'] . "</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<h3>❌ Error checking journals: " . $e->getMessage() . "</h3>";
}

// Test 4: Check if there are any admins
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    $result = $stmt->fetch();
    echo "<h3>👥 Total admins: " . $result['count'] . "</h3>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, full_name FROM admins LIMIT 3");
        $sampleAdmins = $stmt->fetchAll();
        echo "<h3>Sample admins:</h3>";
        echo "<ul>";
        foreach ($sampleAdmins as $admin) {
            echo "<li>ID: " . $admin['id'] . ", Name: " . $admin['full_name'] . "</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<h3>❌ Error checking admins: " . $e->getMessage() . "</h3>";
}

echo "<h2>🎉 Feedback Feature Test Complete!</h2>";
echo "<p>If all tests passed, the feedback feature should be working correctly.</p>";
echo "<p>To test the feature:</p>";
echo "<ol>";
echo "<li>Login as an admin</li>";
echo "<li>Go to a user's profile page</li>";
echo "<li>Click on the Journal tab</li>";
echo "<li>Look for 'Add Feedback' buttons on journal entries</li>";
echo "<li>Click to add/edit feedback</li>";
echo "</ol>";
?> 