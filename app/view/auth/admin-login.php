<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/login.css">
    
</head>
<body>
    <div class="logo-center">
        <img src="/attendance-system/assets/img/ocd.png" alt="Logo">
        
    </div>
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">Admin Login</h2>
            <?php if (Session::getFlash('error')): ?>
                <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
            <?php endif; ?>
            <form action="/attendance-system/admin-auth" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            <div class="student-login">
                <span>Not an admin?</span>
                <a href="/attendance-system/login">Student Login</a>
            </div>
        </div>
    </div>
</body>
</html>