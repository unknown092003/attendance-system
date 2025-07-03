<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Document metadata and settings -->
    <meta charset="UTF-8"> <!-- Sets character encoding to UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Makes page responsive on mobile devices -->
    <title>Create User - Attendance System</title> <!-- Page title shown in browser tab -->
    
    <!-- CSS stylesheets -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css"> <!-- Admin-specific styles -->
</head>
<body>
    <!-- Main page container -->
    <div class="container">
        <?php
        // Define application path if not already defined
        if (!defined('APP_PATH')) {
            define('APP_PATH', dirname(dirname(dirname(__DIR__))));
        }
        // Include the admin header navigation
        include APP_PATH . '/view/admin/partials/header.php';
        ?>
        
        <!-- Main content section -->
        <main>
            <!-- Page header with title and create button -->
            <div class="page-header">
                <h2>Create New User</h2> <!-- Form title -->
                <!-- Button to create new user (note: appears redundant on this page) -->
                <!-- <a href="/attendance-system/public/admin/create_user.php" class="btn primary">Create New User</a> -->
            </div>
            
            <!-- Error message display section -->
            <?php if (!empty($data['errors'])): ?>
                <div class="alert error">
                    <ul>
                        <!-- Loop through and display all validation errors -->
                        <?php foreach ($data['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li> <!-- XSS protection with htmlspecialchars -->
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Form container -->
            <div class="form-container">
                <!-- User creation form that submits to the create endpoint -->
                <form action="/attendance-system/admin/users/create" method="post">
                    <!-- Full name input field -->
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <!-- Input field with value preservation if form submission fails -->
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($data['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <!-- PIN will be auto-generated -->
                    <div class="form-group">
                        <label>PIN</label>
                        <p class="form-note">A unique 4-digit PIN will be automatically generated for this user.</p>
                    </div>
                    
                    <!-- Role selection dropdown -->
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control">
                            <!-- Options with selected state preserved -->
                            <!-- <option value="student" <?php echo (isset($data['role']) && $data['role'] === 'student') ? 'selected' : ''; ?>>Student</option> -->
                            <option value="intern" <?php echo (isset($data['role']) && $data['role'] === 'intern') ? 'selected' : ''; ?>>Intern</option>
                            <!-- <option value="admin" <?php echo (isset($data['role']) && $data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option> -->
                        </select>
                    </div>
                    
                    <!-- Required hours field (conditionally shown for interns) -->
                    <div class="form-group required-hours-group" style="display: none;">
                        <label for="required_hours">Required Hours</label>
                        <!-- Number input with decimal steps -->
                        <input type="number" id="required_hours" name="required_hours" class="form-control" 
                               value="<?php echo htmlspecialchars($data['required_hours'] ?? ''); ?>" 
                               step="0.01" min="0">
                        <small>Enter the total required hours for this intern</small>
                    </div>
                    
                    <!-- JavaScript for dynamic form behavior -->
                    <script>
                        // Show/hide required hours field based on role selection
                        document.getElementById('role').addEventListener('change', function() {
                            var requiredHoursGroup = document.querySelector('.required-hours-group');
                            if (this.value === 'intern') {
                                requiredHoursGroup.style.display = 'block';
                            } else {
                                requiredHoursGroup.style.display = 'none';
                            }
                        });
                        
                        // Initialize visibility on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            var roleSelect = document.getElementById('role');
                            if (roleSelect.value === 'intern') {
                                document.querySelector('.required-hours-group').style.display = 'block';
                            }
                        });
                    </script>
                    
                    <!-- Form submission button -->
                    <div class="form-group">
                        <button type="submit" class="btn primary">Create User</button>
                    </div>
                </form>
            </div>
        </main>
        
        <!-- Page footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> erroljohnpardillo@gmail.com </p>
        </footer>
    </div>
</body>
</html>