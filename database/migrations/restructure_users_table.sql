-- Drop columns that are no longer needed
ALTER TABLE users DROP COLUMN IF EXISTS Name;
ALTER TABLE users DROP COLUMN IF EXISTS email;

-- Add full_name column
ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NOT NULL AFTER id;