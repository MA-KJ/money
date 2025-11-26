<?php

/**
 * Database Configuration
 * Loan Tracking System
 */

// Database configuration
define('DB_HOST', 'sql113.infinityfree.com');
define('DB_NAME', 'if0_40061184_loan_tracking_system');
define('DB_USER', 'if0_40061184'); // ⚠️ update if InfinityFree gives you a different DB user
define('DB_PASS', 'Gwu89x00Cv');
define('DB_CHARSET', 'utf8mb4');


// Application configuration - Dynamic URL detection for shared hosting
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $host . rtrim($script, '/');
define('SITE_URL', $base_url);
define('SITE_NAME', 'Loan Tracking System');

// Security configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
