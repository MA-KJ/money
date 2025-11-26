<?php
/**
 * Add Loan Page
 * Form to create new loans
 */

require_once 'includes/app.php';
require_once 'includes/loans.php';

checkPageAccess(true);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_loan'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize input data
        $loanData = [
            'borrower_name' => sanitizeInput($_POST['borrower_name'] ?? ''),
            'borrower_phone' => sanitizeInput($_POST['borrower_phone'] ?? ''),
            'borrower_email' => sanitizeInput($_POST['borrower_email'] ?? '', 'email'),
            'borrower_address' => sanitizeInput($_POST['borrower_address'] ?? ''),
            'loan_amount' => sanitizeInput($_POST['loan_amount'] ?? '', 'float'),
            'interest_rate' => sanitizeInput($_POST['interest_rate'] ?? '', 'float'),
            'duration_days' => sanitizeInput($_POST['duration_days'] ?? '', 'int'),
            'notes' => sanitizeInput($_POST['notes'] ?? '')
        ];
        
        // Create the loan
        $result = createLoan($loanData);
        
        if ($result['success']) {
            redirect(
                SITE_URL . '/dashboard.php', 
                "Loan created successfully! Total payable: " . formatCurrency($result['total_payable']) . 
                ", Due date: " . formatDate($result['due_date']), 
                'success'
            );
        } else {
            $error = $result['message'];
        }
    }
}

