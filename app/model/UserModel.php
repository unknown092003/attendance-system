<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']}",
            $config['username'],
            $config['password'],
            $config['options']
        );
    }
    
    /**
     * Get user by PIN (for student login)
     */
    public function getUserByPin($pin) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE pin = ? LIMIT 1");
        $stmt->execute([$pin]);
        return $stmt->fetch();
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create a new user
     */
    public function createUser($full_name, $pin, $role = 'user', $required_hours = null) {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, pin, role, required_hours, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$full_name, $pin, $role, $required_hours]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update user
     */
    public function updateUser($id, $data) {
        // Define allowed fields for security
        $allowedFields = [
            'full_name', 'email', 'phone', 'university', 'college',
            'program', 'year_level', 'internship_start', 'internship_end',
            'required_hours', 'supervisor', 'address', 'moa', 'status', 'avatar',
            'role', 'pin', 'university', 'college', 'program', 'year_level',
            'internship_start', 'internship_end'
        ];
        
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            // Only update allowed fields
            if (!in_array($field, $allowedFields)) {
                continue;
            }
            
            // Special handling for password
            if ($field === 'password' && !empty($value)) {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            
            // Handle boolean fields
            if ($field === 'moa') {
                $value = (int)$value;
            }
            
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        // Add ID to values array
        $values[] = $id;
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET " . implode(', ', $fields) . " 
            WHERE id = ?
        ");
        
        return $stmt->execute($values);
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id) {
        try {
            $this->db->beginTransaction();
            
            // First delete related attendance records
            $stmt = $this->db->prepare("DELETE FROM attendance WHERE user_id = ?");
            $stmt->execute([$id]);
            
            // Then delete related journal entries
            $stmt = $this->db->prepare("DELETE FROM daily_journals WHERE user_id = ?");
            $stmt->execute([$id]);
            
            // Finally delete the user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users
     * 
     * @param array $filters Optional filters like ['role' => 'student']
     * @return array Array of users
     */
    public function getAllUsers($filters = []) {
        $sql = "SELECT * FROM users";
        $params = [];
        
        // Apply filters if provided
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $field => $value) {
                $conditions[] = "$field = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY full_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get today's attendance for a user
     */
    public function getTodayAttendance($userId) {
        $today = date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT * FROM attendance 
            WHERE user_id = ? AND DATE(time_in) = ? 
            ORDER BY time_in DESC
        ");
        
        $stmt->execute([$userId, $today]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get month's attendance for a user
     */
    public function getMonthAttendance($userId) {
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        
        $stmt = $this->db->prepare("
            SELECT * FROM attendance 
            WHERE user_id = ? AND DATE(time_in) BETWEEN ? AND ? 
            ORDER BY time_in DESC
        ");
        
        $stmt->execute([$userId, $firstDayOfMonth, $lastDayOfMonth]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get total hours completed by a user
     */
    public function getTotalHoursCompleted($userId) {
        $stmt = $this->db->prepare("
            SELECT SUM(
                TIMESTAMPDIFF(SECOND, time_in, IFNULL(time_out, NOW())) / 3600
            ) as total_hours
            FROM attendance 
            WHERE user_id = ? AND time_out IS NOT NULL
        ");
        
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? (float)$result['total_hours'] : 0;
    }
    
    /**
     * Get progress data for all interns
     */
    public function getInternProgress() {
        $stmt = $this->db->prepare("
            SELECT 
                u.id, 
                u.full_name, 
                u.required_hours,
                COALESCE(
                    (SELECT SUM(TIMESTAMPDIFF(SECOND, a.time_in, a.time_out) / 3600)
                     FROM attendance a
                     WHERE a.user_id = u.id AND a.time_out IS NOT NULL
                    ), 0
                ) as completed_hours
            FROM 
                users u
            WHERE 
                u.required_hours IS NOT NULL
            ORDER BY 
                u.full_name ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Count total users
     */
    public function countUsers() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
        return $stmt->fetchColumn();
    }
    
    /**
     * Count active users (users who logged in today)
     */
    public function countActiveUsers() {
        $today = date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT user_id) FROM attendance 
            WHERE DATE(time_in) = ?
        ");
        
        $stmt->execute([$today]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Generate a unique 4-digit PIN
     */
    public function generateUniquePin() {
        $maxAttempts = 100;
        $attempt = 0;
        
        do {
            // Generate random 4-digit PIN
            $pin = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Check if PIN already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE pin = ?");
            $stmt->execute([$pin]);
            $exists = $stmt->fetchColumn() > 0;
            
            $attempt++;
            
            // If PIN doesn't exist, return it
            if (!$exists) {
                return $pin;
            }
            
        } while ($attempt < $maxAttempts);
        
        // If we couldn't generate a unique PIN after max attempts, throw an error
        throw new Exception("Unable to generate unique PIN after {$maxAttempts} attempts");
    }
}