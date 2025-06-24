<?php
/**
 * Routes Configuration
 */

return [
    // Public routes
    '/' => ['controller' => 'AuthController', 'action' => 'showLoginForm'],
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/admin-login' => ['controller' => 'AuthController', 'action' => 'showAdminLoginForm'],
    '/admin-auth' => ['controller' => 'AuthController', 'action' => 'adminLogin'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],

    // Profile routes
    '/profile' => [
        'controller' => 'UserController',
        'action' => 'profile',
        'methods' => ['GET']
    ],
    '/profile/update' => [
        'controller' => 'UserController',
        'action' => 'update_Profile',
        'methods' => ['POST']
    ],
    
    // User routes
    '/home' => ['controller' => 'AttendanceController', 'action' => 'showHome', 'auth' => true],
    '/profile' => ['controller' => 'UserController', 'action' => 'profile', 'auth' => true],
    '/time-in' => ['controller' => 'AttendanceController', 'action' => 'timeIn', 'auth' => true],
    '/time-out' => ['controller' => 'AttendanceController', 'action' => 'timeOut', 'auth' => true],
    '/end-day' => ['controller' => 'AttendanceController', 'action' => 'endDay', 'auth' => true],
    '/active-attendance' => ['controller' => 'AttendanceController', 'action' => 'getActiveAttendance', 'auth' => true],
    '/get-timer-state' => ['controller' => 'AttendanceController', 'action' => 'getTimerState', 'auth' => true],
    '/start-timer' => ['controller' => 'AttendanceController', 'action' => 'startTimer', 'auth' => true],
    '/pause-timer' => ['controller' => 'AttendanceController', 'action' => 'pauseTimer', 'auth' => true],
    '/reset-timer' => ['controller' => 'AttendanceController', 'action' => 'resetTimer', 'auth' => true],
    
    // Admin routes
    '/admin' => ['controller' => 'AdminController', 'action' => 'dashboard', 'admin' => true],
    '/admin/users' => ['controller' => 'AdminController', 'action' => 'users', 'admin' => true],
    '/admin/users/create' => ['controller' => 'AdminController', 'action' => 'createUser', 'admin' => true],
    '/admin/users/delete' => ['controller' => 'AdminController', 'action' => 'deleteUser', 'admin' => true],
    '/admin/admins' => ['controller' => 'AdminController', 'action' => 'admins', 'admin' => true],
    '/admin/admins/create' => ['controller' => 'AdminController', 'action' => 'createAdmin', 'admin' => true],
    '/admin/admins/delete' => ['controller' => 'AdminController', 'action' => 'deleteAdmin', 'admin' => true],
    '/admin/reports' => ['controller' => 'AdminController', 'action' => 'reports', 'admin' => true],
    '/admin/journals' => ['controller' => 'AdminController', 'action' => 'viewJournals', 'admin' => true],
    '/admin/special-attendance' => ['controller' => 'AdminController', 'action' => 'specialAttendance', 'admin' => true],
];


