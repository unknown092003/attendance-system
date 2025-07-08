CREATE TABLE IF NOT EXISTS journal_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journal_id INT NOT NULL,
    admin_id INT NOT NULL,
    feedback_text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (journal_id) REFERENCES daily_journals(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    UNIQUE KEY unique_feedback_per_journal (journal_id)
); 