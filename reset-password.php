<?php
/**
 * Reset Password Page
 * Reset password using secure token
 */

require_once 'includes/app.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$token = sanitizeInput($_GET['token'] ?? '');
$error = '';
$success = '';

// Verify token first
if (empty($token)) {
    redirect(SITE_URL . '/forgot-password.php', 'Invalid reset link.', 'error');
}

$tokenResult = verifyPasswordResetToken($token);
if (!$tokenResult['success']) {
    redirect(SITE_URL . '/forgot-password.php', $tokenResult['message'], 'error');
}

$user = $tokenResult['user'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in both password fields.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!validateInput($password, 'password')) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            $result = resetPasswordWithToken($token, $password);
            
            if ($result['success']) {
                redirect(SITE_URL . '/login.php', 'Password reset successfully. You can now log in with your new password.', 'success');
            } else {
                $error = $result['message'];
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
    <title><?php echo getPageTitle('Reset Password'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-check"></i>
                            Reset Password
                        </h4>
                        <p class="mb-0 small">Create a new password for your account</p>
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

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Account:</strong> <?php echo htmlspecialchars($user['full_name']); ?>
                            <br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                        </div>

                        <form method="POST" action="" id="resetForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required minlength="6" placeholder="Enter new password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Must be at least 6 characters long</div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           required minlength="6" placeholder="Confirm new password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                        <i class="bi bi-eye" id="confirm_passwordToggleIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text" id="passwordMatch"></div>
                            </div>

                            <button type="submit" name="reset_password" class="btn btn-success w-100">
                                <i class="bi bi-shield-check"></i>
                                Reset Password
                            </button>
                        </form>

                        <hr>
                        
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i>
                                Back to Login
                            </a>
                        </div>
                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>
                            <i class="bi bi-clock"></i>
                            This reset link expires in 1 hour
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + 'ToggleIcon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        // Real-time password matching
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            const feedback = document.getElementById('passwordMatch');
            
            if (confirm.length === 0) {
                feedback.textContent = '';
                feedback.className = 'form-text';
            } else if (password === confirm) {
                feedback.textContent = '✓ Passwords match';
                feedback.className = 'form-text text-success';
            } else {
                feedback.textContent = '✗ Passwords do not match';
                feedback.className = 'form-text text-danger';
            }
        });

        // Form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }
        });
    </script>
</body>
</html>
