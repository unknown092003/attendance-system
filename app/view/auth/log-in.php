<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/login.css">

  
</head>
<body>
     <div class="logo-center">
        <img src="/attendance-system/assets/img/ocd.png" alt="Logo">

    <div class="container">
        <div class="login-container">
            <h2 class="login-title">Student Login</h2>
            
            <?php if (Session::getFlash('error')): ?>
                <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
            <?php endif; ?>
            
            <form action="/attendance-system/login" method="post">
                <div class="form-group">
                    <label for="pin">Enter your 4-digit PIN</label>
                    <input type="password" id="pin" name="pin" class="form-control" maxlength="4" pattern="[0-9]{4}" required>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="student-login">
                <a href="/attendance-system/admin-login">Admin Login</a>
            </div>
            
        </div>
    </div>
</body>
</html>