<header class="admin-header">
    <div class="logo">
        <h1>Attendance System</h1>
    </div>
    <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars(Session::get('username') ?? 'Admin'); ?></span>
        <a href="/attendance-system/logout" class="logout-btn">Logout</a>
    </div>
</header>

<nav class="admin-nav">
    <ul>
        <li><a href="/attendance-system/admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/attendance-system/admin/users"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="/attendance-system/admin/reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
        <li><a href="/attendance-system/admin/journals"><i class="fas fa-book"></i> Journals</a></li>
    </ul>
</nav>