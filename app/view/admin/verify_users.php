<?php
// File: app/view/admin/verify_users.php
// Purpose: Admin-only page to view and verify registered users in the system
// Security: Contains access control and output sanitization

// SECURITY CHECK: Verify the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // If not admin, redirect to login page immediately
    header('Location: /login');
    exit; // Stop further script execution
}

// Include the admin header partial file
// This typically contains navigation, CSS, JS, and opening HTML tags
include APP_PATH . '/view/admin/partials/header.php';
?>

<!-- Main page container with top margin -->
<div class="container mt-4">
    <!-- Page heading -->
    <h2>User Verification</h2>
    
    <!-- Users table with bordered styling -->
    <table class="table table-bordered">
        <!-- Table header row -->
        <thead>
            <tr>
                <th>ID</th>         <!-- Unique user identifier -->
                <th>Username</th>   <!-- User's login name -->
                <th>PIN</th>        <!-- 4-digit security code -->
                <th>Role</th>       <!-- User's permission level -->
                <th>Created</th>    <!-- Account creation timestamp -->
            </tr>
        </thead>
        
        <!-- Table body with user data -->
        <tbody>
            <?php foreach ($users as $user): ?>
            <!-- Table row for each user -->
            <tr>
                <!-- User ID (sanitized output) -->
                <td><?= htmlspecialchars($user['id']) ?></td>
                
                <!-- Username (sanitized output) -->
                <td><?= htmlspecialchars($user['username']) ?></td>
                
                <!-- PIN code (sanitized output) -->
                <td><?= htmlspecialchars($user['pin']) ?></td>
                
                <!-- User role (sanitized output) -->
                <td><?= htmlspecialchars($user['role']) ?></td>
                
                <!-- Creation date (sanitized output) -->
                <td><?= htmlspecialchars($user['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Success alert showing database connection status -->
    <!-- Note: In production, you might want to make this conditional -->
    <div class="alert alert-success mt-4">
        Database connection successful!
    </div>
</div>

<?php
// Note: Uncomment to include footer if available
// Typically contains closing HTML tags and scripts
// include APP_PATH . '/view/admin/partials/footer.php';
?>