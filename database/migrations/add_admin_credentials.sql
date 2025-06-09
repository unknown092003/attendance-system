-- Add password field to users table
ALTER TABLE users ADD COLUMN password VARCHAR(255) NULL AFTER pin;

-- Update existing admin users or create a new admin if none exists
-- Default admin credentials: username: admin, password: admin123
INSERT INTO users (username, pin, password, role, created_at)
VALUES ('admin', '0000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW())
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Add unique constraint on username for admin users
ALTER TABLE users ADD UNIQUE INDEX idx_username (username);