-- Add profile fields to users table
ALTER TABLE users
ADD COLUMN about_me TEXT AFTER required_hours,
ADD COLUMN experience JSON AFTER about_me,
ADD COLUMN education JSON AFTER experience,
ADD COLUMN profile_picture VARCHAR(255) AFTER education;