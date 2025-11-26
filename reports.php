<?php
/**
 * Reports Page
 * Generate downloadable reports for different time periods
 */

require_once 'includes/app.php';
require_once 'includes/loans.php';

checkPageAccess(true);

$flash = getFlashMessage();
$period = sanitizeInput($_GET['period'] ?? '12_months');
$format = sanitizeInput($_GET['format'] ?? 'html');

// Validate period
if (!in_array($period, ['3_months', '6_months', '9_months', '12_months'])) {
    $period = '12_months';
}

// Get statistics for the selected period
$stats = getLoanStatistics($period);

// Get loan details for the period
$dateCondition = '';
$periodMonths = (int)str_replace('_months', '', $period);
$loans = $db->fetchAll(
    "SELECT * FROM loans 
     WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL {$periodMonths} MONTH)
     ORDER BY start_date DESC"
);

$periodLabel = str_replace('_', ' ', ucwords($period, '_'));

// Handle PDF/Excel export
if ($format === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="loan_report_' . $period . '.pdf"');
    // For now, we'll provide a simple HTML that can be printed as PDF
    // In production, you'd use a library like TCPDF or DOMPDF
}

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="loan_report_' . $period . '.xls"');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle('Reports - ' . $periodLabel); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <?php if ($format === 'pdf'): ?>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .table { page-break-inside: auto; }
            .table tr { page-break-inside: avoid; }
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php if ($format === 'html'): ?>
    <?php include 'includes/navigation.php'; ?>
    <?php endif; ?>
    
    <div class="container-fluid">
        <?php if ($format === 'html' && $flash): ?>
            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show no-print" role="alert">
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Report Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                            Loan Performance Report
                        </h2>
                        <p class="text-muted mb-0">
                            Analysis for <?php echo $periodLabel; ?> (<?php echo date('M j, Y', strtotime("-{$periodMonths} months")); ?> - <?php echo date('M j, Y'); ?>)
                        </p>
                    </div>
                    
                    <?php if ($format === 'html'): ?>
                    <div class="no-print">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-calendar-range"></i> <?php echo $periodLabel; ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?period=3_months">Last 3 Months</a></li>
                                <li><a class="dropdown-item" href="?period=6_months">Last 6 Months</a></li>
                                <li><a class="dropdown-item" href="?period=9_months">Last 9 Months</a></li>
                                <li><a class="dropdown-item" href="?period=12_months">Last 12 Months</a></li>
                            </ul>
                        </div>
                        
                        <div class="btn-group ms-2">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-download"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?period=<?php echo $period; ?>&format=pdf" target="_blank">
                                    <i class="bi bi-file-pdf"></i> PDF Report
                                </a></li>
                                <li><a class="dropdown-item" href="?period=<?php echo $period; ?>&format=excel">
                                    <i class="bi bi-file-excel"></i> Excel Export
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="javascript:window.print()">
                                    <i class="bi bi-printer"></i> Print Report
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up"></i>
                            Executive Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="text-center">
                                    <div class="display-6 text-primary fw-bold"><?php echo $stats['total_loans']; ?></div>
                                    <div class="small text-muted">Total Loans</div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="text-center">
                                    <div class="display-6 text-success fw-bold"><?php echo formatCurrency($stats['total_amount_lent']); ?></div>
                                    <div class="small text-muted">Capital Deployed</div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="text-center">
                                    <div class="display-6 text-info fw-bold"><?php echo formatCurrency($stats['total_interest_earned']); ?></div>
                                    <div class="small text-muted">Interest Earned</div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="text-center">
                                    <div class="display-6 text-warning fw-bold"><?php echo formatCurrency($stats['total_amount_repaid']); ?></div>
                                    <div class="small text-muted">Amount Recovered</div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Performance Metrics:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Success Rate:</strong> 
                                        <?php 
                                        $successRate = $stats['total_loans'] > 0 
                                            ? round(($stats['paid_loans'] / $stats['total_loans']) * 100, 1)
                                            : 0;
                                        echo $successRate . '%';
                                        ?>
                                    </li>
                                    <li><strong>Average ROI:</strong> 
                                        <?php 
                                        $roi = $stats['total_amount_lent'] > 0 
                                            ? round(($stats['total_interest_earned'] / $stats['total_amount_lent']) * 100, 1)
                                            : 0;
                                        echo $roi . '%';
                                        ?>
                                    </li>
                                    <li><strong>Recovery Rate:</strong> 
                                        <?php 
                                        $recovery = $stats['total_amount_lent'] > 0 
                                            ? round(($stats['total_amount_repaid'] / $stats['total_amount_lent']) * 100, 1)
                                            : 0;
                                        echo $recovery . '%';
                                        ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Loan Status Breakdown:</h6>
                                <ul class="list-unstyled">
                                    <li><span class="badge bg-success"><?php echo $stats['paid_loans']; ?></span> Paid Loans</li>
                                    <li><span class="badge bg-warning text-dark"><?php echo $stats['unpaid_loans']; ?></span> Unpaid Loans</li>
                                    <li><span class="badge bg-danger"><?php echo $stats['overdue_loans']; ?></span> Overdue Loans</li>
                                    <li><span class="badge bg-info"><?php echo $stats['partially_paid_loans']; ?></span> Partially Paid</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Loan Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i>
                            Detailed Loan Records (<?php echo count($loans); ?> loans)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($loans)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Borrower</th>
                                            <th>Principal</th>
                                            <th>Interest Rate</th>
                                            <th>Interest Amount</th>
                                            <th>Total Payable</th>
                                            <th>Start Date</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Amount Paid</th>
                                            <th>Date Paid</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loans as $loan): ?>
                                        <tr>
                                            <td><?php echo $loan['id']; ?></td>
                                            <td><?php echo htmlspecialchars($loan['borrower_name']); ?></td>
                                            <td><?php echo formatCurrency($loan['loan_amount']); ?></td>
                                            <td><?php echo $loan['interest_rate']; ?>%</td>
                                            <td><?php echo formatCurrency($loan['total_payable'] - $loan['loan_amount']); ?></td>
                                            <td><?php echo formatCurrency($loan['total_payable']); ?></td>
                                            <td><?php echo formatDate($loan['start_date']); ?></td>
                                            <td><?php echo formatDate($loan['due_date']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getStatusClass($loan['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $loan['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatCurrency($loan['amount_paid']); ?></td>
                                            <td>
                                                <?php if ($loan['date_paid']): ?>
                                                    <?php echo formatDate($loan['date_paid']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="2">TOTALS</th>
                                            <th><?php echo formatCurrency($stats['total_amount_lent']); ?></th>
                                            <th>-</th>
                                            <th><?php echo formatCurrency($stats['total_interest_earned']); ?></th>
                                            <th><?php echo formatCurrency($stats['total_amount_lent'] + $stats['total_interest_earned']); ?></th>
                                            <th colspan="3">-</th>
                                            <th><?php echo formatCurrency($stats['total_amount_repaid']); ?></th>
                                            <th>-</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="text-muted mt-3">No loans found for the selected period.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Footer -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted">
                        <small>
                            Report generated on <?php echo date('F j, Y \a\t g:i A'); ?> by <?php echo htmlspecialchars(getCurrentUser()['full_name']); ?>
                            <br>
                            <?php echo getSetting('site_name', 'Loan Tracking System'); ?> - Professional Loan Management
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($format === 'html'): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-print for PDF format
        <?php if (isset($_GET['print']) && $_GET['print'] === '1'): ?>
        window.onload = function() {
            window.print();
        };
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>
