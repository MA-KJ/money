<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Mark Loan as Paid</h2>";

require_once 'includes/app.php';
require_once 'includes/loans.php';

// Check if logged in
if (!isLoggedIn()) {
    echo "❌ Not logged in<br>";
    exit;
}

echo "✅ Logged in as: " . getCurrentUser()['username'] . "<br><br>";

// Get a test loan
$loans = getLoans(['limit' => 1]);
if (empty($loans)) {
    echo "❌ No loans found. Create a loan first.<br>";
    exit;
}

$testLoan = $loans[0];
echo "Testing with loan ID: " . $testLoan['id'] . "<br>";
echo "Borrower: " . $testLoan['borrower_name'] . "<br>";
echo "Amount: K" . $testLoan['total_payable'] . "<br>";
echo "Status: " . $testLoan['status'] . "<br><br>";

// Test marking as paid
echo "<strong>Attempting to mark as paid...</strong><br>";

try {
    $result = markLoanAsPaid($testLoan['id'], null, date('Y-m-d'));
    
    if ($result['success']) {
        echo "✅ SUCCESS: " . $result['message'] . "<br>";
    } else {
        echo "❌ FAILED: " . $result['message'] . "<br>";
    }
    
    print_r($result);
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>
