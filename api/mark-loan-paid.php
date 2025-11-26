<?php
/**
 * API Endpoint: Mark Loan as Paid
 * Handles AJAX requests to mark loans as paid
 */

require_once '../includes/app.php';
require_once '../includes/loans.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $loanId = filter_input(INPUT_POST, 'loan_id', FILTER_VALIDATE_INT);
    $paymentDate = isset($_POST['payment_date']) ? trim($_POST['payment_date']) : null;
    
    // Handle amount_paid - if empty string or not provided, set to null for full amount
    $amountPaid = null;
    if (isset($_POST['amount_paid']) && $_POST['amount_paid'] !== '') {
        $amountPaid = filter_var($_POST['amount_paid'], FILTER_VALIDATE_FLOAT);
        if ($amountPaid === false || $amountPaid <= 0) {
            throw new Exception('Invalid payment amount');
        }
    }
    
    if (!$loanId) {
        throw new Exception('Invalid loan ID');
    }
    
    if (!$paymentDate || !validateInput($paymentDate, 'date')) {
        throw new Exception('Invalid payment date');
    }
    
    // Mark the loan as paid
    $result = markLoanAsPaid($loanId, $amountPaid, $paymentDate);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $result['message']
        ]);
    } else {
        throw new Exception($result['message']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    error_log("Mark loan paid error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
