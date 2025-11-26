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

$page_title = "Loan Management";
$active_page = "loans";

// Handle filters
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Apply filters
    if (!empty($_POST['borrower_name'])) {
        $filters['borrower_name'] = sanitizeInput($_POST['borrower_name']);
    }
    if (!empty($_POST['status'])) {
        $filters['status'] = sanitizeInput($_POST['status']);
    }
    if (!empty($_POST['date_from'])) {
        $filters['date_from'] = sanitizeInput($_POST['date_from']);
    }
    if (!empty($_POST['date_to'])) {
        $filters['date_to'] = sanitizeInput($_POST['date_to']);
    }
    if (!empty($_POST['overdue_only'])) {
        $filters['overdue_only'] = true;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['status'])) {
    // Quick filter from URL
    $filters['status'] = sanitizeInput($_GET['status']);
}

// Get loans with filters
$loans = getLoans($filters);

// Get statistics for dashboard
$stats = getLoanStatistics();

// Handle loan actions
if (isset($_POST['action'])) {
    $loanId = isset($_POST['loan_id']) ? intval($_POST['loan_id']) : 0;

    switch ($_POST['action']) {
        case 'mark_paid':
            if ($loanId > 0) {
                $result = markLoanAsPaid($loanId);
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
            break;

        case 'delete_loan':
            if ($loanId > 0) {
                $result = deleteLoan($loanId);
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
            break;
    }

    // Redirect to avoid form resubmission
    header('Location: loans.php');
    exit();
}
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
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .status-badge {
            font-size: 0.8em;
            padding: 0.35em 0.65em;
        }

        .loan-card {
            transition: transform 0.2s;
        }

        .loan-card:hover {
            transform: translateY(-2px);
        }

        .stat-card {
            border-left: 4px solid;
        }

        .stat-total {
            border-left-color: #007bff;
        }

        .stat-paid {
            border-left-color: #28a745;
        }

        .stat-unpaid {
            border-left-color: #ffc107;
        }

        .stat-overdue {
            border-left-color: #dc3545;
        }

        .table-actions {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h2">
                    <i class="fas fa-hand-holding-usd me-2"></i>
                    <?php echo htmlspecialchars($page_title); ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Loans</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="add-loan.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Loan
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-total">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Total Loans</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_loans']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-list-alt fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-paid">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Paid Loans</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['paid_loans']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-unpaid">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Unpaid Loans</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['unpaid_loans']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-overdue">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Overdue Loans</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['overdue_loans']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>Filter Loans
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="borrower_name" class="form-label">Borrower Name</label>
                            <input type="text" class="form-control" id="borrower_name" name="borrower_name"
                                value="<?php echo isset($filters['borrower_name']) ? htmlspecialchars($filters['borrower_name']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="unpaid" <?php echo (isset($filters['status']) && $filters['status'] === 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="paid" <?php echo (isset($filters['status']) && $filters['status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="overdue" <?php echo (isset($filters['status']) && $filters['status'] === 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                                <option value="partially_paid" <?php echo (isset($filters['status']) && $filters['status'] === 'partially_paid') ? 'selected' : ''; ?>>Partially Paid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                value="<?php echo isset($filters['date_from']) ? htmlspecialchars($filters['date_from']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                value="<?php echo isset($filters['date_to']) ? htmlspecialchars($filters['date_to']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" id="overdue_only" name="overdue_only"
                                    <?php echo (isset($filters['overdue_only']) && $filters['overdue_only']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="overdue_only">
                                    Overdue Only
                                </label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="d-grid gap-2 mt-4 pt-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loans Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>All Loans
                </h5>
                <div class="btn-group">
                    <a href="loans.php?status=overdue" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                    </a>
                    <a href="loans.php?status=unpaid" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-clock me-1"></i>Unpaid
                    </a>
                    <a href="loans.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-sync me-1"></i>Clear
                    </a>
                </div>
            </div>
            <div class="card-body">
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

                <div class="table-responsive">
                    <table id="loansTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Borrower</th>
                                <th>Loan Amount</th>
                                <th>Total Payable</th>
                                <th>Paid</th>
                                <th>Interest Rate</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>Due Date</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loan['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($loan['borrower_name']); ?></strong>
                                        <?php if (!empty($loan['borrower_phone'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($loan['borrower_phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>K<?php echo number_format($loan['loan_amount'], 2); ?></td>
                                    <td>K<?php echo number_format($loan['total_payable'], 2); ?></td>
                                    <td>K<?php echo number_format($loan['amount_paid'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($loan['interest_rate']); ?>%</td>
                                    <td>
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
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($loan['start_date'])); ?></td>
                                    <td>
                                        <?php
                                        $due_date_class = '';
                                        if ($loan['status'] !== 'paid' && strtotime($loan['due_date']) < time()) {
                                            $due_date_class = 'text-danger fw-bold';
                                        }
                                        ?>
                                        <span class="<?php echo $due_date_class; ?>">
                                            <?php echo date('M j, Y', strtotime($loan['due_date'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($loan['created_by_name'] ?? 'System'); ?></td>
                                    <td class="table-actions">
                                        <div class="btn-group btn-group-sm">
                                            <a href="view_loan.php?id=<?php echo $loan['id']; ?>"
                                                class="btn btn-outline-primary"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_loan.php?id=<?php echo $loan['id']; ?>"
                                                class="btn btn-outline-secondary"
                                                title="Edit Loan">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($loan['status'] !== 'paid'): ?>
                                                <button type="button"
                                                    class="btn btn-outline-success mark-paid-btn"
                                                    data-loan-id="<?php echo $loan['id']; ?>"
                                                    data-borrower-name="<?php echo htmlspecialchars($loan['borrower_name']); ?>"
                                                    title="Mark as Paid">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (isSuperAdmin() || $loan['status'] !== 'paid'): ?>
                                                <button type="button"
                                                    class="btn btn-outline-danger delete-loan-btn"
                                                    data-loan-id="<?php echo $loan['id']; ?>"
                                                    data-borrower-name="<?php echo htmlspecialchars($loan['borrower_name']); ?>"
                                                    title="Delete Loan">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($loans)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4>No loans found</h4>
                        <p class="text-muted">
                            <?php echo !empty($filters) ? 'Try adjusting your filters' : 'Get started by creating your first loan'; ?>
                        </p>
                        <?php if (empty($filters)): ?>
                            <a href="add-loan.php" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i> Create New Loan
                            </a>
                        <?php else: ?>
                            <a href="loan.php" class="btn btn-outline-secondary mt-2">
                                <i class="fas fa-times me-1"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div class="modal fade" id="markPaidModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Loan as Paid</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to mark this loan as paid?</p>
                        <p><strong>Borrower:</strong> <span id="paidBorrowerName"></span></p>
                        <input type="hidden" name="loan_id" id="paidLoanId">
                        <input type="hidden" name="action" value="mark_paid">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Mark as Paid</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Loan Modal -->
    <div class="modal fade" id="deleteLoanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Delete Loan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone!
                        </div>
                        <p>Are you sure you want to delete this loan?</p>
                        <p><strong>Borrower:</strong> <span id="deleteBorrowerName"></span></p>
                        <input type="hidden" name="loan_id" id="deleteLoanId">
                        <input type="hidden" name="action" value="delete_loan">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#loansTable').DataTable({
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                language: {
                    search: "Search loans:",
                    lengthMenu: "Show _MENU_ loans per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ loans",
                    infoEmpty: "No loans available",
                    infoFiltered: "(filtered from _MAX_ total loans)"
                }
            });

            // Mark as Paid modal
            $('.mark-paid-btn').click(function() {
                const loanId = $(this).data('loan-id');
                const borrowerName = $(this).data('borrower-name');

                $('#paidLoanId').val(loanId);
                $('#paidBorrowerName').text(borrowerName);
                $('#markPaidModal').modal('show');
            });

            // Delete Loan modal
            $('.delete-loan-btn').click(function() {
                const loanId = $(this).data('loan-id');
                const borrowerName = $(this).data('borrower-name');

                $('#deleteLoanId').val(loanId);
                $('#deleteBorrowerName').text(borrowerName);
                $('#deleteLoanModal').modal('show');
            });

            // Auto-submit filter form when overdue checkbox is checked
            $('#overdue_only').change(function() {
                if ($(this).is(':checked')) {
                    $('#filterForm').submit();
                }
            });
        });
    </script>
</body>

</html>