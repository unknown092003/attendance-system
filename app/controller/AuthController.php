<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/AdminModel.php';

class AuthController {
    private $userModel;
    private $adminModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->adminModel = new AdminModel();
    }
    
    /**
     * Show student login form
     */
    public function showLoginForm() {
        // If already logged in, redirect to home
        if (Session::get('user_id')) {
            if (Session::get('role') === 'admin') {
                header("Location: /attendance-system/admin");
            } else {
                header("Location: /attendance-system/home");
            }
            exit();
        }
        
        require_once APP_PATH . '/view/auth/log-in.php';
    }

    /**
     * Show admin login form
     */
    public function showAdminLoginForm() {
        // If already logged in as admin, redirect to admin dashboard
        if (Session::get('user_id') && Session::get('role') === 'admin') {
            header("Location: /attendance-system/admin");
            exit();
        }
        
        require_once APP_PATH . '/view/auth/admin-login.php';
    }

    /**
     * Handle student login
     */
    public function login() {
        // If already logged in, redirect to appropriate page
        if (Session::get('user_id')) {
            if (Session::get('role') === 'admin') {
                header("Location: /attendance-system/admin");
            } else {
                header("Location: /attendance-system/home");
            }
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pin = $_POST['pin'] ?? '';
            
            if (strlen($pin) !== 4 || !ctype_digit($pin)) {
                Session::setFlash('error', 'PIN must be 4 digits');
                header("Location: /attendance-system/login");
                exit();
            }

            $user = $this->userModel->getUserByPin($pin);
            
            if ($user) {
                // Don't allow admin login through student login form
                if ($user['role'] === 'admin') {
                    Session::setFlash('error', 'Please use the admin login page');
                    header("Location: /attendance-system/admin-login");
                    exit();
                }
                
                Session::set('user_id', $user['id']);
                Session::set('role', $user['role']);
                
                // Set a session flag to indicate fresh login
                Session::set('fresh_login', true);
                
                header("Location: /attendance-system/home");
                exit();
            } else {
                Session::setFlash('error', 'Invalid PIN');
                header("Location: /attendance-system/login");
                exit();
            }
        }
        
        // Show login form for GET requests
        require_once APP_PATH . '/view/auth/log-in.php';
    }
    
    /**
     * Handle admin login
     */
    public function adminLogin() {
        // If already logged in as admin, redirect to admin dashboard
        if (Session::get('admin_id')) {
            header("Location: /attendance-system/admin");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                Session::setFlash('error', 'Username and password are required');
                header("Location: /attendance-system/admin-login");
                exit();
            }

            $admin = $this->adminModel->getAdminByCredentials($username, $password);
            
            if ($admin) {
                Session::set('admin_id', $admin['id']);
                Session::set('admin_name', $admin['firstname'] . ' ' . $admin['lastname']);
                Session::set('admin_username', $admin['username']);
                
                header("Location: /attendance-system/admin");
                exit();
            } else {
                Session::setFlash('error', 'Invalid username or password');
                header("Location: /attendance-system/admin-login");
                exit();
            }
        }
        
        // Show admin login form for GET requests
        require_once APP_PATH . '/view/auth/admin-login.php';
    }

    /**
     * Handle logout
     */
    public function logout() {
        $isAdmin = Session::get('admin_id') !== null;
        Session::destroy();
        
        if ($isAdmin) {
            header("Location: /attendance-system/admin-login");
        } else {
            header("Location: /attendance-system/login");
        }
        exit();
    }
}