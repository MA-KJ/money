<?php
/**
 * Authentication Functions
 * Login, logout, and user management
 */

require_once 'config.php';
require_once 'database.php';
require_once 'security.php';

/**
 * Authenticate user login
 */
function authenticateUser($username, $password) {
    global $db;
    
    try {
        // Check rate limiting
        if (!checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'])) {
            logSecurityEvent('login_rate_limit_exceeded', ['username' => $username]);
            return [
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.'
            ];
        }
        
        // Get user from database
        $user = $db->fetch(
            "SELECT id, username, password_hash, full_name, email, role, status, last_login 
             FROM users 
             WHERE username = ? AND status = 'active'",
            [$username]
        );
        
        if (!$user) {
            logSecurityEvent('login_failed_user_not_found', ['username' => $username]);
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
        
        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            logSecurityEvent('login_failed_invalid_password', [
                'username' => $username,
                'user_id' => $user['id']
            ]);
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
        
        // Update last login
        $db->query(
            "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
            [$user['id']]
        );
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        // Generate new CSRF token
        unset($_SESSION[CSRF_TOKEN_NAME]);
        generateCSRFToken();
        
        logSecurityEvent('login_successful', [
            'username' => $username,
            'user_id' => $user['id']
        ]);
        
        return [
            'success' => true,
            'message' => 'Login successful.',
            'user' => $user
        ];
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Authentication failed. Please try again.'
        ];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    $user_id = $_SESSION['user_id'] ?? null;
    
    logSecurityEvent('logout', ['user_id' => $user_id]);
    
    // Clear all session data
    $_SESSION = [];
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    return true;
}

/**
 * Create new user
 */
function createUser($userData) {
    global $db;
    
    try {
        // Validate required fields
        $requiredFields = ['username', 'email', 'password', 'full_name'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                return [
                    'success' => false,
                    'message' => "Field '{$field}' is required."
                ];
            }
        }
        
        // Validate input data
        if (!validateInput($userData['username'], 'username')) {
            return [
                'success' => false,
                'message' => 'Invalid username format. Use only letters, numbers, and underscores (3-50 characters).'
            ];
        }
        
        if (!validateInput($userData['email'], 'email')) {
            return [
                'success' => false,
                'message' => 'Invalid email format.'
            ];
        }
        
        if (!validateInput($userData['password'], 'password')) {
            return [
                'success' => false,
                'message' => 'Password must be at least 6 characters long.'
            ];
        }
        
        if (!validateInput($userData['full_name'], 'name')) {
            return [
                'success' => false,
                'message' => 'Invalid full name format.'
            ];
        }
        
        // Check if username already exists
        $existingUser = $db->fetch(
            "SELECT id FROM users WHERE username = ?",
            [$userData['username']]
        );
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Username already exists.'
            ];
        }
        
        // Check if email already exists
        $existingEmail = $db->fetch(
            "SELECT id FROM users WHERE email = ?",
            [$userData['email']]
        );
        
        if ($existingEmail) {
            return [
                'success' => false,
                'message' => 'Email already exists.'
            ];
        }
        
        // Hash password
        $passwordHash = hashPassword($userData['password']);
        
        // Set default role if not specified
        $role = isset($userData['role']) && in_array($userData['role'], ['super_admin', 'admin']) 
               ? $userData['role'] 
               : 'admin';
        
        // Insert user
        $userId = $db->query(
            "INSERT INTO users (username, email, password_hash, full_name, role, created_by, status) 
             VALUES (?, ?, ?, ?, ?, ?, 'active')",
            [
                $userData['username'],
                $userData['email'],
                $passwordHash,
                $userData['full_name'],
                $role,
                $_SESSION['user_id'] ?? null
            ]
        );
        
        $userId = $db->lastInsertId();
        
        logSecurityEvent('user_created', [
            'new_user_id' => $userId,
            'username' => $userData['username'],
            'role' => $role,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);
        
        return [
            'success' => true,
            'message' => 'User created successfully.',
            'user_id' => $userId
        ];
        
    } catch (Exception $e) {
        error_log("User creation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to create user. Please try again.'
        ];
    }
}

/**
 * Update user information
 */
