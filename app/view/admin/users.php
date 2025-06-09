<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css">
</head>
<body>
    <div class="container">
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <main>
            <?php if (Session::has('_flash')): ?>
                <?php if (Session::getFlash('success')): ?>
                    <div class="alert success"><?php echo Session::getFlash('success'); ?></div>
                <?php endif; ?>
                <?php if (Session::getFlash('error')): ?>
                    <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="page-header">
                <h2>Manage Users</h2>
                <a href="/attendance-system/admin/users/create" class="btn primary">Create New User</a>
            </div>
            
            <div class="users-list">
                <?php if (empty($data['users'])): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>PIN</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['users'] as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['pin']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'intern' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
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
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>
</body>
</html>