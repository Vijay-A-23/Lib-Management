<?php
/**
 * Configuration settings for the Library Management System
 */

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration (MySQL/MariaDB for XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_db'); // Change if you used a different DB name
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password is empty

// Application configuration
define('APP_NAME', 'Library Management System');
define('APP_URL', 'http://localhost/Library'); // Full path to your app in browser

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Default admin credentials (used only for initial setup, not login!)
define('DEFAULT_ADMIN_EMAIL', 'admin@library.com');
define('DEFAULT_ADMIN_PASSWORD', 'admin123');
?>
