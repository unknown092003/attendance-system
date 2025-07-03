-- Add email column back to users table
ALTER TABLE users ADD COLUMN email VARCHAR(255) AFTER full_name;