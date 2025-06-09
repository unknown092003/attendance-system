-- Insert default admin account
-- Name: Errol John Pardillo, Username: errolpardillo, Password: 12345678
INSERT INTO admins (firstname, lastname, username, password, created_at)
VALUES ('Errol John', 'Pardillo', 'errolpardillo', '$2y$10$FXOtYb0XkP/xP5As/vB4euvxpxckLvf3J1AQJ2wV94An8gV2JTR2q', NOW())
ON DUPLICATE KEY UPDATE 
    firstname = 'Errol John',
    lastname = 'Pardillo',
    password = '$2y$10$FXOtYb0XkP/xP5As/vB4euvxpxckLvf3J1AQJ2wV94An8gV2JTR2q';