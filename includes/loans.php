<?php
/**
 * Loan Management Functions
 * CRUD operations for loans
 */

require_once 'config.php';
require_once 'database.php';
require_once 'security.php';

/**
 * Create a new loan
 */
function createLoan($loanData) {
    global $db;
    
    try {
        // Validate required fields
        $requiredFields = ['borrower_name', 'loan_amount', 'interest_rate', 'duration_days'];
        foreach ($requiredFields as $field) {
            if (empty($loanData[$field])) {
                return [
                    'success' => false,
                    'message' => "Field '{$field}' is required."
                ];
            }
        }
        
        // Validate input data
        if (!validateInput($loanData['borrower_name'], 'name')) {
            return [
                'success' => false,
                'message' => 'Invalid borrower name format.'
            ];
        }
        
        if (!validateInput($loanData['loan_amount'], 'amount')) {
            return [
                'success' => false,
                'message' => 'Invalid loan amount. Must be greater than 0.'
            ];
        }
        
        if (!validateInput($loanData['interest_rate'], 'percentage')) {
            return [
                'success' => false,
                'message' => 'Invalid interest rate. Must be between 0 and 100.'
            ];
        }
        
        if (!validateInput($loanData['duration_days'], 'days')) {
            return [
                'success' => false,
                'message' => 'Invalid duration. Must be between 1 and 3650 days.'
            ];
        }
        
        // Validate optional fields
        if (!empty($loanData['borrower_email']) && !validateInput($loanData['borrower_email'], 'email')) {
            return [
                'success' => false,
                'message' => 'Invalid email format.'
            ];
        }
        
        if (!empty($loanData['borrower_phone']) && !validateInput($loanData['borrower_phone'], 'phone')) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format.'
            ];
        }
        
        // Calculate loan details
        $loanAmount = floatval($loanData['loan_amount']);
        $interestRate = floatval($loanData['interest_rate']);
        $durationDays = intval($loanData['duration_days']);
        
        $interestAmount = $loanAmount * ($interestRate / 100);
        $totalPayable = $loanAmount + $interestAmount;
        
        // Calculate dates
        $startDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime("+{$durationDays} days"));
        
        // Insert loan record
        $db->query(
            "INSERT INTO loans (
                borrower_name, borrower_phone, borrower_email, borrower_address,
                loan_amount, interest_rate, duration_days, total_payable,
                start_date, due_date, created_by, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $loanData['borrower_name'],
                $loanData['borrower_phone'] ?? null,
                $loanData['borrower_email'] ?? null,
                $loanData['borrower_address'] ?? null,
                $loanAmount,
                $interestRate,
                $durationDays,
                $totalPayable,
                $startDate,
                $dueDate,
                $_SESSION['user_id'],
                $loanData['notes'] ?? null
            ]
        );
        
        $loanId = $db->lastInsertId();
        
        // Log the action
        logLoanHistory($loanId, 'created', null, $loanData);
        
        return [
            'success' => true,
            'message' => 'Loan created successfully.',
            'loan_id' => $loanId,
            'total_payable' => $totalPayable,
            'due_date' => $dueDate
        ];
        
    } catch (Exception $e) {
        error_log("Loan creation error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to create loan. Please try again.'
        ];
    }
}

/**
 * Get all loans with optional filtering
 */
