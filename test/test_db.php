<?php
// test_pin.php
require_once __DIR__ . '/../temp/config/database.php';
require_once __DIR__ . '/app/model/UserModel.php';

$testPins = ['1234', '0000', '1111']; // Add your test PINs here

$userModel = new UserModel();

foreach ($testPins as $pin) {
    echo "<h3>Testing PIN: $pin</h3>";
    $user = $userModel->getUserByPin($pin);
    
    if ($user) {
        echo "<p>User found: " . htmlspecialchars($user['username']) . "</p>";
        echo "<pre>" . print_r($user, true) . "</pre>";
    } else {
        echo "<p style='color:red'>No user found with PIN: $pin</p>";
    }
}