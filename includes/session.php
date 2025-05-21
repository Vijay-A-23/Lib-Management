<?php
/**
 * Session management for the Library Management System
 */

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set session message
 * 
 * @param string $message Message to store in session
 * @param string $type Type of message (success, error)
 * @return void
 */
function setSessionMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Display session message and clear it
 * 
 * @return string|null HTML for the message or null if no message
 */
function displaySessionMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        
        // Clear the message
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        if ($type === 'success') {
            return successMessage($message);
        } else {
            return errorMessage($message);
        }
    }
    return null;
}

/**
 * Check session timeout and logout if expired
 * 
 * @return void
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive >= SESSION_TIMEOUT) {
            // Session expired
            session_unset();
            session_destroy();
            redirect(APP_URL . '/login.php?timeout=1');
        }
    }
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

/**
 * Require user to be logged in, redirect if not
 * 
 * @param string $redirect URL to redirect to if not logged in
 * @return void
 */
function requireLogin($redirect = 'login.php') {
    if (!isLoggedIn()) {
        setSessionMessage('You must be logged in to access that page.', 'error');
        redirect(APP_URL . '/' . $redirect);
    }
    checkSessionTimeout();
}

/**
 * Require user to be an admin, redirect if not
 * 
 * @param string $redirect URL to redirect to if not admin
 * @return void
 */
function requireAdmin($redirect = 'index.php') {
    requireLogin('login.php');
    
    if (!isAdmin()) {
        setSessionMessage('You do not have permission to access that page.', 'error');
        redirect(APP_URL . '/' . $redirect);
    }
}

/**
 * Require user to be a regular user, redirect if not
 * 
 * @param string $redirect URL to redirect to if not a regular user
 * @return void
 */
function requireUser($redirect = 'index.php') {
    requireLogin('login.php');
    
    if (isAdmin()) {
        setSessionMessage('Admin users cannot access user features.', 'error');
        redirect(APP_URL . '/' . $redirect);
    }
}
?>
