<?php
// app/controller/UserController.php

class UserController {
    private $userModel;
    
    public function __construct() {
        require_once APP_PATH . '/model/UserModel.php';
        $this->userModel = new UserModel();
    }
    
    public function profile() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            header('Location: /login');
            exit;
        }
        
        // Generate and store CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Load profile view with CSRF token
        include APP_PATH . '/view/user/profile.php';
    }
    
    public function verifyUsers() {
        // Admin only function to verify users in the system
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /attendance-system/login');
            exit;
        }
        
        $users = $this->userModel->getAllUsers();
        include APP_PATH . '/view/admin/verify_users.php';
    }
public function updateProfile() {
    header('Content-Type: application/json');
    
    try {
        // Verify request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method', 405);
        }

        // Validate CSRF token
        if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token', 403);
        }

        // Verify authenticated user
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Unauthorized access', 401);
        }

        // Filter and sanitize input data
        $allowedFields = [
            'full_name', 'email', 'phone', 'university', 'college',
            'student_number', 'program', 'year_level', 'internship_start',
            'internship_end', 'required_hours', 'supervisor_name', 'supervisor_email'
        ];
        
        $userData = array_filter(
            $_POST,
            fn($key) => in_array($key, $allowedFields),
            ARRAY_FILTER_USE_KEY
        );

        // Update through model
        $updated = $this->userModel->updateUser($_SESSION['user_id'], $userData);
        
        if (!$updated) {
            throw new Exception('Failed to update profile', 500);
        }

        // Return updated user data
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $this->userModel->getUserById($_SESSION['user_id'])
        ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}