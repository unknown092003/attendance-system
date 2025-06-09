-- Add required_hours field to users table
ALTER TABLE users ADD COLUMN required_hours DECIMAL(10,2) DEFAULT NULL AFTER role;