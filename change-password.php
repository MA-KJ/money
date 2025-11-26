<?php
/**
 * Change Password Page
 * Allows users to change their own password without email verification
 */

require_once 'includes/app.php';

checkPageAccess(true);

$error = '';
$success = '';
$currentUser = getCurrentUser();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirmation do not match.';
        } elseif (!validateInput($newPassword, 'password')) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            // Verify current password
            $user = $db->fetch(
                "SELECT password_hash FROM users WHERE id = ?",
                [$currentUser['id']]
            );
            
            if (!$user || !verifyPassword($currentPassword, $user['password_hash'])) {
                $error = 'Current password is incorrect.';
                logSecurityEvent('password_change_failed_wrong_password', [
                    'user_id' => $currentUser['id'],
                    'username' => $currentUser['username']
                ]);
            } else {
                // Update password
                $result = updateUser($currentUser['id'], ['password' => $newPassword]);
                if ($result['success']) {
                    $success = 'Password changed successfully!';
                    logSecurityEvent('password_changed', [
                        'user_id' => $currentUser['id'],
                        'username' => $currentUser['username']
                    ]);
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
    <title><?php echo getPageTitle('Change Password'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="container-fluid">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lock-fill"></i>
                            Change Password
                        </h5>
                        <p class="mb-0 small">Update your account password</p>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Minimum 6 characters long</li>
                                <li>Use a strong, unique password</li>
                                <li>Don't reuse passwords from other accounts</li>
                            </ul>
                        </div>

                        <form method="POST" action="" id="changePasswordForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-shield-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="current_password" name="current_password" 
                                           required placeholder="Enter your current password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('current_password')">
                                        <i class="bi bi-eye" id="current_password_icon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           required minlength="6" placeholder="Enter new password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('new_password')">
                                        <i class="bi bi-eye" id="new_password_icon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Must be at least 6 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           required minlength="6" placeholder="Re-enter new password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirm_password')">
                                        <i class="bi bi-eye" id="confirm_password_icon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Password Strength Indicator -->
                            <div class="mb-3">
                                <label class="form-label">Password Strength</label>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted" id="strengthText"></small>
                            </div>

                            <!-- Password Match Indicator -->
                            <div class="mb-3" id="matchIndicator" style="display: none;">
                                <div class="alert alert-warning py-2" id="matchText">
                                    <i class="bi bi-exclamation-circle"></i>
                                    <small>Passwords do not match</small>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" name="change_password" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-check-circle"></i>
                                    Change Password
                                </button>
                                <a href="profile.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i>
                                    Back to Profile
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-shield-check"></i>
                            Security Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Use a unique password that you don't use for other accounts</li>
                            <li>Consider using a password manager to generate and store strong passwords</li>
                            <li>Change your password regularly (every 3-6 months)</li>
                            <li>Never share your password with anyone</li>
                            <li>If you suspect your account has been compromised, change your password immediately</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        // Password strength checker
        const newPassword = document.getElementById('new_password');
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');

        newPassword.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength += 25;
            if (password.length >= 10) strength += 25;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
            if (/\d/.test(password)) strength += 15;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
            
            strengthBar.style.width = strength + '%';
            
            if (strength < 40) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Weak';
            } else if (strength < 70) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Medium';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Strong';
            }
        });

        // Password match checker
        const confirmPassword = document.getElementById('confirm_password');
        const matchIndicator = document.getElementById('matchIndicator');
        const matchText = document.getElementById('matchText');
        const submitBtn = document.getElementById('submitBtn');

        function checkPasswordMatch() {
            const newPass = newPassword.value;
            const confirmPass = confirmPassword.value;
            
            if (confirmPass.length > 0) {
                matchIndicator.style.display = 'block';
                
                if (newPass === confirmPass) {
                    matchText.className = 'alert alert-success py-2';
                    matchText.innerHTML = '<i class="bi bi-check-circle"></i> <small>Passwords match</small>';
                    submitBtn.disabled = false;
                } else {
                    matchText.className = 'alert alert-warning py-2';
                    matchText.innerHTML = '<i class="bi bi-exclamation-circle"></i> <small>Passwords do not match</small>';
                    submitBtn.disabled = true;
                }
            } else {
                matchIndicator.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        newPassword.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);

        // Auto-dismiss success alerts
        setTimeout(function() {
            const successAlerts = document.querySelectorAll('.alert-success');
            successAlerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
