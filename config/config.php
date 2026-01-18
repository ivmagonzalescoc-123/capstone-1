<?php
/**
 * Database Configuration
 * Azucena Dental Clinic - Web-Based Appointment System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'azucena_dental');

// Application settings
define('SITE_NAME', 'Azucena Dental Clinic');
define('SITE_URL', 'http://localhost/capstone');
define('ADMIN_EMAIL', 'admin@azucenadentalclinic.com');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
