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
        
        $data = [
            'userCount' => $this->userModel->countUsers(),
            'activeUsers' => $this->userModel->countActiveUsers(),
            'todayAttendance' => $this->attendanceModel->getTodayAttendanceCount(),
            'recentActivity' => $this->attendanceModel->getRecentActivity(5),
            'studentsToday' => $studentsToday,
            'internProgress' => $this->userModel->getInternProgress(),
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
        $data = [
            'users' => $this->userModel->getAllUsers()
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
            $pin = $_POST['pin'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $required_hours = !empty($_POST['required_hours']) ? (float)$_POST['required_hours'] : null;
            
            // Validate input
            $errors = [];
            
            if (empty($full_name)) {
                $errors[] = 'Full name is required';
            }
            
            if (empty($pin) || strlen($pin) !== 4 || !ctype_digit($pin)) {
                $errors[] = 'PIN must be 4 digits';
            }
            
            if (empty($errors)) {
                $userId = $this->userModel->createUser($full_name, $pin, $role, $required_hours);
                
                if ($userId) {
                    Session::setFlash('success', 'User created successfully');
                    header("Location: /attendance-system/admin/users");
                    exit();
                } else {
                    $errors[] = 'Failed to create user';
                }
            }
            
            // If we get here, there were errors
            Session::setFlash('errors', $errors);
        }
        
        require_once APP_PATH . '/view/admin/create_user.php';
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
            echo json_encode(['success' => true, 'message' => 'Special attendance recorded successfully']);
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