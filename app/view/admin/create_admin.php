<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Document metadata and settings -->
    <meta charset="UTF-8"> <!-- Sets character encoding to UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Makes page responsive on mobile devices -->
    <title>Create Admin - Attendance System</title> <!-- Page title shown in browser tab -->
    
    <!-- CSS stylesheets -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css"> <!-- Admin-specific styles -->
</head>
<body>
    <!-- Main page container -->
    <div class="container">
        <!-- Include the admin header navigation -->
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <!-- Main content section -->
        <main>
            <!-- Page header with title and back button -->
            <div class="page-header">
                <h2>Create New Admin</h2> <!-- Form title -->
                <!-- Back button to return to admin list -->
                <a href="/attendance-system/public/admin/admins" class="btn secondary">Back to Admins</a>
            </div>
            
            <!-- Error message display section -->
            <?php if (!empty($data['errors'])): ?>
                <div class="alert error">
                    <ul>
                        <!-- Loop through and display all validation errors -->
                        <?php foreach ($data['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Form container -->
            <div class="form-container">
                <!-- Admin creation form that submits to the create endpoint -->
                <form action="/attendance-system/public/admin/admins/create" method="post">
                    <!-- First name input field -->
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <!-- Input field with value preservation if form submission fails -->
                        <input type="text" id="firstname" name="firstname" class="form-control" 
                               value="<?php echo htmlspecialchars($data['firstname'] ?? ''); ?>" required>
                    </div>
                    
                    <!-- Last name input field -->
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" class="form-control" 
                               value="<?php echo htmlspecialchars($data['lastname'] ?? ''); ?>" required>
                    </div>
                    
                    <!-- Username input field -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" required>
                    </div>
                    
                    <!-- Password input field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <!-- Password confirmation field -->
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <!-- Form submission button -->
                    <div class="form-group">
                        <button type="submit" class="btn primary">Create Admin</button>
                    </div>
                </form>
            </div>
        </main>
        
        <!-- Page footer -->
        <footer>
            <!-- Dynamic copyright year -->
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>
</body>
</html>