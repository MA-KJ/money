<?php

/**
 * Loan Management Interface
 * View all loans in the system (Super Admin & Admin only)
 */
require_once 'includes/app.php';
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/security.php';
require_once 'includes/loans.php';

// Check authentication and authorization
checkPageAccess(true);

// Get loan ID from URL
$loanId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($loanId <= 0) {
    $_SESSION['error'] = 'Invalid loan ID.';
    header('Location: loan.php');
    exit();
}

// Get loan details
$loan = getLoan($loanId);
if (!$loan) {
    $_SESSION['error'] = 'Loan not found.';
    header('Location: loan.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanData = [
        'borrower_name' => sanitizeInput($_POST['borrower_name']),
        'borrower_phone' => sanitizeInput($_POST['borrower_phone']),
        'borrower_email' => sanitizeInput($_POST['borrower_email']),
        'borrower_address' => sanitizeInput($_POST['borrower_address']),
        'loan_amount' => sanitizeInput($_POST['loan_amount']),
        'interest_rate' => sanitizeInput($_POST['interest_rate']),
        'duration_days' => sanitizeInput($_POST['duration_days']),
        'notes' => sanitizeInput($_POST['notes'])
    ];

    $result = updateLoan($loanId, $loanData);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: view_loan.php?id=' . $loanId);
        exit();
    } else {
        $_SESSION['error'] = $result['message'];
    }
}

$page_title = "Edit Loan #" . $loan['id'];
$active_page = "loans";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Loan Management System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3">
                            <i class="fas fa-edit me-2"></i>
                            Edit Loan
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="loan.php">Loans</a></li>
                                <li class="breadcrumb-item"><a href="view_loan.php?id=<?php echo $loanId; ?>">Loan #<?php echo $loanId; ?></a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="view_loan.php?id=<?php echo $loanId; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Loan
                        </a>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Edit Loan Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-pencil-alt me-2"></i>Edit Loan Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="editLoanForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="borrower_name" class="form-label">Borrower Name *</label>
                                        <input type="text" class="form-control" id="borrower_name" name="borrower_name"
                                            value="<?php echo htmlspecialchars($loan['borrower_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="borrower_phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="borrower_phone" name="borrower_phone"
                                            value="<?php echo htmlspecialchars($loan['borrower_phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="borrower_email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="borrower_email" name="borrower_email"
                                            value="<?php echo htmlspecialchars($loan['borrower_email'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="loan_amount" class="form-label">Loan Amount (K) *</label>
                                        <input type="number" class="form-control" id="loan_amount" name="loan_amount"
                                            step="0.01" min="0" value="<?php echo htmlspecialchars($loan['loan_amount']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="interest_rate" class="form-label">Interest Rate (%) *</label>
                                        <input type="number" class="form-control" id="interest_rate" name="interest_rate"
                                            step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($loan['interest_rate']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="duration_days" class="form-label">Duration (Days) *</label>
                                        <input type="number" class="form-control" id="duration_days" name="duration_days"
                                            min="1" max="3650" value="<?php echo htmlspecialchars($loan['duration_days']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="borrower_address" class="form-label">Borrower Address</label>
                                <textarea class="form-control" id="borrower_address" name="borrower_address"
                                    rows="3"><?php echo htmlspecialchars($loan['borrower_address'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes"
                                    rows="4"><?php echo htmlspecialchars($loan['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> Changing the loan amount or interest rate will automatically recalculate the total payable amount.
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="view_loan.php?id=<?php echo $loanId; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Loan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Recalculate total payable when loan amount or interest rate changes
            function recalculateTotal() {
                const loanAmount = parseFloat($('#loan_amount').val()) || 0;
                const interestRate = parseFloat($('#interest_rate').val()) || 0;
                const interestAmount = loanAmount * (interestRate / 100);
                const totalPayable = loanAmount + interestAmount;

                // You can display this to the user if needed
                console.log('New total payable: K' + totalPayable.toFixed(2));
            }

            $('#loan_amount, #interest_rate').on('input', recalculateTotal);
        });
    </script>
</body>

</html>