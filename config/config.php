<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL configuration
define('BASE_URL', 'http://localhost/sumindaStores');

// Database configuration (already in database.php, but keep these as constants)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'suminda_stores');

// Site configuration
define('SITE_NAME', 'සුමින්ද ස්ටෝර්ස්');
define('SITE_EMAIL', 'info@sumindastores.lk');
define('SITE_PHONE', '+94 77 123 4567');
define('SITE_ADDRESS', 'නෙගොඹ, ශ්‍රී ලංකාව');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ITEMS_UPLOAD_DIR', UPLOAD_DIR . 'items/');
define('ADS_UPLOAD_DIR', UPLOAD_DIR . 'ads/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
if (!file_exists(ITEMS_UPLOAD_DIR)) {
    mkdir(ITEMS_UPLOAD_DIR, 0777, true);
}
if (!file_exists(ADS_UPLOAD_DIR)) {
    mkdir(ADS_UPLOAD_DIR, 0777, true);
}

// Timezone
date_default_timezone_set('Asia/Colombo');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Helper function to redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Helper function to set flash message
function setMessage($message, $type = 'info') {
    $_SESSION[$type . '_message'] = $message;
}
?>