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
    header('Location: loans.php');
    exit();
}

// Get loan details
$loan = getLoan($loanId);
if (!$loan) {
    $_SESSION['error'] = 'Loan not found.';
    header('Location: loans.php');
    exit();
}

// Get loan payments
$payments = getLoanPayments($loanId);

$page_title = "View Loan #" . $loan['id'];
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

    <style>
        .loan-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .status-badge {
            font-size: 0.9em;
            padding: 0.5em 1em;
        }

        .info-card {
            border-left: 4px solid #007bff;
        }

        .payment-card {
            border-left: 4px solid #28a745;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Loan Header -->
    <div class="loan-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 mb-1">
                        <i class="fas fa-hand-holding-usd me-2"></i>
                        Loan Details
                    </h1>
                    <p class="mb-0 opacity-75">Loan #<?php echo $loan['id']; ?> - <?php echo htmlspecialchars($loan['borrower_name']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="loans.php" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Loans
                    </a>
                    <a href="edit_loan.php?id=<?php echo $loan['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit Loan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Loan Information -->
            <div class="col-lg-8">
                <div class="card info-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Loan Information
                        </h5>
                        <?php
                        $status_badge = [
                            'unpaid' => 'warning',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            'partially_paid' => 'info'
                        ];
                        $badge_class = $status_badge[$loan['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?php echo $badge_class; ?> status-badge">
                            <?php echo ucfirst(str_replace('_', ' ', $loan['status'])); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Borrower Name:</th>
                                        <td><?php echo htmlspecialchars($loan['borrower_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td><?php echo !empty($loan['borrower_phone']) ? htmlspecialchars($loan['borrower_phone']) : '<span class="text-muted">N/A</span>'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo !empty($loan['borrower_email']) ? htmlspecialchars($loan['borrower_email']) : '<span class="text-muted">N/A</span>'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Loan Amount:</th>
                                        <td class="fw-bold">K<?php echo number_format($loan['loan_amount'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Interest Rate:</th>
                                        <td><?php echo htmlspecialchars($loan['interest_rate']); ?>%</td>
                                    </tr>
                                    <tr>
                                        <th>Total Payable:</th>
                                        <td class="fw-bold text-success">K<?php echo number_format($loan['total_payable'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Amount Paid:</th>
                                        <td class="fw-bold text-primary">K<?php echo number_format($loan['amount_paid'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Remaining Balance:</th>
                                        <td class="fw-bold text-danger">K<?php echo number_format($loan['total_payable'] - $loan['amount_paid'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Start Date:</th>
                                        <td><?php echo date('F j, Y', strtotime($loan['start_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Due Date:</th>
                                        <td>
                                            <?php
                                            $due_date_class = '';
                                            if ($loan['status'] !== 'paid' && strtotime($loan['due_date']) < time()) {
                                                $due_date_class = 'text-danger fw-bold';
                                            }
                                            ?>
                                            <span class="<?php echo $due_date_class; ?>">
                                                <?php echo date('F j, Y', strtotime($loan['due_date'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Duration:</th>
                                        <td><?php echo $loan['duration_days']; ?> days</td>
                                    </tr>
                                    <tr>
                                        <th>Created By:</th>
                                        <td><?php echo htmlspecialchars($loan['created_by_name'] ?? 'System'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if (!empty($loan['borrower_address'])): ?>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong>Address:</strong>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($loan['borrower_address'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($loan['notes'])): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <strong>Notes:</strong>
                                    <div class="border rounded p-3 bg-light">
                                        <?php echo nl2br(htmlspecialchars($loan['notes'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Actions & Status -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($loan['status'] !== 'paid'): ?>
                                <a href="add_payment.php?loan_id=<?php echo $loan['id']; ?>" class="btn btn-success">
                                    <i class="fas fa-money-bill-wave me-1"></i> Add Payment
                                </a>
                                <form method="POST" action="view_loans.php" class="d-grid">
                                    <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                    <input type="hidden" name="action" value="mark_paid">
                                    <button type="submit" class="btn btn-outline-success"
                                        onclick="return confirm('Mark this loan as fully paid?')">
                                        <i class="fas fa-check me-1"></i> Mark as Paid
                                    </button>
                                </form>
                            <?php endif; ?>

                            <a href="edit_loan.php?id=<?php echo $loan['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i> Edit Loan
                            </a>

                            <?php if (isSuperAdmin() || $loan['status'] !== 'paid'): ?>
                                <form method="POST" action="loan.php" class="d-grid">
                                    <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                    <input type="hidden" name="action" value="delete_loan">
                                    <button type="submit" class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this loan? This action cannot be undone!')">
                                        <i class="fas fa-trash me-1"></i> Delete Loan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card payment-card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Payment Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <h4 class="text-success">K<?php echo number_format($loan['amount_paid'], 2); ?></h4>
                            <p class="text-muted mb-2">Total Paid</p>
                            <div class="progress mb-3" style="height: 10px;">
                                <?php
                                $progress = ($loan['total_payable'] > 0) ? ($loan['amount_paid'] / $loan['total_payable']) * 100 : 0;
                                $progress_class = ($progress >= 100) ? 'bg-success' : (($progress >= 50) ? 'bg-primary' : 'bg-warning');
                                ?>
                                <div class="progress-bar <?php echo $progress_class; ?>"
                                    role="progressbar"
                                    style="width: <?php echo $progress; ?>%"
                                    aria-valuenow="<?php echo $progress; ?>"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php echo number_format($progress, 1); ?>% Paid
                                (<?php echo count($payments); ?> payment<?php echo count($payments) !== 1 ? 's' : ''; ?>)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <?php if (!empty($payments)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-receipt me-2"></i>Payment History
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Recorded By</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                                                <td class="fw-bold text-success">K<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><?php echo !empty($payment['payment_method']) ? htmlspecialchars($payment['payment_method']) : '<span class="text-muted">N/A</span>'; ?></td>
                                                <td><?php echo htmlspecialchars($payment['recorded_by_name'] ?? 'System'); ?></td>
                                                <td><?php echo !empty($payment['notes']) ? htmlspecialchars($payment['notes']) : '<span class="text-muted">No notes</span>'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>