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

    </style>
    <div class="logo">
        <img src="\attendance-system\assets\img\ocd.png" class="logo-img">
        <h1 style="color:orange;">Internship Attendance </h1>
    </div>
    <div class="user-info">
        <a href="/attendance-system/logout" class="logout-btn">Logout</a>
    </div>
</header>

<nav class="admin-nav">
    <ul>
        <li><a href="/attendance-system/admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/attendance-system/admin/users"><i class="fas fa-users"></i> Users</a></li>
        <!-- <li><a href="/attendance-system/admin/reports"><i class="fas fa-chart-bar"></i> Reports</a></li> -->
        <li><a href="/attendance-system/admin/journals"><i class="fas fa-book"></i> Journals</a></li>
    </ul>
</nav>