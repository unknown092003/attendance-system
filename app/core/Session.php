<?php
/**
 * Session Class
 * Handles session management
 */
class Session {
    /**
     * Start the session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Set a session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session value
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if a session key exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session key
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Destroy the session
     */
    public static function destroy() {
        session_unset();
        session_destroy();
    }
    
    /**
     * Flash message system
     */
    public static function setFlash($key, $message) {
        $_SESSION['_flash'][$key] = $message;
    }
    
    /**
     * Get flash message and remove it
     */
    public static function getFlash($key, $default = null) {
        $message = $_SESSION['_flash'][$key] ?? $default;
        if (isset($_SESSION['_flash'][$key])) {
            unset($_SESSION['_flash'][$key]);
        }
        return $message;
    }
}