function updateUser($userId, $userData) {
    global $db;
    
    try {
        // Check if user exists
        $existingUser = $db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$existingUser) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }
        
        $updateFields = [];
        $updateValues = [];
        
        // Update username
        if (!empty($userData['username']) && $userData['username'] !== $existingUser['username']) {
            if (!validateInput($userData['username'], 'username')) {
                return [
                    'success' => false,
                    'message' => 'Invalid username format.'
                ];
            }
            
            // Check if new username exists
            $usernameExists = $db->fetch(
                "SELECT id FROM users WHERE username = ? AND id != ?",
                [$userData['username'], $userId]
            );
            
            if ($usernameExists) {
                return [
                    'success' => false,
                    'message' => 'Username already exists.'
                ];
            }
            
            $updateFields[] = 'username = ?';
            $updateValues[] = $userData['username'];
        }
        
        // Update email
        if (!empty($userData['email']) && $userData['email'] !== $existingUser['email']) {
            if (!validateInput($userData['email'], 'email')) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format.'
                ];
            }
            
            // Check if new email exists
            $emailExists = $db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$userData['email'], $userId]
            );
            
            if ($emailExists) {
                return [
                    'success' => false,
                    'message' => 'Email already exists.'
                ];
            }
            
            $updateFields[] = 'email = ?';
            $updateValues[] = $userData['email'];
        }
        
        // Update full name
        if (!empty($userData['full_name']) && $userData['full_name'] !== $existingUser['full_name']) {
            if (!validateInput($userData['full_name'], 'name')) {
                return [
                    'success' => false,
                    'message' => 'Invalid full name format.'
                ];
            }
            
            $updateFields[] = 'full_name = ?';
            $updateValues[] = $userData['full_name'];
        }
        
        // Update password
        if (!empty($userData['password'])) {
            if (!validateInput($userData['password'], 'password')) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 6 characters long.'
                ];
            }
            
            $updateFields[] = 'password_hash = ?';
            $updateValues[] = hashPassword($userData['password']);
        }
        
        // Update role (only super admin can change roles)
        if (isset($userData['role']) && isSuperAdmin() && $userData['role'] !== $existingUser['role']) {
            if (in_array($userData['role'], ['super_admin', 'admin'])) {
                $updateFields[] = 'role = ?';
                $updateValues[] = $userData['role'];
            }
        }
        
        // Update status (only super admin can change status)
        if (isset($userData['status']) && isSuperAdmin() && $userData['status'] !== $existingUser['status']) {
            if (in_array($userData['status'], ['active', 'inactive'])) {
                $updateFields[] = 'status = ?';
                $updateValues[] = $userData['status'];
            }
        }
        
        if (empty($updateFields)) {
            return [
                'success' => false,
                'message' => 'No changes to update.'
            ];
        }
        
        // Add updated_at field
        $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
        $updateValues[] = $userId;
        
        // Execute update
        $db->query(
            "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?",
            $updateValues
        );
        
        logSecurityEvent('user_updated', [
            'user_id' => $userId,
            'updated_by' => $_SESSION['user_id'] ?? null,
            'fields_updated' => array_keys($userData)
        ]);
        
        return [
            'success' => true,
            'message' => 'User updated successfully.'
        ];
        
    } catch (Exception $e) {
        error_log("User update error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to update user. Please try again.'
        ];
    }
}

/**
 * Get all users (for admin management)
 */
function getAllUsers() {
    global $db;
    
    try {
        return $db->fetchAll(
            "SELECT id, username, email, full_name, role, status, created_at, last_login 
             FROM users 
             ORDER BY created_at DESC"
        );
    } catch (Exception $e) {
        error_log("Get users error: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete user
 */
function deleteUser($userId) {
    global $db;
    
    try {
        // Check if user exists
        $user = $db->fetch(
            "SELECT username FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }
        
        // Don't allow deleting own account or other super admins
        if ($userId == $_SESSION['user_id']) {
            return [
                'success' => false,
                'message' => 'Cannot delete your own account.'
            ];
        }
        
        // Delete user
        $db->query(
            "DELETE FROM users WHERE id = ?",
            [$userId]
        );
        
        logSecurityEvent('user_deleted', [
            'deleted_user_id' => $userId,
            'username' => $user['username'],
            'deleted_by' => $_SESSION['user_id']
        ]);
        
        return [
            'success' => true,
            'message' => 'User deleted successfully.'
        ];
        
    } catch (Exception $e) {
        error_log("User deletion error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to delete user. Please try again.'
        ];
    }
}

/**
 * Generate password reset token
 */
function generatePasswordResetToken($email) {
    global $db;
    
    try {
        // Check if user exists
        $user = $db->fetch(
            "SELECT id, email, full_name FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'No account found with this email address.'
            ];
        }
        
        // Generate secure token
        $token = generateSecureToken(32);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
        
        // Update user with reset token
        $db->query(
            "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?",
            [$token, $expires, $user['id']]
        );
        
        logSecurityEvent('password_reset_requested', [
            'user_id' => $user['id'],
            'email' => $email
        ]);
        
        return [
            'success' => true,
            'message' => 'Password reset token generated successfully.',
            'token' => $token,
            'user' => $user
        ];
        
    } catch (Exception $e) {
        error_log("Password reset token generation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to generate reset token. Please try again.'
        ];
    }
}

/**
 * Verify password reset token
 */
function verifyPasswordResetToken($token) {
    global $db;
    
    try {
        $user = $db->fetch(
            "SELECT id, email, full_name, password_reset_expires 
             FROM users 
             WHERE password_reset_token = ? AND status = 'active'",
            [$token]
        );
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid reset token.'
            ];
        }
        
        // Check if token has expired
        if (strtotime($user['password_reset_expires']) < time()) {
            return [
                'success' => false,
                'message' => 'Reset token has expired. Please request a new one.'
            ];
        }
        
        return [
            'success' => true,
            'user' => $user
        ];
        
    } catch (Exception $e) {
        error_log("Password reset token verification error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Token verification failed. Please try again.'
        ];
    }
}

/**
 * Reset password with token
 */
function resetPasswordWithToken($token, $newPassword) {
    global $db;
    
    try {
        // Verify token
        $tokenResult = verifyPasswordResetToken($token);
        if (!$tokenResult['success']) {
            return $tokenResult;
        }
        
        $user = $tokenResult['user'];
        
        // Validate password
        if (!validateInput($newPassword, 'password')) {
            return [
                'success' => false,
                'message' => 'Password must be at least 6 characters long.'
            ];
        }
        
        // Hash new password
        $passwordHash = hashPassword($newPassword);
        
        // Update password and clear reset token
        $db->query(
            "UPDATE users SET 
                password_hash = ?, 
                password_reset_token = NULL, 
                password_reset_expires = NULL,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = ?",
            [$passwordHash, $user['id']]
        );
        
        logSecurityEvent('password_reset_completed', [
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);
        
        return [
            'success' => true,
            'message' => 'Password has been reset successfully.'
        ];
        
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to reset password. Please try again.'
        ];
    }
}
?>
