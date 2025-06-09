<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css">
</head>
<body>
    <div class="container">
        <?php
        if (!defined('APP_PATH')) {
            define('APP_PATH', dirname(dirname(dirname(__DIR__))));
        }
        include APP_PATH . '/view/admin/partials/header.php';
        ?>
        
        <main>
            <div class="page-header">
                <h2>Create New User</h2>
                <a href="/attendance-system/public/admin/create_user.php" class="btn primary">Create New User</a>

            </div>
            
            <?php if (!empty($data['errors'])): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($data['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form action="/attendance-system/admin/users/create" method="post">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($data['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pin">PIN (4 digits)</label>
                        <input type="text" id="pin" name="pin" class="form-control" value="<?php echo htmlspecialchars($data['pin'] ?? ''); ?>" maxlength="4" pattern="[0-9]{4}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control">
                            <option value="student" <?php echo (isset($data['role']) && $data['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="intern" <?php echo (isset($data['role']) && $data['role'] === 'intern') ? 'selected' : ''; ?>>Intern</option>
                            <option value="admin" <?php echo (isset($data['role']) && $data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group required-hours-group" style="display: none;">
                        <label for="required_hours">Required Hours</label>
                        <input type="number" id="required_hours" name="required_hours" class="form-control" value="<?php echo htmlspecialchars($data['required_hours'] ?? ''); ?>" step="0.01" min="0">
                        <small>Enter the total required hours for this intern</small>
                    </div>
                    
                    <script>
                        document.getElementById('role').addEventListener('change', function() {
                            var requiredHoursGroup = document.querySelector('.required-hours-group');
                            if (this.value === 'intern') {
                                requiredHoursGroup.style.display = 'block';
                            } else {
                                requiredHoursGroup.style.display = 'none';
                            }
                        });
                        
                        // Initialize on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            var roleSelect = document.getElementById('role');
                            if (roleSelect.value === 'intern') {
                                document.querySelector('.required-hours-group').style.display = 'block';
                            }
                        });
                    </script>
                    
                    <div class="form-group">
                        <button type="submit" class="btn primary">Create User</button>
                    </div>
                </form>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>
</body>
</html>