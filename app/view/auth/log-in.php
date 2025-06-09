<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        
        .btn-login:hover {
            background-color: #45a049;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .admin-login {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .admin-login a {
            color: #2196F3;
            text-decoration: none;
        }
        
        .admin-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
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
            
            <div class="admin-login">
                <a href="/attendance-system/admin-login">Admin Login</a>
            </div>
        </div>
    </div>
</body>
</html>