<?php
// public/api/attendance.php
require_once __DIR__ . '/../../app/controllers/AttendanceController.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$attendanceController = new AttendanceController();
$userId = $_SESSION['user_id'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'time-in':
            $result = $attendanceController->timeIn($userId);
            echo json_encode(['success' => $result]);
            break;
            
        case 'time-out':
            $result = $attendanceController->timeOut($userId);
            echo json_encode(['success' => $result]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}