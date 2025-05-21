<?php
/**
 * Helper functions for the Library Management System
 */

/**
 * Redirect to a specified URL
 * 
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Display a success message
 * 
 * @param string $message The message to display
 * @return string HTML formatted message
 */
function successMessage($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
              <strong>Success!</strong> ' . htmlspecialchars($message) . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Display an error message
 * 
 * @param string $message The message to display
 * @return string HTML formatted message
 */
function errorMessage($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
              <strong>Error!</strong> ' . htmlspecialchars($message) . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Clean input data to prevent XSS attacks
 * 
 * @param string $data Data to be cleaned
 * @return string Cleaned data
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
}

/**
 * Format date in a readable format
 * 
 * @param string $date MySQL date string
 * @return string Formatted date
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Calculate days between two dates
 * 
 * @param string $date1 First date
 * @param string $date2 Second date (default: current date)
 * @return int Number of days
 */
function daysBetween($date1, $date2 = null) {
    $date1 = new DateTime($date1);
    $date2 = $date2 ? new DateTime($date2) : new DateTime();
    $interval = $date1->diff($date2);
    return $interval->days;
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the random string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Display a confirmation dialog
 * 
 * @param string $message Confirmation message
 * @param string $confirmUrl URL to redirect to if confirmed
 * @param string $cancelUrl URL to redirect to if canceled
 * @return string HTML for the confirmation dialog
 */
function confirmDialog($message, $confirmUrl, $cancelUrl) {
    return '<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">' . htmlspecialchars($message) . '</div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="' . htmlspecialchars($confirmUrl) . '" class="btn btn-danger">Confirm</a>
                  </div>
                </div>
              </div>
            </div>';
}
?>
