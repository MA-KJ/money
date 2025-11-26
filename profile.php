<?php
/**
 * User Profile Page
 * Allows users to view and update their own profile information
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/app.php';

checkPageAccess(true);

$error = '';
$success = '';
$currentUser = getCurrentUser();

// Get full user details from database
$userDetails = $db->fetch(
    "SELECT * FROM users WHERE id = ?",
    [$currentUser['id']]
);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $userData = [
            'username' => sanitizeInput($_POST['username'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? '', 'email'),
            'full_name' => sanitizeInput($_POST['full_name'] ?? '')
        ];
        
        $result = updateUser($currentUser['id'], $userData);
        if ($result['success']) {
            $success = $result['message'];
            // Update session data
            $_SESSION['username'] = $userData['username'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['full_name'] = $userData['full_name'];
            // Refresh user details
            $userDetails = $db->fetch(
                "SELECT * FROM users WHERE id = ?",
                [$currentUser['id']]
            );
        } else {
            $error = $result['message'];
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
    <title><?php echo getPageTitle('My Profile'); ?></title>
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
            <div class="col-lg-8 mx-auto">
                <!-- Profile Header -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php echo strtoupper(substr($userDetails['full_name'], 0, 1)); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($userDetails['full_name']); ?></h3>
                        <p class="text-muted mb-2">@<?php echo htmlspecialchars($userDetails['username']); ?></p>
                        <span class="badge bg-<?php echo $userDetails['role'] === 'super_admin' ? 'warning text-dark' : 'info'; ?> mb-3">
                            <?php echo ucfirst(str_replace('_', ' ', $userDetails['role'])); ?>
                        </span>
                        <div class="row text-center mt-4">
                            <div class="col-md-6">
                                <div class="border-end">
                                    <p class="text-muted mb-1">Member Since</p>
                                    <p class="fw-bold"><?php echo formatDate($userDetails['created_at']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Last Login</p>
                                <p class="fw-bold">
                                    <?php if ($userDetails['last_login']): ?>
                                        <?php echo formatDate($userDetails['last_login']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i>
                            Edit Profile Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($userDetails['username']); ?>" 
                                               required pattern="[a-zA-Z0-9_]{3,50}">
                                    </div>
                                    <div class="form-text">3-50 characters, letters, numbers, and underscores only</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($userDetails['email']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($userDetails['full_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo ucfirst(str_replace('_', ' ', $userDetails['role'])); ?>" 
                                           disabled readonly>
                                    <div class="form-text">Contact super admin to change role</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo ucfirst($userDetails['status']); ?>" 
                                           disabled readonly>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Note:</strong> To change your password, please visit the <a href="change-password.php" class="alert-link">Change Password</a> page.
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i>
                                    Update Profile
                                </button>
                                <a href="change-password.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-lock"></i>
                                    Change Password
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
