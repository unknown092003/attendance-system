<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Document metadata and settings -->
    <meta charset="UTF-8"> <!-- Sets character encoding to UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Makes page responsive on mobile devices -->
    <title>Manage Users - Attendance System</title> <!-- Page title shown in browser tab -->
    
    <!-- CSS stylesheets -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css"> <!-- Admin-specific styles -->
</head>
<body>
    <!-- Main page container -->
    <div class="container">
        <!-- Include admin header navigation -->
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <!-- Main content area -->
        <main>
            <!-- Flash message display section -->
            <?php if (Session::has('_flash')): ?>
                <!-- Success message display -->
                <?php if (Session::getFlash('success')): ?>
                    <div class="alert success"><?php echo Session::getFlash('success'); ?></div>
                <?php endif; ?>
                <!-- Error message display -->
                <?php if (Session::getFlash('error')): ?>
                    <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Page header section with title and create button -->
            <div class="page-header">
                <h2>Manage Users</h2> <!-- Page title -->
                <!-- Button to create new user -->
                <a href="/attendance-system/admin/users/create" class="btn primary">Create New User</a>
            </div>
            
            <!-- Users list section -->
            <div class="users-list">
                <?php if (empty($data['users'])): ?>
                    <!-- Message shown when no users exist -->
                    <p>No users found.</p>
                <?php else: ?>
                    <!-- Users table -->
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th> <!-- Column header -->
                                <th>PIN</th> <!-- Column header -->
                                <th>Role</th> <!-- Column header -->
                                <th>Actions</th> <!-- Column header -->
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through each user and display their information -->
                            <?php foreach ($data['users'] as $user): ?>
                                <tr>
                                    <!-- User's full name (escaped for security) -->
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <!-- User's PIN (escaped for security) -->
                                    <td><?php echo htmlspecialchars($user['pin']); ?></td>
                                    <!-- User's role with colored badge -->
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'intern' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                    </td>
                                    <!-- Action buttons -->
                                    <td>
                                        <!-- Delete user form with confirmation dialog -->
                                        <form action="/attendance-system/admin/users/delete" method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-sm danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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