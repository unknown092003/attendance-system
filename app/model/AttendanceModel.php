<?php
require_once __DIR__ . '/../../config/database.php';

class AttendanceModel {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']}",
            $config['username'],
            $config['password'],
            $config['options']
        );
        
        // Set timezone to UTC+08:00
        date_default_timezone_set('Asia/Singapore');
    }
    
    public function recordAttendance($userId, $type) {
        // Use server's time directly from database to ensure consistency
        if ($type === 'in') {
            $stmt = $this->db->prepare("
                INSERT INTO attendance (user_id, time_in) 
                VALUES (?, NOW())
            ");
            $success = $stmt->execute([$userId]);
            
            if ($success) {
                // Get the exact server time that was recorded
                $stmt = $this->db->prepare("
                    SELECT time_in FROM attendance 
                    WHERE user_id = ? 
                    ORDER BY id DESC LIMIT 1
                ");
                $stmt->execute([$userId]);
                $record = $stmt->fetch();
                return ['success' => true, 'time_in' => $record['time_in']];
            }
            return ['success' => false];
        } else {
            // Find the latest time-in record without a time-out
            $stmt = $this->db->prepare("
                SELECT id FROM attendance 
                WHERE user_id = ? AND time_out IS NULL 
                ORDER BY time_in DESC LIMIT 1
            ");
            $stmt->execute([$userId]);
            $record = $stmt->fetch();
            
            if ($record) {
                // Use NOW() to get server time directly
                $stmt = $this->db->prepare("
                    UPDATE attendance 
                    SET time_out = NOW() 
                    WHERE id = ?
                ");
                $success = $stmt->execute([$record['id']]);
                
                if ($success) {
                    // Get the exact server time that was recorded
                    $stmt = $this->db->prepare("
                        SELECT time_out FROM attendance 
                        WHERE id = ?
                    ");
                    $stmt->execute([$record['id']]);
                    $updatedRecord = $stmt->fetch();
                    return ['success' => true, 'time_out' => $updatedRecord['time_out']];
                }
                return false;
            }
            
            return false;
        }
    }
    
    public function getActiveAttendance($userId) {
        // Get the timestamp of the last journal entry (which marks the end of the previous day)
        $stmt = $this->db->prepare("
            SELECT MAX(created_at) as last_end_day
            FROM daily_journals
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $lastEndDay = $stmt->fetch()['last_end_day'];
        
        // If no journal entries exist, use the beginning of time
        if (!$lastEndDay) {
            $lastEndDay = '1970-01-01 00:00:00';
        }
        
        // Get active attendance since the last end day
        $stmt = $this->db->prepare("
            SELECT *, 
                   UNIX_TIMESTAMP(time_in) * 1000 as time_in_ms,
                   UNIX_TIMESTAMP(NOW()) * 1000 as server_time_ms
            FROM attendance 
            WHERE user_id = ? 
            AND time_out IS NULL 
            AND time_in > ?
            ORDER BY time_in DESC LIMIT 1
        ");
        $stmt->execute([$userId, $lastEndDay]);
        return $stmt->fetch();
    }
    
    public function endDay($userId, $journal) {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        
        try {
            $this->db->beginTransaction();
            
            // Check if a journal entry already exists for today
            $stmt = $this->db->prepare("
                SELECT id FROM daily_journals 
                WHERE user_id = ? AND date = ?
            ");
            $stmt->execute([$userId, $today]);
            $existingJournal = $stmt->fetch();
            
            // Close any open attendance records for today
            $stmt = $this->db->prepare("
                UPDATE attendance 
                SET time_out = ? 
                WHERE user_id = ? AND DATE(time_in) = ? AND time_out IS NULL
            ");
            $stmt->execute([$now, $userId, $today]);
            
            // Handle journal entry
            if ($existingJournal) {
                // Update existing journal if needed
                if ($journal !== 'Journal already submitted') {
                    $stmt = $this->db->prepare("
                        UPDATE daily_journals 
                        SET journal_text = ?, created_at = ? 
                        WHERE id = ?
                    ");
                    $journalSuccess = $stmt->execute([$journal, $now, $existingJournal['id']]);
                } else {
                    $journalSuccess = true; // No need to update
                }
            } else {
                // Create new journal entry
                $stmt = $this->db->prepare("
                    INSERT INTO daily_journals (user_id, date, journal_text, created_at) 
                    VALUES (?, ?, ?, ?)
                ");
                $journalSuccess = $stmt->execute([$userId, $today, $journal, $now]);
            }
            
            $this->db->commit();
            return $journalSuccess;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("End day error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLastEndDayTimestamp($userId) {
        $stmt = $this->db->prepare("
            SELECT MAX(created_at) as last_end_day
            FROM daily_journals
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['last_end_day'] ?? '1970-01-01 00:00:00';
    }
    
    public function getDailyAttendance($date) {
        $stmt = $this->db->prepare("
            SELECT 
                a.user_id, u.full_name, u.role,
                MIN(a.time_in) as first_time_in,
                MAX(IFNULL(a.time_out, NOW())) as last_time_out
            FROM 
                attendance a
            JOIN 
                users u ON a.user_id = u.id
            WHERE 
                DATE(a.time_in) = ?
            GROUP BY 
                a.user_id, u.full_name, u.role
            ORDER BY 
                first_time_in DESC
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    public function getUserAttendance($userId, $startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT * FROM attendance 
            WHERE user_id = ? 
            AND DATE(time_in) BETWEEN ? AND ?
            ORDER BY time_in DESC
        ");
        $stmt->execute([$userId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user attendance for a specific date
     */
    public function getUserAttendanceByDate($userId, $date) {
        $stmt = $this->db->prepare("
            SELECT * FROM attendance 
            WHERE user_id = ? 
            AND DATE(time_in) = ?
            ORDER BY time_in ASC
        ");
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }
    
    public function getTotalAccumulatedTimeForToday($userId, $dayEnded = false) {
        // If day has been ended, return zero accumulated time
        if ($dayEnded) {
            return [
                'total_seconds' => 0,
                'first_time_in' => null,
                'latest_activity' => null
            ];
        }
        
        // Get the timestamp of the last journal entry (which marks the end of the previous day)
        $stmt = $this->db->prepare("
            SELECT MAX(created_at) as last_end_day
            FROM daily_journals
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $lastEndDay = $stmt->fetch()['last_end_day'];
        
        // If no journal entries exist, use the beginning of time
        if (!$lastEndDay) {
            $lastEndDay = '1970-01-01 00:00:00';
        }
        
        // Get all attendance records since the last end day
        $stmt = $this->db->prepare("
            SELECT 
                SUM(TIMESTAMPDIFF(SECOND, time_in, IFNULL(time_out, NOW()))) as total_seconds,
                MIN(time_in) as first_time_in,
                MAX(IFNULL(time_out, NOW())) as latest_activity
            FROM attendance 
            WHERE user_id = ? 
            AND time_in > ?
        ");
        $stmt->execute([$userId, $lastEndDay]);
        return $stmt->fetch();
    }
    
    public function getDailyJournal($userId, $date) {
        $stmt = $this->db->prepare("
            SELECT * FROM daily_journals 
            WHERE user_id = ? AND date = ?
            LIMIT 1
        ");
        $stmt->execute([$userId, $date]);
        return $stmt->fetch();
    }
    
    /**
     * Get count of unique students who attended today
     */
    public function getTodayAttendanceCount() {
        $today = date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT user_id) FROM attendance 
            WHERE DATE(time_in) = ?
        ");
        $stmt->execute([$today]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get recent activity for admin dashboard
     */
    public function getRecentActivity($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                a.*, u.full_name, u.role
            FROM 
                attendance a
            JOIN 
                users u ON a.user_id = u.id
            ORDER BY 
                a.time_in DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get monthly attendance report
     */
    public function getMonthlyReport($month) {
        // Extract year and month from the input (format: YYYY-MM)
        $parts = explode('-', $month);
        $year = $parts[0];
        $monthNum = $parts[1];
        
        // Calculate first and last day of the month
        $firstDay = "$year-$monthNum-01";
        $lastDay = date('Y-m-t', strtotime($firstDay));
        
        $stmt = $this->db->prepare("
            SELECT 
                a.*, u.full_name, u.role
            FROM 
                attendance a
            JOIN 
                users u ON a.user_id = u.id
            WHERE 
                DATE(a.time_in) BETWEEN ? AND ?
            ORDER BY 
                a.time_in ASC
        ");
        $stmt->execute([$firstDay, $lastDay]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get journals by date
     */
    public function getJournalsByDate($date) {
        $stmt = $this->db->prepare("
            SELECT 
                j.*, u.full_name, u.role
            FROM 
                daily_journals j
            JOIN 
                users u ON j.user_id = u.id
            WHERE 
                j.date = ?
            ORDER BY 
                j.created_at DESC
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get students who have attendance or journal entries on a specific date
     */
    public function getStudentsWithActivityOnDate($date) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT 
                u.id, u.full_name, u.role,
                CASE 
                    WHEN j.id IS NOT NULL THEN 1
                    ELSE 0
                END as has_journal
            FROM 
                users u
            LEFT JOIN 
                attendance a ON u.id = a.user_id AND DATE(a.time_in) = ?
            LEFT JOIN 
                daily_journals j ON u.id = j.user_id AND j.date = ?
            WHERE 
                (a.id IS NOT NULL OR j.id IS NOT NULL)
                AND u.role != 'admin'
            ORDER BY 
                u.full_name ASC
        ");
        $stmt->execute([$date, $date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Record special attendance for multiple students
     * 
     * @param array $studentIds Array of student IDs
     * @param string $date Date in Y-m-d format
     * @param float $hours Number of hours to record
     * @return bool Success or failure
     */
    public function recordSpecialAttendance($studentIds, $date, $hours) {
        if (empty($studentIds)) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($studentIds as $studentId) {
                // Create time in and time out entries - fixed at 8:00 AM to 4:00 PM
                $timeIn = $date . ' 08:00:00';
                $timeOut = $date . ' 16:00:00';
                
                // Check if student already has attendance for this date
                $stmt = $this->db->prepare("
                    SELECT id FROM attendance 
                    WHERE user_id = ? AND DATE(time_in) = ?
                ");
                $stmt->execute([$studentId, $date]);
                
                if ($stmt->rowCount() === 0) {
                    // Simple insert with just the required fields
                    $stmt = $this->db->prepare("
                        INSERT INTO attendance (user_id, time_in, time_out) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$studentId, $timeIn, $timeOut]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}