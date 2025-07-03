<?php
// Always return JSON and suppress errors from being output as HTML
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session and check CSRF token
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

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

    // Only allow update for logged-in user
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    // Prepare supervisor notes as JSON
    $supervisor_notes = json_encode([
        'name' => $_POST['supervisor_name'] ?? '',
        'email' => $_POST['supervisor_email'] ?? ''
    ]);

    // Update user data
    $stmt = $pdo->prepare("UPDATE users SET 
        full_name = ?, email = ?, phone = ?, university = ?, college = ?,
        program = ?, year_level = ?, required_hours = ?,
        supervisor_notes = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $_POST['full_name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['university'] ?? '',
        $_POST['college'] ?? '',
        $_POST['program'] ?? '',
        $_POST['year_level'] ?? '',
        $_POST['required_hours'] ?? 0,
        $supervisor_notes,
        $_SESSION['user_id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit;
?>