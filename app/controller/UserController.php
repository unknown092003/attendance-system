<?php
// app/controller/UserController.php

class UserController {
    private $userModel;
    private $attendanceModel;
    
    public function __construct() {
        require_once APP_PATH . '/model/UserModel.php';
        $this->userModel = new UserModel();
        require_once APP_PATH . '/model/AttendanceModel.php';
        $this->attendanceModel = new AttendanceModel();
    }
    
    public function profile() {
        // Check if user is logged in (either regular user or admin)
        $isUser = isset($_SESSION['user_id']);
        $isAdmin = isset($_SESSION['admin_id']) && ($_SESSION['role'] ?? '') === 'admin';
        
        if (!$isUser && !$isAdmin) {
            header('Location: /login');
            exit;
        }
        
        // Determine which user profile to show
        $userId = $_SESSION['user_id'] ?? null;
        
        // If admin is viewing another user's profile
        if ($isAdmin && isset($_GET['student_id'])) {
            $userId = (int)$_GET['student_id'];
        }
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
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

            // Skip CSRF validation for now to debug
            // if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            //     throw new Exception('Invalid CSRF token', 403);
            // }

            // Verify authenticated user
            $userId = $_SESSION['user_id'] ?? null;
            $isAdmin = isset($_SESSION['admin_id']) && ($_SESSION['role'] ?? '') === 'admin';
            
            if (!$userId && !$isAdmin) {
                throw new Exception('Unauthorized access - no user session', 401);
            }

            // Get target user ID
            $targetUserId = $userId; // Default to current user
            
            // If admin is editing another user's profile
            if ($isAdmin && isset($_POST['target_user_id'])) {
                $targetUserId = (int)$_POST['target_user_id'];
            } elseif ($isAdmin && isset($_GET['student_id'])) {
                $targetUserId = (int)$_GET['student_id'];
            }
            
            if (!$targetUserId) {
                throw new Exception('No target user ID found', 400);
            }

            // Handle avatar upload
            $avatarFileName = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $avatarFileName = 'avatar_' . $targetUserId . '_' . time() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $avatarFileName;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                        // Avatar uploaded successfully
                    } else {
                        throw new Exception('Failed to upload avatar');
                    }
                } else {
                    throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                }
            }

            // Filter and sanitize input data
            $allowedFields = [
                'full_name', 'email', 'phone', 'university', 'college',
                'program', 'required_hours', 'supervisor', 'address'
            ];
            
            $userData = [];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $userData[$key] = $value;
                }
            }
            
            // Add avatar to userData if uploaded
            if ($avatarFileName) {
                $userData['avatar'] = $avatarFileName;
            }
            
            // Debug: Log the data being sent
            error_log('Update data: ' . print_r($userData, true));
            error_log('Target user ID: ' . $targetUserId);
            
            // Update through model
            $updated = $this->userModel->updateUser($targetUserId, $userData);
            
            if (!$updated) {
                throw new Exception('Failed to update profile in database', 500);
            }

            // Return success response
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
            exit;
                
        } catch (Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    public function updateJournal() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }

        $journalId = $_POST['journal_id'] ?? 0;
        $journalText = $_POST['journal_text'] ?? '';
        $sessionUserId = $_SESSION['user_id'] ?? 0;
        $targetUserId = $_POST['user_id'] ?? $sessionUserId; // Get target user ID from POST, default to session user
        
        $isAdmin = isset($_SESSION['admin_id']);

        if (!$journalId || !$targetUserId) {
            echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
            exit();
        }

        // Authorize: either admin, or user editing their own journal
        if (!$isAdmin && ($sessionUserId != $targetUserId)) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to perform this action.']);
            exit();
        }

        $success = $this->attendanceModel->updateJournal($journalId, $journalText, $targetUserId, $isAdmin);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Journal updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update journal.']);
        }
        exit();
    }
}