<?php
/**
 * Main Application Initialization
 * Common includes and initialization for all pages
 */

// Start secure session
require_once 'config.php';
require_once 'security.php';
secureSessionStart();

// Check session timeout for authenticated users
if (isLoggedIn()) {
    checkSessionTimeout();
}

// Include other necessary files
require_once 'database.php';
require_once 'auth.php';

/**
 * Common functions
 */

/**
 * Get application setting
 */
function getSetting($key, $default = '') {
    global $db;
    
    try {
        $setting = $db->fetch(
            "SELECT setting_value FROM settings WHERE setting_key = ?",
            [$key]
        );
        
        return $setting ? $setting['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    $symbol = getSetting('currency_symbol', 'K');
    return $symbol . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = null) {
    if (!$format) {
        $format = getSetting('date_format', 'Y-m-d');
    }
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

/**
 * Calculate days between dates
 */
function daysBetween($date1, $date2) {
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    return $d1->diff($d2)->days;
}

/**
 * Get loan status class for CSS
 */
function getStatusClass($status) {
    switch ($status) {
        case 'paid':
            return 'success';
        case 'overdue':
            return 'danger';
        case 'unpaid':
            return 'warning';
        case 'partially_paid':
            return 'info';
        default:
            return 'secondary';
    }
}

/**
 * Generate page title
 */
function getPageTitle($page = '') {
    $siteName = getSetting('site_name', SITE_NAME);
    return $page ? $page . ' - ' . $siteName : $siteName;
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Check if page requires authentication
 */
function checkPageAccess($requireAuth = true, $requireSuperAdmin = false) {
    if ($requireAuth && !isLoggedIn()) {
        redirect(SITE_URL . '/login.php', 'Please log in to access this page.', 'warning');
    }
    
    if ($requireSuperAdmin && !isSuperAdmin()) {
        redirect(SITE_URL . '/dashboard.php', 'Access denied. Super admin privileges required.', 'error');
    }
}
?>
