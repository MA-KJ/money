<?php
/**
 * Security Functions
 * Input validation, CSRF protection, and authentication
 */

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !hash_equals($_SESSION[CSRF_TOKEN_NAME], $token)) {
        return false;
    }
    return true;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        default:
            return $data;
    }
}

/**
 * Validate input data
 */
function validateInput($data, $type, $required = true) {
    if ($required && empty($data)) {
        return false;
    }
    
    if (!$required && empty($data)) {
        return true;
    }
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL) !== false;
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT) !== false;
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT) !== false;
        case 'phone':
            return preg_match('/^[+]?[\d\s\-\(\)]{10,20}$/', $data);
        case 'username':
            return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data);
        case 'password':
            return strlen($data) >= 6;
        case 'name':
            return preg_match('/^[a-zA-Z\s]{2,100}$/', $data);
        case 'amount':
            return is_numeric($data) && $data > 0;
        case 'percentage':
            return is_numeric($data) && $data >= 0 && $data <= 100;
        case 'days':
            return is_numeric($data) && $data > 0 && $data <= 3650; // Max 10 years
        case 'date':
            return DateTime::createFromFormat('Y-m-d', $data) !== false;
        case 'datetime':
            return DateTime::createFromFormat('Y-m-d H:i:s', $data) !== false;
        case 'token':
            return preg_match('/^[a-f0-9]{64}$/', $data); // 64 character hex token
        case 'alphanumeric':
            return preg_match('/^[a-zA-Z0-9]+$/', $data);
        case 'text':
            return strlen($data) <= 10000; // Max 10k characters for text fields
        default:
            return true;
    }
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate secure random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Rate limiting check
 */
function checkRateLimit($identifier, $max_attempts = 5, $time_window = 300) {
    $attempts_key = "rate_limit_{$identifier}";
    
    if (!isset($_SESSION[$attempts_key])) {
        $_SESSION[$attempts_key] = [
            'count' => 0,
            'first_attempt' => time()
        ];
    }
    
    $attempts = $_SESSION[$attempts_key];
    $current_time = time();
    
    // Reset if time window has passed
    if ($current_time - $attempts['first_attempt'] > $time_window) {
        $_SESSION[$attempts_key] = [
            'count' => 1,
            'first_attempt' => $current_time
        ];
        return true;
    }
    
    // Check if limit exceeded
    if ($attempts['count'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$attempts_key]['count']++;
    return true;
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'event' => $event,
        'details' => $details
    ];
    
    // Log to file (in production, use proper logging system)
    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Check if user is super admin
 */
function isSuperAdmin() {
    return hasRole('super_admin');
}

/**
 * Check if user is the primary super admin (user ID 1)
 * Only the primary super admin can edit/delete records and users
 */
function isPrimarySuperAdmin() {
    return isLoggedIn() && $_SESSION['user_id'] == 1 && isSuperAdmin();
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

/**
 * Require super admin role
 */
function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        header('Location: ' . SITE_URL . '/dashboard.php?error=access_denied');
        exit;
    }
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['user_role'],
        'email' => $_SESSION['email'] ?? ''
    ];
}

/**
 * Update session timeout
 */
function updateSessionTimeout() {
    $_SESSION['last_activity'] = time();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        header('Location: ' . SITE_URL . '/login.php?error=session_expired');
        exit;
    }
    updateSessionTimeout();
}

/**
 * Secure session start
 */
function secureSessionStart() {
    // Configure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
?>
