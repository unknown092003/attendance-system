<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if user is admin
if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$admin_id = $_SESSION['admin_id'];

switch ($method) {
    case 'POST':
        // Add or update feedback
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['journal_id']) || !isset($input['feedback_text'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $journal_id = (int)$input['journal_id'];
        $feedback_text = trim($input['feedback_text']);
        
        if (empty($feedback_text)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Feedback text cannot be empty']);
            exit;
        }
        
        // Check if journal exists
        $stmt = $pdo->prepare("SELECT id FROM daily_journals WHERE id = ?");
        $stmt->execute([$journal_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Journal not found']);
            exit;
        }
        
        // Check if feedback already exists
        $stmt = $pdo->prepare("SELECT id FROM journal_feedback WHERE journal_id = ?");
        $stmt->execute([$journal_id]);
        $existing_feedback = $stmt->fetch();
        
        if ($existing_feedback) {
            // Update existing feedback
            $stmt = $pdo->prepare("UPDATE journal_feedback SET feedback_text = ?, updated_at = CURRENT_TIMESTAMP WHERE journal_id = ?");
            $stmt->execute([$feedback_text, $journal_id]);
        } else {
            // Insert new feedback
            $stmt = $pdo->prepare("INSERT INTO journal_feedback (journal_id, admin_id, feedback_text) VALUES (?, ?, ?)");
            $stmt->execute([$journal_id, $admin_id, $feedback_text]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Feedback saved successfully']);
        break;
        
    case 'GET':
        // Get feedback for a specific journal
        if (!isset($_GET['journal_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Journal ID required']);
            exit;
        }
        
        $journal_id = (int)$_GET['journal_id'];
        
        $stmt = $pdo->prepare("
            SELECT jf.*, a.full_name as admin_name 
            FROM journal_feedback jf 
            JOIN admins a ON jf.admin_id = a.id 
            WHERE jf.journal_id = ?
        ");
        $stmt->execute([$journal_id]);
        $feedback = $stmt->fetch();
        
        echo json_encode(['success' => true, 'feedback' => $feedback]);
        break;
        
    case 'DELETE':
        // Delete feedback
        if (!isset($_GET['journal_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Journal ID required']);
            exit;
        }
        
        $journal_id = (int)$_GET['journal_id'];
        
        $stmt = $pdo->prepare("DELETE FROM journal_feedback WHERE journal_id = ?");
        $stmt->execute([$journal_id]);
        
        echo json_encode(['success' => true, 'message' => 'Feedback deleted successfully']);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?> 