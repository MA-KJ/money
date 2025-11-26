<?php
/**
 * Index Page - Entry Point
 * Redirects users to dashboard if logged in, otherwise to landing page
 */

require_once 'includes/app.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard
    redirect(SITE_URL . '/dashboard.php');
} else {
    // Redirect to landing page
    redirect(SITE_URL . '/landing.php');
}
?>
