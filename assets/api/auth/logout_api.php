<?php
/**
 * Logout API Endpoint
 * GET /assets/api/auth/logout_api.php
 */
session_start();

// Destroy session
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to home page
header('Location: ../../../index.php');
exit;
?>
