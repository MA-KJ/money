<?php

/**
 * Dashboard Page
 * Main overview of loan system
 */

require_once 'includes/app.php';
require_once 'includes/loans.php';

checkPageAccess(true);

// Get statistics and recent loans
$stats = getLoanStatistics('12_months');
$recentLoans = getLoans(['limit' => 10]);
$overdueLoans = getLoans(['overdue_only' => true, 'limit' => 5]);

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle('Dashboard'); ?></title>
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

        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">
                    <i class="bi bi-speedometer2 text-primary"></i>
                    Welcome back, <?php echo htmlspecialchars(getCurrentUser()['full_name']); ?>!
                </h2>
                <p class="text-muted mb-0">
                    Here's an overview of your loan tracking system as of <?php echo date('F j, Y'); ?>
                </p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-list-check display-6 mb-2"></i>
                        <div class="stats-number"><?php echo $stats['total_loans']; ?></div>
                        <div class="stats-label">Total Loans</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle display-6 mb-2"></i>
                        <div class="stats-number"><?php echo $stats['paid_loans']; ?></div>
                        <div class="stats-label">Paid Loans</div>
                        <small class="opacity-75">
                            (<?php echo $stats['total_loans'] > 0 ? round(($stats['paid_loans'] / $stats['total_loans']) * 100, 1) : 0; ?>%)
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-warning text-dark h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clock display-6 mb-2"></i>
                        <div class="stats-number"><?php echo $stats['unpaid_loans']; ?></div>
                        <div class="stats-label">Pending Loans</div>
                        <small class="opacity-75">
                            Balance: <?php echo formatCurrency($stats['total_amount_lent'] - $stats['total_amount_repaid']); ?>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-danger text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle display-6 mb-2"></i>
                        <div class="stats-number"><?php echo $stats['overdue_loans']; ?></div>
                        <div class="stats-label">Overdue Loans</div>
                        <?php if ($stats['overdue_loans'] > 0): ?>
                            <small class="opacity-75">Needs attention!</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <i class="bi bi-cash-coin text-primary"></i>
                            Total Lent
                        </h5>
                        <div class="display-6 text-primary fw-bold">
                            <?php echo formatCurrency($stats['total_amount_lent']); ?>
                        </div>
                        <small class="text-muted">Capital deployed</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up text-success"></i>
                            Interest Earned
                        </h5>
                        <div class="display-6 text-success fw-bold">
                            <?php echo formatCurrency($stats['total_interest_earned']); ?>
                        </div>
                        <small class="text-muted">Profit generated</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <i class="bi bi-arrow-down-circle text-info"></i>
                            Total Repaid
                        </h5>
                        <div class="display-6 text-info fw-bold">
                            <?php echo formatCurrency($stats['total_amount_repaid']); ?>
                        </div>
                        <small class="text-muted">Money recovered</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Recent Loans -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i>
                            Recent Loans
                        </h5>
                        <a href="loans.php" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($recentLoans)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Borrower</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($recentLoans, 0, 5) as $loan): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($loan['borrower_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Started <?php echo formatDate($loan['start_date']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong><?php echo formatCurrency($loan['loan_amount']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Total: <?php echo formatCurrency($loan['total_payable']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php echo formatDate($loan['due_date']); ?>
                                                    <?php
                                                    $daysUntilDue = daysBetween(date('Y-m-d'), $loan['due_date']);
                                                    if ($loan['status'] !== 'paid'):
                                                    ?>
                                                        <br>
                                                        <small class="<?php echo $daysUntilDue < 0 ? 'text-danger' : ($daysUntilDue <= 7 ? 'text-warning' : 'text-muted'); ?>">
                                                            <?php
                                                            if ($daysUntilDue < 0) {
                                                                echo abs($daysUntilDue) . ' days overdue';
                                                            } elseif ($daysUntilDue == 0) {
                                                                echo 'Due today';
                                                            } else {
                                                                echo $daysUntilDue . ' days left';
                                                            }
                                                            ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusClass($loan['status']); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $loan['status'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view_loan.php?id=<?php echo $loan['id']; ?>"
                                                            class="btn btn-outline-primary btn-sm" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($loan['status'] !== 'paid'): ?>
                                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                                title="Mark as Paid"
                                                                onclick="markAsPaid(<?php echo $loan['id']; ?>, '<?php echo htmlspecialchars($loan['borrower_name']); ?>')">
                                                                <i class="bi bi-check-circle"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="text-muted mt-3">No loans found. <a href="add-loan.php">Add your first loan</a>.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Overdue Loans Alert -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            Overdue Loans
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($overdueLoans)): ?>
                            <?php foreach ($overdueLoans as $loan): ?>
                                <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                    <div class="flex-grow-1">
                                        <strong><?php echo htmlspecialchars($loan['borrower_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo formatCurrency($loan['total_payable']); ?> due <?php echo formatDate($loan['due_date']); ?>
                                        </small>
                                        <br>
                                        <small class="text-danger">
                                            <?php echo daysBetween($loan['due_date'], date('Y-m-d')); ?> days overdue
                                        </small>
                                    </div>
                                    <div class="btn-group-vertical btn-group-sm">
                                        <a href="loan-details.php?id=<?php echo $loan['id']; ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            onclick="markAsPaid(<?php echo $loan['id']; ?>, '<?php echo htmlspecialchars($loan['borrower_name']); ?>')">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (count($overdueLoans) > 5): ?>
                                <div class="text-center">
                                    <a href="loans.php?filter=overdue" class="btn btn-sm btn-danger">
                                        View All Overdue Loans
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle-fill text-success display-4"></i>
                                <p class="text-success mt-2 mb-0">No overdue loans!</p>
                                <small class="text-muted">All loans are on track.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning"></i>
                            Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="add-loan.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i>
                                Add New Loan
                            </a>
                            <a href="loans.php" class="btn btn-outline-primary">
                                <i class="bi bi-list"></i>
                                View All Loans
                            </a>
                            <a href="reports.php" class="btn btn-outline-info">
                                <i class="bi bi-file-earmark-text"></i>
                                Generate Report
                            </a>
                            <?php if (isSuperAdmin()): ?>
                                <a href="users.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-people"></i>
                                    Manage Users
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div class="modal fade" id="markPaidModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Loan as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="markPaidForm">
                    <div class="modal-body">
                        <input type="hidden" id="loanId" name="loan_id">
                        <p>Are you sure you want to mark the loan for <strong id="borrowerName"></strong> as paid?</p>

                        <div class="mb-3">
                            <label for="paymentDate" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="paymentDate" name="payment_date"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="amountPaid" class="form-label">Amount Paid (optional)</label>
                            <input type="number" class="form-control" id="amountPaid" name="amount_paid"
                                step="0.01" min="0" placeholder="Leave blank for full amount">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Mark as Paid</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAsPaid(loanId, borrowerName) {
            document.getElementById('loanId').value = loanId;
            document.getElementById('borrowerName').textContent = borrowerName;
            new bootstrap.Modal(document.getElementById('markPaidModal')).show();
        }

        document.getElementById('markPaidForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('api/mark-loan-paid.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Server error');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Loan marked as paid successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                });
        });

        // Auto-refresh page every 5 minutes to update overdue status
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>

</html>