<?php
/**
 * Logout Page
 * Handles user logout and session cleanup
 */

require_once 'includes/app.php';

// Perform logout
logoutUser();

// Redirect to login page with message
redirect(SITE_URL . '/login.php', 'You have been successfully logged out.', 'info');
?>
