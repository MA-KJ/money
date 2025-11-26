<?php
/**
 * Forgot Password Page
 * Request password reset via email
 */

require_once 'includes/app.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '', 'email');
        
        if (empty($email) || !validateInput($email, 'email')) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check rate limiting
            if (!checkRateLimit('password_reset_' . $_SERVER['REMOTE_ADDR'], 3, 3600)) {
                $error = 'Too many password reset attempts. Please try again later.';
            } else {
                $result = generatePasswordResetToken($email);
                
                if ($result['success']) {
                    // In a real application, you would send this via email
                    // For now, we'll display it (this is not secure for production!)
                    $resetLink = SITE_URL . '/reset-password.php?token=' . $result['token'];
                    $success = "Password reset instructions have been sent to your email. For demo purposes, your reset link is: <a href='$resetLink' class='alert-link'>$resetLink</a>";
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle('Forgot Password'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-key"></i>
                            Forgot Password
                        </h4>
                        <p class="mb-0 small">Reset your account password</p>
                    </div>
                    <div class="card-body">
                        <?php if ($flash): ?>
                            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($flash['message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle"></i>
                                <?php echo $success; ?>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-arrow-left"></i>
                                    Back to Login
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-4">
                                Enter your email address and we'll send you instructions to reset your password.
                            </p>

                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                               required autofocus placeholder="Enter your email address">
                                    </div>
                                    <div class="form-text">We'll send password reset instructions to this email.</div>
                                </div>

                                <button type="submit" name="forgot_password" class="btn btn-warning w-100 text-dark">
                                    <i class="bi bi-send"></i>
                                    Send Reset Instructions
                                </button>
                            </form>

                            <hr>
                            
                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="bi bi-arrow-left"></i>
                                    Remember your password? Sign in
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>
                            <i class="bi bi-shield-check"></i>
                            Your account security is important to us
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
