<?php
// app/views/user/profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /user/log-in.php');
    exit();
}

require_once __DIR__ . '/../../controllers/AttendanceController.php';
$attendanceController = new AttendanceController();
$totalHours = $attendanceController->getTotalHoursWorked($_SESSION['user_id']);
$todayHours = $attendanceController->getTodayHours($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="profile-container">
        <h1>Attendance Summary</h1>
        
        <div class="stats">
            <div class="stat">
                <h3>Today's Hours</h3>
                <p><?= $todayHours ?> hours</p>
            </div>
            
            <div class="stat">
                <h3>Total Hours</h3>
                <p><?= $totalHours ?> hours</p>
            </div>
        </div>
        
        <a href="/user/home.php">Back to Home</a>
    </div>
</body>
</html>