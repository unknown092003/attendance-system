-- Migrate existing admin users to the new admins table
INSERT INTO admins (firstname, lastname, username, password, created_at)
SELECT 
    SUBSTRING_INDEX(name, ' ', 1) AS firstname,
    SUBSTRING_INDEX(name, ' ', -1) AS lastname,
    username,
    password,
    created_at
FROM users
WHERE role = 'admin' AND username IS NOT NULL AND password IS NOT NULL;

-- Remove admin users from the users table
DELETE FROM users WHERE role = 'admin';