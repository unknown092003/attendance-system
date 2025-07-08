<?php
// Simple debug file to test profile functionality
session_start();

// Simulate admin session for testing
$_SESSION['admin_id'] = 1;
$_SESSION['role'] = 'admin';

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
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get a test user
$stmt = $pdo->prepare("SELECT * FROM users LIMIT 1");
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    die("No users found in database");
}

// Set the student_id parameter
$_GET['student_id'] = $user['id'];

echo "<h1>Profile Debug Test</h1>";
echo "<p>Testing profile page for user: " . htmlspecialchars($user['full_name']) . " (ID: " . $user['id'] . ")</p>";
echo "<p>Admin session: " . (isset($_SESSION['admin_id']) ? 'Yes' : 'No') . "</p>";
echo "<hr>";

// Include the profile page
include 'app/view/user/profile.php';
?> 