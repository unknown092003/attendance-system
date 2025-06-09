<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css">
</head>
<body>
    <div class="container">
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Create New Admin</h2>
                <a href="/attendance-system/public/admin/admins" class="btn secondary">Back to Admins</a>
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
                <form action="/attendance-system/public/admin/admins/create" method="post">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" class="form-control" value="<?php echo htmlspecialchars($data['firstname'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" class="form-control" value="<?php echo htmlspecialchars($data['lastname'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn primary">Create Admin</button>
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