echo "
ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER email;
ALTER TABLE users ADD COLUMN password VARCHAR(255) NULL AFTER username;

CREATE TABLE IF NOT EXISTS journal_remarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journal_id INT NOT NULL,
    admin_id INT NOT NULL,
    remark TEXT NOT NULL,
    status ENUM('read', 'unread', 'feedback') NOT NULL DEFAULT 'read',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (journal_id) REFERENCES daily_journals(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);" > migration/add_admin_features.sql


"