function getLoans($filters = []) {
    global $db;
    
    try {
        $where = [];
        $params = [];
        
        // Build WHERE clause based on filters
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['borrower_name'])) {
            $where[] = "borrower_name LIKE ?";
            $params[] = '%' . $filters['borrower_name'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "start_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "start_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['overdue_only']) && $filters['overdue_only']) {
            $where[] = "due_date < CURDATE() AND status != 'paid'";
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Handle limit
        $limitClause = '';
        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $limitClause = 'LIMIT ' . intval($filters['limit']);
        }
        
        $sql = "SELECT l.*, u.full_name as created_by_name 
                FROM loans l 
                LEFT JOIN users u ON l.created_by = u.id 
                {$whereClause}
                ORDER BY l.created_at DESC
                {$limitClause}";
        
        $loans = $db->fetchAll($sql, $params);
        
        // Update overdue status
        foreach ($loans as &$loan) {
            if ($loan['status'] === 'unpaid' && $loan['due_date'] < date('Y-m-d')) {
                updateLoanStatus($loan['id'], 'overdue');
                $loan['status'] = 'overdue';
            }
        }
        
        return $loans;
        
    } catch (Exception $e) {
        error_log("Get loans error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get single loan by ID
 */
function getLoan($loanId) {
    global $db;
    
    try {
        $loan = $db->fetch(
            "SELECT l.*, u.full_name as created_by_name 
             FROM loans l 
             LEFT JOIN users u ON l.created_by = u.id 
             WHERE l.id = ?",
            [$loanId]
        );
        
        if ($loan && $loan['status'] === 'unpaid' && $loan['due_date'] < date('Y-m-d')) {
            updateLoanStatus($loanId, 'overdue');
            $loan['status'] = 'overdue';
        }
        
        return $loan;
        
    } catch (Exception $e) {
        error_log("Get loan error: " . $e->getMessage());
        return null;
    }
}

/**
 * Update loan information
 */
function updateLoan($loanId, $loanData) {
    global $db;
    
    try {
        // Get existing loan
        $existingLoan = getLoan($loanId);
        if (!$existingLoan) {
            return [
                'success' => false,
                'message' => 'Loan not found.'
            ];
        }
        
        // Validate input data (same as create)
        if (!empty($loanData['borrower_name']) && !validateInput($loanData['borrower_name'], 'name')) {
            return [
                'success' => false,
                'message' => 'Invalid borrower name format.'
            ];
        }
        
        if (!empty($loanData['loan_amount']) && !validateInput($loanData['loan_amount'], 'amount')) {
            return [
                'success' => false,
                'message' => 'Invalid loan amount.'
            ];
        }
        
        if (!empty($loanData['interest_rate']) && !validateInput($loanData['interest_rate'], 'percentage')) {
            return [
                'success' => false,
                'message' => 'Invalid interest rate.'
            ];
        }
        
        if (!empty($loanData['duration_days']) && !validateInput($loanData['duration_days'], 'days')) {
            return [
                'success' => false,
                'message' => 'Invalid duration.'
            ];
        }
        
        $updateFields = [];
        $updateValues = [];
        
        // Build update query
        $fieldsToUpdate = [
            'borrower_name', 'borrower_phone', 'borrower_email', 
            'borrower_address', 'loan_amount', 'interest_rate', 
            'duration_days', 'notes'
        ];
        
        foreach ($fieldsToUpdate as $field) {
            if (isset($loanData[$field]) && $loanData[$field] !== $existingLoan[$field]) {
                $updateFields[] = "{$field} = ?";
                $updateValues[] = $loanData[$field];
            }
        }
        
        // Recalculate if loan amount or interest rate changed
        if (isset($loanData['loan_amount']) || isset($loanData['interest_rate'])) {
            $loanAmount = isset($loanData['loan_amount']) ? floatval($loanData['loan_amount']) : $existingLoan['loan_amount'];
            $interestRate = isset($loanData['interest_rate']) ? floatval($loanData['interest_rate']) : $existingLoan['interest_rate'];
            
            $interestAmount = $loanAmount * ($interestRate / 100);
            $totalPayable = $loanAmount + $interestAmount;
            
            $updateFields[] = "total_payable = ?";
            $updateValues[] = $totalPayable;
        }
        
        // Recalculate due date if duration changed
        if (isset($loanData['duration_days'])) {
            $durationDays = intval($loanData['duration_days']);
            $dueDate = date('Y-m-d', strtotime($existingLoan['start_date'] . " +{$durationDays} days"));
            
            $updateFields[] = "due_date = ?";
            $updateValues[] = $dueDate;
        }
        
        if (empty($updateFields)) {
            return [
                'success' => false,
                'message' => 'No changes to update.'
            ];
        }
        
        $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
        $updateValues[] = $loanId;
        
        // Execute update
        $db->query(
            "UPDATE loans SET " . implode(', ', $updateFields) . " WHERE id = ?",
            $updateValues
        );
        
        // Log the action
        logLoanHistory($loanId, 'updated', $existingLoan, $loanData);
        
        return [
            'success' => true,
            'message' => 'Loan updated successfully.'
        ];
        
    } catch (Exception $e) {
        error_log("Loan update error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to update loan. Please try again.'
        ];
    }
}

/**
 * Mark loan as paid
 */
function markLoanAsPaid($loanId, $amountPaid = null, $paymentDate = null) {
    global $db;
    
    try {
        $loan = getLoan($loanId);
        if (!$loan) {
            return [
                'success' => false,
                'message' => 'Loan not found.'
            ];
        }
        
        if ($loan['status'] === 'paid') {
            return [
                'success' => false,
                'message' => 'Loan is already marked as paid.'
            ];
        }
        
        $paymentDate = $paymentDate ?: date('Y-m-d');
        $amountPaid = $amountPaid ?: $loan['total_payable'];
        
        // Update loan status
        $db->query(
            "UPDATE loans SET 
                status = 'paid', 
                amount_paid = ?, 
                date_paid = ?,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = ?",
            [$amountPaid, $paymentDate, $loanId]
        );
        
        // Record payment
        $db->query(
            "INSERT INTO payments (loan_id, amount, payment_date, recorded_by) 
             VALUES (?, ?, ?, ?)",
            [$loanId, $amountPaid, $paymentDate, $_SESSION['user_id']]
        );
        
        // Log the action
        logLoanHistory($loanId, 'paid', $loan, [
            'amount_paid' => $amountPaid,
            'payment_date' => $paymentDate
        ]);
        
        return [
            'success' => true,
            'message' => 'Loan marked as paid successfully.'
        ];
        
    } catch (Exception $e) {
        error_log("Mark loan paid error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to mark loan as paid. Please try again.'
        ];
    }
}

/**
 * Add partial payment
 */
function addPayment($loanId, $amount, $paymentDate = null, $paymentMethod = null, $notes = null) {
    global $db;
    
    try {
        $loan = getLoan($loanId);
        if (!$loan) {
            return [
                'success' => false,
                'message' => 'Loan not found.'
            ];
        }
        
        if ($loan['status'] === 'paid') {
            return [
                'success' => false,
                'message' => 'Cannot add payment to already paid loan.'
            ];
        }
        
        if (!validateInput($amount, 'amount')) {
            return [
                'success' => false,
                'message' => 'Invalid payment amount.'
            ];
        }
        
        $paymentDate = $paymentDate ?: date('Y-m-d');
        $currentPaid = floatval($loan['amount_paid']);
        $newTotalPaid = $currentPaid + floatval($amount);
        
        // Check if payment exceeds total payable
        if ($newTotalPaid > $loan['total_payable']) {
            return [
                'success' => false,
                'message' => 'Payment amount exceeds remaining balance.'
            ];
        }
        
        $db->beginTransaction();
        
        // Record payment
        $db->query(
            "INSERT INTO payments (loan_id, amount, payment_date, payment_method, notes, recorded_by) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$loanId, $amount, $paymentDate, $paymentMethod, $notes, $_SESSION['user_id']]
        );
        
        // Update loan
        $newStatus = ($newTotalPaid >= $loan['total_payable']) ? 'paid' : 'partially_paid';
        $datePaid = ($newStatus === 'paid') ? $paymentDate : null;
        
        $db->query(
            "UPDATE loans SET 
                amount_paid = ?, 
                status = ?,
                date_paid = ?,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = ?",
            [$newTotalPaid, $newStatus, $datePaid, $loanId]
        );
        
        $db->commit();
        
        // Log the action
        logLoanHistory($loanId, 'payment_added', $loan, [
            'payment_amount' => $amount,
            'payment_date' => $paymentDate,
            'new_total_paid' => $newTotalPaid,
            'new_status' => $newStatus
        ]);
        
        return [
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'new_status' => $newStatus,
            'remaining_balance' => $loan['total_payable'] - $newTotalPaid
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        error_log("Add payment error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to record payment. Please try again.'
        ];
    }
}

/**
 * Delete loan
 */
function deleteLoan($loanId) {
    global $db;
    
    try {
        $loan = getLoan($loanId);
        if (!$loan) {
            return [
                'success' => false,
                'message' => 'Loan not found.'
            ];
        }
        
        // Only allow deletion of unpaid loans or by super admin
        if ($loan['status'] === 'paid' && !isSuperAdmin()) {
            return [
                'success' => false,
                'message' => 'Cannot delete paid loans. Contact super admin if needed.'
            ];
        }
        
        // Log before deletion
        logLoanHistory($loanId, 'deleted', $loan, null);
        
        // Delete loan (this will cascade delete payments and history due to FK constraints)
        $db->query("DELETE FROM loans WHERE id = ?", [$loanId]);
        
        return [
            'success' => true,
            'message' => 'Loan deleted successfully.'
        ];
        
    } catch (Exception $e) {
        error_log("Delete loan error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to delete loan. Please try again.'
        ];
    }
}

/**
 * Update loan status only
 */
function updateLoanStatus($loanId, $status) {
    global $db;
    
    try {
        if (!in_array($status, ['unpaid', 'paid', 'overdue', 'partially_paid'])) {
            return false;
        }
        
        $db->query(
            "UPDATE loans SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$status, $loanId]
        );
        
        return true;
        
    } catch (Exception $e) {
        error_log("Update loan status error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get loan statistics
 */
function getLoanStatistics($period = '12_months') {
    global $db;
    
    try {
        $dateCondition = '';
        $params = [];
        
        switch ($period) {
            case '3_months':
                $dateCondition = "WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                break;
            case '6_months':
                $dateCondition = "WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
                break;
            case '9_months':
                $dateCondition = "WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 9 MONTH)";
                break;
            case '12_months':
                $dateCondition = "WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                break;
            default:
                $dateCondition = '';
        }
        
        $stats = $db->fetch("
            SELECT 
                COUNT(*) as total_loans,
                COALESCE(SUM(loan_amount), 0) as total_amount_lent,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN total_payable - loan_amount ELSE 0 END), 0) as total_interest_earned,
                COALESCE(SUM(amount_paid), 0) as total_amount_repaid,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_loans,
                COUNT(CASE WHEN status = 'unpaid' THEN 1 END) as unpaid_loans,
                COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_loans,
                COUNT(CASE WHEN status = 'partially_paid' THEN 1 END) as partially_paid_loans
            FROM loans 
            {$dateCondition}
        ", $params);
        
        return $stats ?: [
            'total_loans' => 0,
            'total_amount_lent' => 0,
            'total_interest_earned' => 0,
            'total_amount_repaid' => 0,
            'paid_loans' => 0,
            'unpaid_loans' => 0,
            'overdue_loans' => 0,
            'partially_paid_loans' => 0
        ];
        
    } catch (Exception $e) {
        error_log("Get loan statistics error: " . $e->getMessage());
        return [];
    }
}

/**
 * Log loan history
 */
function logLoanHistory($loanId, $action, $oldValues = null, $newValues = null) {
    global $db;
    
    try {
        $db->query(
            "INSERT INTO loan_history (loan_id, action, old_values, new_values, performed_by) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $loanId,
                $action,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $_SESSION['user_id']
            ]
        );
    } catch (Exception $e) {
        error_log("Log loan history error: " . $e->getMessage());
    }
}

/**
 * Get loan payments
 */
function getLoanPayments($loanId) {
    global $db;
    
    try {
        return $db->fetchAll(
            "SELECT p.*, u.full_name as recorded_by_name 
             FROM payments p 
             LEFT JOIN users u ON p.recorded_by = u.id 
             WHERE p.loan_id = ? 
             ORDER BY p.payment_date DESC, p.created_at DESC",
            [$loanId]
        );
    } catch (Exception $e) {
        error_log("Get loan payments error: " . $e->getMessage());
        return [];
    }
}
?>
