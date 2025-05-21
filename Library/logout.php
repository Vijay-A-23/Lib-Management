<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page with logout parameter
redirect(APP_URL . '/login.php?logout=1');
?>
