<?php
// app/view/admin/verify_users.php
// Secure admin page to verify users in the system

// Ensure this page is only accessible to admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

// Include header
include APP_PATH . '/view/admin/partials/header.php';
?>

<div class="container mt-4">
    <h2>User Verification</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>PIN</th>
                <th>Role</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['pin']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="alert alert-success mt-4">
        Database connection successful!
    </div>
</div>

<?php
// Include footer if you have one
// include APP_PATH . '/view/admin/partials/footer.php';
?>