$flash = getFlashMessage();
$defaultInterestRate = getSetting('default_interest_rate', '10.00');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle('Add New Loan'); ?></title>
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

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-plus-circle"></i>
                            Add New Loan
                        </h4>
                        <p class="mb-0 small">Enter loan details to track a new borrower</p>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="addLoanForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <!-- Borrower Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-person"></i>
                                        Borrower Information
                                    </h5>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="borrower_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person-fill"></i>
                                        </span>
                                        <input type="text" class="form-control" id="borrower_name" name="borrower_name" 
                                               value="<?php echo htmlspecialchars($loanData['borrower_name'] ?? ''); ?>" 
                                               required placeholder="e.g., John Banda">
                                    </div>
                                    <div class="form-text">The full name of the person borrowing money</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="borrower_phone" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-telephone"></i>
                                        </span>
                                        <input type="tel" class="form-control" id="borrower_phone" name="borrower_phone" 
                                               value="<?php echo htmlspecialchars($loanData['borrower_phone'] ?? ''); ?>" 
                                               placeholder="e.g., +260 97 123 4567">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="borrower_email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="borrower_email" name="borrower_email" 
                                               value="<?php echo htmlspecialchars($loanData['borrower_email'] ?? ''); ?>" 
                                               placeholder="e.g., john@example.com">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="borrower_address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-geo-alt"></i>
                                        </span>
                                        <input type="text" class="form-control" id="borrower_address" name="borrower_address" 
                                               value="<?php echo htmlspecialchars($loanData['borrower_address'] ?? ''); ?>" 
                                               placeholder="e.g., Plot 123, Lusaka">
                                    </div>
                                </div>
                            </div>

                            <!-- Loan Details -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-cash-coin"></i>
                                        Loan Details
                                    </h5>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="loan_amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo getSetting('currency_symbol', 'K'); ?></span>
                                        <input type="number" class="form-control" id="loan_amount" name="loan_amount" 
                                               value="<?php echo htmlspecialchars($loanData['loan_amount'] ?? ''); ?>" 
                                               required step="0.01" min="1" placeholder="1000.00"
                                               onchange="calculateTotal()">
                                    </div>
                                    <div class="form-text">The principal amount being lent</div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="interest_rate" class="form-label">Interest Rate <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="interest_rate" name="interest_rate" 
                                               value="<?php echo htmlspecialchars($loanData['interest_rate'] ?? $defaultInterestRate); ?>" 
                                               required step="0.01" min="0" max="100" placeholder="10.00"
                                               onchange="calculateTotal()">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">Interest percentage on the principal amount</div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="duration_days" class="form-label">Duration <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="duration_days" name="duration_days" 
                                               value="<?php echo htmlspecialchars($loanData['duration_days'] ?? '14'); ?>" 
                                               required min="1" max="3650" placeholder="14"
                                               onchange="calculateTotal()">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Loan period in days</div>
                                </div>
                            </div>

                            <!-- Calculation Results -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-calculator"></i>
                                                Loan Calculation
                                            </h6>
                                            <div class="row text-center">
                                                <div class="col-md-3">
                                                    <div class="mb-2">
                                                        <strong>Principal Amount</strong>
                                                        <div class="h5 text-primary" id="principal_display"><?php echo getSetting('currency_symbol', 'K'); ?>0.00</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-2">
                                                        <strong>Interest Amount</strong>
                                                        <div class="h5 text-info" id="interest_display"><?php echo getSetting('currency_symbol', 'K'); ?>0.00</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-2">
                                                        <strong>Total Payable</strong>
                                                        <div class="h5 text-success" id="total_display"><?php echo getSetting('currency_symbol', 'K'); ?>0.00</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-2">
                                                        <strong>Due Date</strong>
                                                        <div class="h5 text-warning" id="due_date_display">--</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Any additional information about this loan..."><?php echo htmlspecialchars($loanData['notes'] ?? ''); ?></textarea>
                                    <div class="form-text">Optional notes about the loan terms, agreements, or conditions</div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="dashboard.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i>
                                            Cancel
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary" onclick="resetCalculations()">
                                            <i class="bi bi-arrow-clockwise"></i>
                                            Reset
                                        </button>
                                        <button type="submit" name="add_loan" class="btn btn-success">
                                            <i class="bi bi-plus-circle"></i>
                                            Create Loan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const currencySymbol = '<?php echo getSetting('currency_symbol', 'K'); ?>';
        
        function calculateTotal() {
            const principal = parseFloat(document.getElementById('loan_amount').value) || 0;
            const interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
            const duration = parseInt(document.getElementById('duration_days').value) || 0;
            
            const interestAmount = principal * (interestRate / 100);
            const totalPayable = principal + interestAmount;
            
            // Update displays
            document.getElementById('principal_display').textContent = currencySymbol + principal.toFixed(2);
            document.getElementById('interest_display').textContent = currencySymbol + interestAmount.toFixed(2);
            document.getElementById('total_display').textContent = currencySymbol + totalPayable.toFixed(2);
            
            // Calculate due date
            if (duration > 0) {
                const today = new Date();
                const dueDate = new Date(today);
                dueDate.setDate(today.getDate() + duration);
                
                const options = { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                };
                document.getElementById('due_date_display').textContent = dueDate.toLocaleDateString('en-US', options);
            } else {
                document.getElementById('due_date_display').textContent = '--';
            }
        }
        
        function resetCalculations() {
            setTimeout(function() {
                calculateTotal();
            }, 10);
        }
        
        // Initialize calculations on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotal();
            
            // Add real-time calculation on input
            document.getElementById('loan_amount').addEventListener('input', calculateTotal);
            document.getElementById('interest_rate').addEventListener('input', calculateTotal);
            document.getElementById('duration_days').addEventListener('input', calculateTotal);
        });
        
        // Form validation
        document.getElementById('addLoanForm').addEventListener('submit', function(e) {
            const principal = parseFloat(document.getElementById('loan_amount').value) || 0;
            const interestRate = parseFloat(document.getElementById('interest_rate').value) || -1;
            const duration = parseInt(document.getElementById('duration_days').value) || 0;
            
            if (principal <= 0) {
                e.preventDefault();
                alert('Please enter a valid loan amount greater than 0.');
                document.getElementById('loan_amount').focus();
                return;
            }
            
            if (interestRate < 0 || interestRate > 100) {
                e.preventDefault();
                alert('Please enter a valid interest rate between 0 and 100.');
                document.getElementById('interest_rate').focus();
                return;
            }
            
            if (duration <= 0 || duration > 3650) {
                e.preventDefault();
                alert('Please enter a valid duration between 1 and 3650 days.');
                document.getElementById('duration_days').focus();
                return;
            }
        });
    </script>
</body>
</html>
