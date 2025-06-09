<?php
/**
 * Root Entry Point - Redirects to public/index.php
 */

// Define base path
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');

// Set timezone to UTC+08:00
date_default_timezone_set('Asia/Singapore');

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load core files
require_once APP_PATH . '/core/Session.php';
require_once APP_PATH . '/core/Router.php';

// Start session
Session::start();

// Initialize router
$router = new Router();
$router->loadRoutes();
$router->handleRequest();