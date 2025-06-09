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
            header('Location: /attendance-system/login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            // Handle error - user not found
            header('Location: /attendance-system/login');
            exit;
        }
        
        // Load profile view
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
}