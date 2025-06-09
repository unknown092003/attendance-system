<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/AttendanceModel.php';

class AttendanceController {
    private $userModel;
    private $attendanceModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->attendanceModel = new AttendanceModel();
    }
    
    /**
     * Show home page with attendance data
     */
    public function showHome() {
        $userId = Session::get('user_id');
        
        // Check if user exists
        if (!$userId) {
            header("Location: /attendance-system/login");
            exit();
        }
        
        $user = $this->userModel->getUserById($userId);
        
        // Get attendance data
        $data = [
            'user' => $user,
            'today' => $this->userModel->getTodayAttendance($userId),
            'month' => $this->userModel->getMonthAttendance($userId)
        ];
        
        // Check if user has an active session (time-in without time-out)
        $activeSession = false;
        $activeAttendance = $this->attendanceModel->getActiveAttendance($userId);
        
        if ($activeAttendance) {
            $activeSession = true;
            $data['activeAttendance'] = $activeAttendance;
        }
        
        $data['activeSession'] = $activeSession;
        
        // Get today's journal if exists
        $today = date('Y-m-d');
        $data['journal'] = $this->attendanceModel->getDailyJournal($userId, $today);
        
        // Get the last end day timestamp
        $data['last_end_day'] = $this->attendanceModel->getLastEndDayTimestamp($userId);
        
        // Calculate total hours completed if user has required hours
        if (!empty($user['required_hours'])) {
            $data['total_hours_completed'] = $this->userModel->getTotalHoursCompleted($userId);
            $data['remaining_hours'] = max(0, $user['required_hours'] - $data['total_hours_completed']);
        }
        
        require_once APP_PATH . '/view/user/home.php';
    }
    
    /**
     * Handle time-in request
     */
    public function timeIn() {
        $userId = Session::get('user_id');
        
        // Clear any day ended flags when starting a new day
        Session::remove('day_ended');
        // Update attendance status to active
        $_SESSION['attendance_status'] = 'active';
        
        $result = $this->attendanceModel->recordAttendance($userId, 'in');
        
        // For API requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }
        
        // For regular form submissions
        if ($result['success']) {
            Session::setFlash('success', 'Time-in recorded successfully');
        } else {
            Session::setFlash('error', 'Failed to record time-in');
        }
        
        header("Location: /attendance-system/home");
        exit();
    }
    
    /**
     * Handle time-out request
     */
    public function timeOut() {
        $userId = Session::get('user_id');
        $success = $this->attendanceModel->recordAttendance($userId, 'out');
        
        // Update attendance status to paused
        if ($success) {
            $_SESSION['attendance_status'] = 'paused';
        }
        
        // For API requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit();
        }
        
        // For regular form submissions
        if ($success) {
            Session::setFlash('success', 'Time-out recorded successfully');
        } else {
            Session::setFlash('error', 'Failed to record time-out');
        }
        
        header("Location: /attendance-system/home");
        exit();
    }
    
    /**
     * Get active attendance for a user
     */
    public function getActiveAttendance() {
        $userId = Session::get('user_id');
        $dayEnded = Session::get('day_ended');
        
        $activeAttendance = $this->attendanceModel->getActiveAttendance($userId);
        $accumulatedTime = $this->attendanceModel->getTotalAccumulatedTimeForToday($userId, $dayEnded);
        
        header('Content-Type: application/json');
        if ($dayEnded) {
            echo json_encode([
                'success' => true,
                'active' => false,
                'status' => 'ended',
                'total_seconds' => 0,
                'day_ended' => true
            ]);
        } else if ($activeAttendance) {
            echo json_encode([
                'success' => true,
                'active' => true,
                'status' => 'active',
                'time_in' => $activeAttendance['time_in'],
                'time_in_ms' => $activeAttendance['time_in_ms'],
                'server_time_ms' => $activeAttendance['server_time_ms'],
                'total_seconds' => $accumulatedTime['total_seconds'],
                'first_time_in' => $accumulatedTime['first_time_in'],
                'latest_activity' => $accumulatedTime['latest_activity']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'active' => false,
                'status' => 'paused',
                'total_seconds' => $accumulatedTime['total_seconds'] ?? 0,
                'first_time_in' => $accumulatedTime['first_time_in'] ?? null,
                'latest_activity' => $accumulatedTime['latest_activity'] ?? null
            ]);
        }
        exit();
    }
    
    /**
     * Handle end day request
     */
    public function endDay() {
        $userId = Session::get('user_id');
        
        // For API requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $journal = $data['journal'] ?? '';
                
                $success = $this->attendanceModel->endDay($userId, $journal);
                
                // Set a permanent session flag to indicate day has ended
                if ($success) {
                    Session::set('day_ended', true);
                    // Store in session that will persist until next time-in
                    $_SESSION['attendance_status'] = 'ended';
                    // Reset timer state
                    $_SESSION['timer_active'] = false;
                    $_SESSION['timer_elapsed'] = 0;
                    $_SESSION['timer_start'] = null;
                }
                
                header('Content-Type: application/json');
                echo json_encode(['success' => $success]);
                exit();
            }
        }
        
        // For regular form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $journal = $_POST['journal'] ?? '';
            
            $success = $this->attendanceModel->endDay($userId, $journal);
            
            if ($success) {
                // Set a permanent session flag to indicate day has ended
                Session::set('day_ended', true);
                // Store in session that will persist until next time-in
                $_SESSION['attendance_status'] = 'ended';
                Session::setFlash('success', 'Day ended successfully. Journal saved.');
            } else {
                Session::setFlash('error', 'Failed to end day');
            }
            
            header("Location: /attendance-system/home");
            exit();
        }
        
        // Show end day form for GET requests
        require_once APP_PATH . '/view/user/end_day.php';
    }
    
    /**
     * Get timer state for client-side timer
     */
    public function getTimerState() {
        $userId = Session::get('user_id');
        $dayEnded = Session::get('day_ended');
        
        $activeAttendance = $this->attendanceModel->getActiveAttendance($userId);
        $accumulatedTime = $this->attendanceModel->getTotalAccumulatedTimeForToday($userId, $dayEnded);
        
        header('Content-Type: application/json');
        if ($dayEnded) {
            echo json_encode([
                'success' => true,
                'isActive' => false,
                'elapsedSeconds' => 0
            ]);
        } else if ($activeAttendance) {
            echo json_encode([
                'success' => true,
                'isActive' => true,
                'startTime' => $activeAttendance['time_in'],
                'elapsedSeconds' => $accumulatedTime['total_seconds']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'isActive' => false,
                'elapsedSeconds' => $accumulatedTime['total_seconds'] ?? 0
            ]);
        }
        exit();
    }
    
    /**
     * Start timer
     */
    public function startTimer() {
        $userId = Session::get('user_id');
        $data = json_decode(file_get_contents('php://input'), true);
        $elapsedSeconds = $data['elapsedSeconds'] ?? 0;
        
        // Store timer state in session
        $_SESSION['timer_active'] = true;
        $_SESSION['timer_start'] = date('Y-m-d H:i:s');
        $_SESSION['timer_elapsed'] = $elapsedSeconds;
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    
    /**
     * Pause timer
     */
    public function pauseTimer() {
        $userId = Session::get('user_id');
        $data = json_decode(file_get_contents('php://input'), true);
        $elapsedSeconds = $data['elapsedSeconds'] ?? 0;
        
        // Store timer state in session
        $_SESSION['timer_active'] = false;
        $_SESSION['timer_elapsed'] = $elapsedSeconds;
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    
    /**
     * Reset timer
     */
    public function resetTimer() {
        // Reset timer state in session
        $_SESSION['timer_active'] = false;
        $_SESSION['timer_elapsed'] = 0;
        $_SESSION['timer_start'] = null;
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
}