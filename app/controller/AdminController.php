<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/AttendanceModel.php';
require_once __DIR__ . '/../model/AdminModel.php';

class AdminController {
    private $userModel;
    private $attendanceModel;
    private $adminModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->attendanceModel = new AttendanceModel();
        $this->adminModel = new AdminModel();
        
        // Check if user is admin
        if (!Session::get('admin_id')) {
            header("Location: /attendance-system/admin-login");
            exit();
        }
    }
    
    /**
     * Show admin dashboard
     */
    public function dashboard() {
        $today = date('Y-m-d');
        
        // Get all students and students who attended today
        $allStudents = $this->userModel->getAllUsers(['role' => 'student']);
        $studentsToday = $this->attendanceModel->getDailyAttendance($today);
        
        // Extract IDs of students who already have attendance today
        $attendedStudentIds = [];
        foreach ($studentsToday as $student) {
            $attendedStudentIds[] = $student['user_id'];
        }
        
        // Filter out students who already have attendance
        $absentStudents = [];
        foreach ($allStudents as $student) {
            if (!in_array($student['id'], $attendedStudentIds)) {
                $absentStudents[] = $student;
            }
        }
        
        $internProgress = $this->userModel->getInternProgress();
        $ongoingProgress = [];
        $completedProgress = [];

        foreach ($internProgress as $intern) {
            $completedHours = (float)$intern['completed_hours'];
            $requiredHours = (float)$intern['required_hours'];
            if ($requiredHours > 0) {
                $percentage = ($completedHours / $requiredHours) * 100;
                if ($percentage >= 100) {
                    $completedProgress[] = $intern;
                } else {
                    $ongoingProgress[] = $intern;
                }
            } else {
                $ongoingProgress[] = $intern;
            }
        }

        $data = [
            'userCount' => $this->userModel->countUsers(),
            'activeUsers' => $this->userModel->countActiveUsers(),
            'todayAttendance' => $this->attendanceModel->getTodayAttendanceCount(),
            'recentActivity' => $this->attendanceModel->getRecentActivity(5),
            'studentsToday' => $studentsToday,
            'ongoingProgress' => $ongoingProgress,
            'completedProgress' => $completedProgress,
            'allStudents' => $allStudents,
            'absentStudents' => $absentStudents,
            'today' => $today
        ];
        
        require_once APP_PATH . '/view/admin/dashboard.php';
    }
    
    /**
     * Show users management page
     */
    public function users() {
        $allUsers = $this->userModel->getAllUsers();
        $activeUsers = [];
        $inactiveUsers = [];

        foreach ($allUsers as $user) {
            if ($user['status'] === 'active') {
                $activeUsers[] = $user;
            } else {
                $inactiveUsers[] = $user;
            }
        }

        $data = [
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers
        ];
        
        require_once APP_PATH . '/view/admin/users.php';
    }
    
    /**
     * Show admins management page
     */
    public function admins() {
        $data = [
            'admins' => $this->adminModel->getAllAdmins()
        ];
        
        require_once APP_PATH . '/view/admin/admins.php';
    }
    
    /**
     * Handle password change
     */
    public function changePassword() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }
        
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit();
        }
        
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
            exit();
        }
        
        $adminId = Session::get('admin_id');
        $success = $this->adminModel->changePassword($adminId, $currentPassword, $newPassword);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        }
        exit();
    }
    
    /**
     * Show create admin form / handle admin creation
     */
    public function createAdmin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstname = $_POST['firstname'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate input
            $errors = [];
            
            if (empty($firstname)) {
                $errors[] = 'First name is required';
            }
            
            if (empty($lastname)) {
                $errors[] = 'Last name is required';
            }
            
            if (empty($username)) {
                $errors[] = 'Username is required';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            }
            
            if ($password !== $confirm_password) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($errors)) {
                $adminId = $this->adminModel->createAdmin($firstname, $lastname, $username, $password);
                
                if ($adminId) {
                    Session::setFlash('success', 'Admin created successfully');
                    header("Location: /attendance-system/admin/admins");
                    exit();
                } else {
                    $errors[] = 'Failed to create admin';
                }
            }
            
            // If we get here, there were errors
            Session::setFlash('errors', $errors);
        }
        
        require_once APP_PATH . '/view/admin/create_admin.php';
    }
    
    /**
     * Handle admin deletion
     */
    public function deleteAdmin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminId = $_POST['admin_id'] ?? 0;
            
            if ($adminId) {
                $success = $this->adminModel->deleteAdmin($adminId);
                
                if ($success) {
                    Session::setFlash('success', 'Admin deleted successfully');
                } else {
                    Session::setFlash('error', 'Failed to delete admin');
                }
            }
        }
        
        header("Location: /attendance-system/admin/admins");
        exit();
    }
    
    /**
     * Show create user form / handle user creation
     */
    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $full_name = $_POST['full_name'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $required_hours = !empty($_POST['required_hours']) ? (float)$_POST['required_hours'] : null;
            
            // Validate input
            $errors = [];
            
            if (empty($full_name)) {
                $errors[] = 'Full name is required';
            }
            
            if (empty($errors)) {
                try {
                    // Generate unique PIN automatically
                    $pin = $this->userModel->generateUniquePin();
                    
                    $userId = $this->userModel->createUser($full_name, $pin, $role, $required_hours);
                    
                    if ($userId) {
                        Session::setFlash('success', "User created successfully. PIN: {$pin}");
                        header("Location: /attendance-system/admin/users");
                        exit();
                    } else {
                        $errors[] = 'Failed to create user';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Error generating PIN: ' . $e->getMessage();
                }
            }
            
            // If we get here, there were errors
            Session::setFlash('errors', $errors);
        }
        
        require_once APP_PATH . '/view/admin/create_user.php';
    }
    
    /**
     * Handle user status update
     */
    public function updateUserStatus() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }
        
        $userId = $_POST['user_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $moa = $_POST['moa'] ?? '0';
        
        // Debug logging
        error_log('UpdateUserStatus - POST data: ' . print_r($_POST, true));
        error_log('UpdateUserStatus - userId: ' . $userId . ', status: ' . $status . ', moa: ' . $moa);
        
        if (!$userId || !in_array($status, ['active', 'inactive'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit();
        }
        
        $currentUser = $this->userModel->getUserById($userId);
        if (!$currentUser) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit();
        }

        $updateData = [
            'status' => $status,
            'moa' => (int)$moa
        ];
        
        $pin = null; // Initialize pin variable

        // Logic for handling PIN
        if ($status === 'inactive') {
            $updateData['pin'] = null; // Set PIN to null if user is made inactive
        } elseif ($currentUser['status'] === 'inactive' && $status === 'active') {
            // Generate a new PIN only when moving from inactive to active
            try {
                $pin = $this->userModel->generateUniquePin();
                $updateData['pin'] = $pin;
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error generating PIN: ' . $e->getMessage()]);
                exit();
            }
        }
        
        $success = $this->userModel->updateUser($userId, $updateData);
        
        if ($success) {
            $message = ($pin !== null) ? "Status updated successfully. New PIN: {$pin}" : 'Status updated successfully';
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        exit();
    }
    
    /**
     * Handle user deletion
     */
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            
            if ($userId) {
                $success = $this->userModel->deleteUser($userId);
                
                if ($success) {
                    Session::setFlash('success', 'User deleted successfully');
                } else {
                    Session::setFlash('error', 'Failed to delete user');
                }
            }
        }
        
        header("Location: /attendance-system/admin/users");
        exit();
    }
    
    /**
     * Show reports page
     */
    public function reports() {
        $month = $_GET['month'] ?? date('Y-m');
        
        $data = [
            'month' => $month,
            'attendance' => $this->attendanceModel->getMonthlyReport($month)
        ];
        
        require_once APP_PATH . '/view/admin/reports.php';
    }
    
    /**
     * Show journals page
     */
    public function viewJournals() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $student_id = $_GET['student_id'] ?? null;
        
        if ($student_id) {
            // Single student view
            $student = $this->userModel->getUserById($student_id);
            $journal = $this->attendanceModel->getDailyJournal($student_id, $date);
            $attendance = $this->attendanceModel->getUserAttendanceByDate($student_id, $date);
            
            $data = [
                'date' => $date,
                'student' => $student,
                'journal' => $journal,
                'attendance' => $attendance
            ];
        } else {
            // List of students with attendance/journals for the selected date
            $students = $this->attendanceModel->getStudentsWithActivityOnDate($date);
            
            $data = [
                'date' => $date,
                'students' => $students
            ];
        }
        
        require_once APP_PATH . '/view/admin/journals.php';
    }
    
    /**
     * Handle special attendance recording
     */
    public function specialAttendance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }
        
        // Debug: Log the POST data
        error_log("Special Attendance POST data: " . print_r($_POST, true));
        
        // Handle both regular form submission and AJAX
        $inputData = file_get_contents('php://input');
        if (!empty($inputData)) {
            $jsonData = json_decode($inputData, true);
            if ($jsonData) {
                $_POST = array_merge($_POST, $jsonData);
            }
        }
        
        $students = $_POST['students'] ?? [];
        $date = $_POST['date'] ?? date('Y-m-d');
        $hours = 8; // Fixed at 8 hours
        
        if (empty($students)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No students selected']);
            exit();
        }
        
        // Debug: Log the processed data
        error_log("Processing special attendance for students: " . implode(', ', $students) . " on date: $date");
        
        $success = $this->attendanceModel->recordSpecialAttendance($students, $date, $hours);
        
        header('Content-Type: application/json');
        if ($success) {
            $studentCount = count($students);
            $message = "Special attendance recorded successfully for {$studentCount} intern(s). Attendance records (8:00 AM - 4:00 PM) and 'Work from home' journal entries have been created automatically.";
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record special attendance']);
        }
        exit();
    }
    /**
     * Show admin profile view
     */
    public function profile() {
        $student_id = $_GET['student_id'] ?? null;
        
        if (!$student_id) {
            header("Location: /attendance-system/admin/journals");
            exit();
        }

        $student = $this->userModel->getUserById($student_id);
        
        if (!$student) {
            header("HTTP/1.0 404 Not Found");
            exit("Student not found");
        }

        // Generate CSRF token for view compatibility
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Ensure 'user_id' key exists for view compatibility
        $user = $student;
        if (!isset($user['user_id']) && isset($user['id'])) {
            $user['user_id'] = $user['id'];
        }
        // Also ensure supervisor_notes is available
        $supervisor_notes = [];
        if (!empty($user['supervisor_notes'])) {
            $supervisor_notes = json_decode($user['supervisor_notes'], true);
        }
        // Load user profile view with admin context
        require_once APP_PATH . '/view/user/profile.php';
    }
}
