<?php
/**
 * Application Settings
 */

return [
    'app_name' => 'Attendance System',
    'app_url' => 'http://localhost/attendance-system',
    'timezone' => 'Asia/Singapore', // UTC+08:00
    'debug' => true,
    'session' => [
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true
    ],
    'mail' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'user@example.com',
        'password' => 'password',
        'encryption' => 'tls',
        'from_address' => 'noreply@example.com',
        'from_name' => 'Attendance System'
    ]
];