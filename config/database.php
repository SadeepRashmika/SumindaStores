<?php

// Database configuration (prevent redefinition)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'suminda_stores');

// Function to get database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset to UTF-8 for Sinhala support
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Create global connection (for backward compatibility)
if (!isset($conn)) {
    $conn = getDBConnection();
}

// Function to sanitize input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check user role
function check_role($required_role) {
    if (!isset($_SESSION['role'])) {
        return false;
    }

    $roles = ['customer' => 1, 'seller' => 2, 'admin' => 3];
    $user_level = $roles[$_SESSION['role']] ?? 0;  // Fixed typo: removed extra $
    $required_level = $roles[$required_role] ?? 0;

    return $user_level >= $required_level;
}

// Redirect if not authorized
function require_role($required_role) {
    if (!is_logged_in()) {
        header("Location: /login.php");
        exit();
    }

    if (!check_role($required_role)) {
        header("Location: /index.php");
        exit();
    }
}
?>