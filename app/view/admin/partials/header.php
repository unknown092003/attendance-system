<header class="admin-header">
    <style>
    .logo-img{
        max-width: 100px;
        height: auto;
        min-width: none;
    }
    .logo{
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    /* Account Dropdown Styles */
    .account-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .account-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .account-btn:hover {
        background: #0056b3;
    }
    
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        min-width: 200px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 4px;
        z-index: 1000;
        margin-top: 5px;
    }
    
    .dropdown-menu.show {
        display: block;
    }
    
    .dropdown-menu a {
        display: block;
        padding: 12px 16px;
        text-decoration: none;
        color: #333;
        border-bottom: 1px solid #eee;
    }
    
    .dropdown-menu a:hover {
        background: #f8f9fa;
    }
    
    .dropdown-menu a:last-child {
        border-bottom: none;
    }
    
    .dropdown-menu i {
        margin-right: 8px;
        width: 16px;
    }
    
    /* Change Password Modal */
    .password-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
    }
    
    .password-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #001938;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        max-width: 90%;
    }
    
    .password-form-group {
        margin-bottom: 15px;
    }
    
    .password-form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .password-form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    
    .password-btn {
        padding: 8px 16px;
        margin-right: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .password-btn.primary {
        background: #007bff;
        color: white;
    }
    
    .password-btn.secondary {
        background: #6c757d;
        color: white;
    }

    </style>
    
    <script>
        function toggleAccountMenu() {
            const menu = document.getElementById('accountMenu');
            menu.classList.toggle('show');
        }
        
        function openChangePasswordModal() {
            document.getElementById('passwordModal').style.display = 'block';
            toggleAccountMenu();
        }
        
        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
            document.getElementById('passwordForm').reset();
        }
        
        function submitPasswordChange() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                alert('Please fill in all fields');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }
            
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long');
                return;
            }
            
            const formData = new FormData();
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);
            
            fetch('/attendance-system/admin/change-password', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password changed successfully!');
                    closePasswordModal();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while changing password');
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.account-dropdown');
            if (!dropdown.contains(event.target)) {
                document.getElementById('accountMenu').classList.remove('show');
            }
        });
    </script>
    <div class="logo">
        <img src="\attendance-system\assets\img\ocd.png" class="logo-img">
        <h1 style="color:orange;">Internship Attendance </h1>
    </div>
    <div class="user-info">
        <div class="account-dropdown">
            <button class="account-btn" onclick="toggleAccountMenu()">Account <i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-menu" id="accountMenu">
                <a href="/attendance-system/admin/admins/create"><i class="fas fa-user-plus"></i> Create New Admin</a>
                <a href="#" onclick="openChangePasswordModal()"><i class="fas fa-key"></i> Change Password</a>
                <a href="/attendance-system/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</header>

<!-- Change Password Modal -->
<div id="passwordModal" class="password-modal">
    <div class="password-modal-content">
        <h3>Change Password</h3>
        <form id="passwordForm">
            <div class="password-form-group">
                <label for="currentPassword">Current Password:</label>
                <input type="password" id="currentPassword" required>
            </div>
            <div class="password-form-group">
                <label for="newPassword">New Password:</label>
                <input type="password" id="newPassword" required>
            </div>
            <div class="password-form-group">
                <label for="confirmPassword">Confirm New Password:</label>
                <input type="password" id="confirmPassword" required>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="password-btn secondary" onclick="closePasswordModal()">Cancel</button>
                <button type="button" class="password-btn primary" onclick="submitPasswordChange()">Change Password</button>
            </div>
        </form>
    </div>
</div>

<nav class="admin-nav">
    <ul>
        <li><a href="/attendance-system/admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/attendance-system/admin/users"><i class="fas fa-users"></i> Users</a></li>
        <!-- <li><a href="/attendance-system/admin/reports"><i class="fas fa-chart-bar"></i> Reports</a></li> -->
        <li><a href="/attendance-system/admin/journals"><i class="fas fa-book"></i> Journals</a></li>
    </ul>
</nav>