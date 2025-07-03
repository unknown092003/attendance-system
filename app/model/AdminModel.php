<?php
require_once __DIR__ . '/../../config/database.php';

class AdminModel {
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
     * Get admin by username and password
     */
    public function getAdminByCredentials($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        
        return false;
    }
    
    /**
     * Get admin by ID
     */
    public function getAdminById($id) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create a new admin
     */
    public function createAdmin($firstname, $lastname, $username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO admins (firstname, lastname, username, password, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$firstname, $lastname, $username, $hashedPassword]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update admin
     */
    public function updateAdmin($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            // Special handling for password
            if ($field === 'password' && !empty($value)) {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        // Add ID to values array
        $values[] = $id;
        
        $stmt = $this->db->prepare("
            UPDATE admins 
            SET " . implode(', ', $fields) . " 
            WHERE id = ?
        ");
        
        return $stmt->execute($values);
    }
    
    /**
     * Delete admin
     */
    public function deleteAdmin($id) {
        $stmt = $this->db->prepare("DELETE FROM admins WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Change admin password
     */
    public function changePassword($adminId, $currentPassword, $newPassword) {
        // First verify current password
        $stmt = $this->db->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();
        
        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
            return false;
        }
        
        // Update with new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $adminId]);
    }
    
    /**
     * Get all admins
     */
    public function getAllAdmins() {
        $stmt = $this->db->query("SELECT * FROM admins ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
}