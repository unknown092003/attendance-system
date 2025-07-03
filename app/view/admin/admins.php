<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML document setup and metadata -->
    <meta charset="UTF-8"> <!-- Character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport settings -->
    <title>Manage Admins - Attendance System</title> <!-- Page title -->
    
    <!-- CSS stylesheets -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css"> <!-- Admin-specific styles -->
</head>
<body>
    <!-- Main container for the page -->
    <div class="container">
        <!-- Include the admin header partial -->
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
                <h2>Manage Admins</h2> <!-- Page title -->
                <!-- Button to create new admin -->
                <a href="/attendance-system/admin/admins/create" class="btn primary">Create New Admin</a>
            </div>
            
            <!-- Admins list section -->
            <div class="admins-list">
                <?php if (empty($data['admins'])): ?>
                    <!-- Message shown when no admins exist -->
                    <p>No admins found.</p>
                <?php else: ?>
                    <!-- Table displaying admin information -->
                    <table>
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Username</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through each admin and display their information -->
                            <?php foreach ($data['admins'] as $admin): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($admin['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <!-- Format the creation date -->
                                    <td><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                                    <td>
                                        <!-- Form for deleting an admin -->
                                        <form action="/attendance-system/admin/admins/delete" method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
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
            <p>&copy; <?php echo date('Y'); ?> erroljohnpardillo@gmail.com </p>
        </footer>
    </div>
</body>
</html>