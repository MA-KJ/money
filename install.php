<?php
/**
 * Installation Script
 * Quick setup wizard for the Loan Tracking System
 */

// Prevent access after installation
if (file_exists('INSTALLED.lock')) {
    die('System is already installed. Delete INSTALLED.lock file to run installer again.');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Database configuration step
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_name = $_POST['db_name'] ?? 'loan_tracking_system';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';
        $site_url = $_POST['site_url'] ?? '';
        
        // Test database connection
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Save configuration
            $config_content = "<?php\n";
            $config_content .= "// Database configuration\n";
            $config_content .= "define('DB_HOST', '$db_host');\n";
            $config_content .= "define('DB_NAME', '$db_name');\n";
            $config_content .= "define('DB_USER', '$db_user');\n";
            $config_content .= "define('DB_PASS', '$db_pass');\n";
            $config_content .= "define('DB_CHARSET', 'utf8mb4');\n\n";
            $config_content .= "// Application configuration\n";
            $config_content .= "define('SITE_URL', '$site_url');\n";
            $config_content .= "define('SITE_NAME', 'Loan Tracking System');\n\n";
            $config_content .= "// Security configuration\n";
            $config_content .= "define('CSRF_TOKEN_NAME', 'csrf_token');\n";
            $config_content .= "define('SESSION_TIMEOUT', 3600);\n\n";
            $config_content .= "// Timezone\ndate_default_timezone_set('UTC');\n\n";
            $config_content .= "// Error reporting (disable in production)\n";
            $config_content .= "error_reporting(E_ALL);\nini_set('display_errors', 1);\n?>";
            
            file_put_contents('includes/config.php', $config_content);
            
            header('Location: install.php?step=2');
            exit;
        } catch (PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    } elseif ($step === 2) {
        // Database schema installation
        require_once 'includes/config.php';
        
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $schema = file_get_contents('includes/schema.sql');
            $pdo->exec($schema);
            
            header('Location: install.php?step=3');
            exit;
        } catch (Exception $e) {
            $error = 'Schema installation failed: ' . $e->getMessage();
        }
    } elseif ($step === 3) {
        // Create installation lock file
        file_put_contents('INSTALLED.lock', date('Y-m-d H:i:s'));
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Tracking System - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .install-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .step-indicator { background: #e9ecef; height: 4px; border-radius: 2px; }
        .step-progress { background: #0d6efd; height: 100%; border-radius: 2px; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 col-lg-6">
                <div class="card install-card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-bank"></i>
                            Loan Tracking System
                        </h3>
                        <p class="mb-0">Installation Wizard</p>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="px-4 pt-3">
                        <div class="step-indicator">
                            <div class="step-progress" style="width: <?php echo ($step / 3) * 100; ?>%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-<?php echo $step >= 1 ? 'primary' : 'muted'; ?>">Database</small>
                            <small class="text-<?php echo $step >= 2 ? 'primary' : 'muted'; ?>">Schema</small>
                            <small class="text-<?php echo $step >= 3 ? 'primary' : 'muted'; ?>">Complete</small>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step === 1): ?>
                            <h4>Step 1: Database Configuration</h4>
                            <p class="text-muted mb-4">Enter your database connection details</p>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="db_host" class="form-label">Database Host</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="db_name" class="form-label">Database Name</label>
                                    <input type="text" class="form-control" id="db_name" name="db_name" value="loan_tracking_system" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="db_user" class="form-label">Database Username</label>
                                    <input type="text" class="form-control" id="db_user" name="db_user" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="db_pass" class="form-label">Database Password</label>
                                    <input type="password" class="form-control" id="db_pass" name="db_pass">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="site_url" class="form-label">Site URL</label>
                                    <input type="url" class="form-control" id="site_url" name="site_url" 
                                           value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?>" required>
                                    <div class="form-text">Full URL to your installation (without trailing slash)</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    Test Connection & Continue
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </form>
                            
                        <?php elseif ($step === 2): ?>
                            <h4>Step 2: Database Schema</h4>
                            <p class="text-muted mb-4">Install database tables and default data</p>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                This will create all necessary database tables and insert default data including the admin user.
                            </div>
                            
                            <form method="POST">
                                <button type="submit" class="btn btn-primary w-100">
                                    Install Database Schema
                                    <i class="bi bi-download"></i>
                                </button>
                            </form>
                            
                        <?php elseif ($step === 3): ?>
                            <h4>Step 3: Installation Complete!</h4>
                            <div class="text-center">
                                <i class="bi bi-check-circle-fill text-success display-1"></i>
                                <h5 class="text-success mt-3">System Installed Successfully!</h5>
                                
                                <div class="alert alert-warning mt-4">
                                    <h6>Default Login Credentials:</h6>
                                    <p class="mb-0">
                                        <strong>Username:</strong> admin<br>
                                        <strong>Password:</strong> admin123
                                    </p>
                                </div>
                                
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Important:</strong> Change the default password immediately after logging in!
                                </div>
                                
                                <form method="POST">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        Go to Login Page
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer text-center text-muted">
                        <small>
                            <i class="bi bi-shield-check"></i>
                            Secure PHP Loan Tracking System v1.0
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
