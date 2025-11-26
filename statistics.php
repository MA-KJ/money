<?php
/**
 * Statistics Page
 * Visual charts showing income, paid vs unpaid loans, etc.
 */

require_once 'includes/app.php';
require_once 'includes/loans.php';
require_once 'includes/stats.php';

checkPageAccess(true);

$flash = getFlashMessage();

// Get data for charts
$monthlyIncome = getMonthlyInterestIncome(12);
$statusDistribution = getLoanStatusDistribution();
$topBorrowers = getInterestByBorrower(12, 10);
$overallStats = getLoanStatistics('12_months');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getPageTitle('Statistics & Charts'); ?></title>
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

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">
                    <i class="bi bi-bar-chart text-primary"></i>
                    Statistics & Analytics
                </h2>
                <p class="text-muted mb-0">
                    Visual insights into your loan performance over the past 12 months
                </p>
            </div>
        </div>

        <!-- Key Metrics Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card bg-gradient bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-cash-stack display-6 mb-2"></i>
                        <div class="h4"><?php echo formatCurrency($overallStats['total_interest_earned']); ?></div>
                        <div class="small">Total Interest Earned</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card bg-gradient bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-percent display-6 mb-2"></i>
                        <div class="h4">
                            <?php 
                            $rate = $overallStats['total_loans'] > 0 
                                ? round(($overallStats['paid_loans'] / $overallStats['total_loans']) * 100, 1)
                                : 0;
                            echo $rate . '%';
                            ?>
                        </div>
                        <div class="small">Payment Success Rate</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card bg-gradient bg-warning text-dark h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clock display-6 mb-2"></i>
                        <div class="h4"><?php echo $overallStats['unpaid_loans']; ?></div>
                        <div class="small">Active Loans</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card bg-gradient bg-info text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up display-6 mb-2"></i>
                        <div class="h4">
                            <?php 
                            $roi = $overallStats['total_amount_lent'] > 0 
                                ? round(($overallStats['total_interest_earned'] / $overallStats['total_amount_lent']) * 100, 1)
                                : 0;
                            echo $roi . '%';
                            ?>
                        </div>
                        <div class="small">Average ROI</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Monthly Interest Income Chart -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up-arrow text-success"></i>
                            Monthly Interest Income (Last 12 Months)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyIncomeChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Loan Status Distribution -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-pie-chart text-info"></i>
                            Loan Status Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" width="300" height="200"></canvas>
                        
                        <!-- Legend -->
                        <div class="mt-3">
                            <div class="row text-center">
                                <div class="col-6 col-lg-12 mb-2">
                                    <span class="badge bg-success"><?php echo $statusDistribution['paid']; ?></span> Paid
                                </div>
                                <div class="col-6 col-lg-12 mb-2">
                                    <span class="badge bg-warning text-dark"><?php echo $statusDistribution['unpaid']; ?></span> Unpaid
                                </div>
                                <div class="col-6 col-lg-12 mb-2">
                                    <span class="badge bg-danger"><?php echo $statusDistribution['overdue']; ?></span> Overdue
                                </div>
                                <div class="col-6 col-lg-12 mb-2">
                                    <span class="badge bg-info"><?php echo $statusDistribution['partially_paid']; ?></span> Partial
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Borrowers by Interest Income -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill text-primary"></i>
                            Top Borrowers by Interest Generated
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="borrowersChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-speedometer2 text-warning"></i>
                            Performance Metrics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Capital Deployed</span>
                                    <strong><?php echo formatCurrency($overallStats['total_amount_lent']); ?></strong>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Capital Recovered</span>
                                    <strong><?php echo formatCurrency($overallStats['total_amount_repaid']); ?></strong>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Outstanding Balance</span>
                                    <strong class="text-warning">
                                        <?php echo formatCurrency($overallStats['total_amount_lent'] - $overallStats['total_amount_repaid']); ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Recovery Rate Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Recovery Rate</span>
                                <span class="small">
                                    <?php 
                                    $recovery = $overallStats['total_amount_lent'] > 0 
                                        ? round(($overallStats['total_amount_repaid'] / $overallStats['total_amount_lent']) * 100, 1)
                                        : 0;
                                    echo $recovery . '%';
                                    ?>
                                </span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?php echo $recovery; ?>%"></div>
                            </div>
                        </div>
                        
                        <!-- Collection Efficiency -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Collection Efficiency</span>
                                <span class="small"><?php echo $rate; ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: <?php echo $rate; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h6>Export Data</h6>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" onclick="downloadChart('monthlyIncomeChart', 'monthly-income')">
                                <i class="bi bi-download"></i> Income Chart
                            </button>
                            <button class="btn btn-outline-secondary" onclick="downloadChart('statusChart', 'status-distribution')">
                                <i class="bi bi-download"></i> Status Chart  
                            </button>
                            <button class="btn btn-outline-info" onclick="downloadChart('borrowersChart', 'top-borrowers')">
                                <i class="bi bi-download"></i> Borrowers Chart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script>
        // Data from PHP
        const monthlyData = <?php echo json_encode($monthlyIncome); ?>;
        const statusData = <?php echo json_encode($statusDistribution); ?>;
        const borrowersData = <?php echo json_encode($topBorrowers); ?>;
        
        // Monthly Income Chart
        const monthlyCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.label),
                datasets: [{
                    label: 'Interest Income',
                    data: monthlyData.map(d => d.value),
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'K' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });

        // Status Distribution Chart  
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Unpaid', 'Overdue', 'Partial'],
                datasets: [{
                    data: [
                        statusData.paid,
                        statusData.unpaid, 
                        statusData.overdue,
                        statusData.partially_paid
                    ],
                    backgroundColor: [
                        '#198754', // success
                        '#ffc107', // warning  
                        '#dc3545', // danger
                        '#0dcaf0'  // info
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Top Borrowers Chart
        const borrowersCtx = document.getElementById('borrowersChart').getContext('2d');
        const borrowersChart = new Chart(borrowersCtx, {
            type: 'bar',
            data: {
                labels: borrowersData.map(d => d.borrower_name),
                datasets: [{
                    label: 'Interest Generated',
                    data: borrowersData.map(d => d.interest),
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: 'rgb(13, 110, 253)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'K' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });

        // Download chart as image
        function downloadChart(chartId, filename) {
            const canvas = document.getElementById(chartId);
            const link = document.createElement('a');
            link.download = filename + '.png';
            link.href = canvas.toDataURL();
            link.click();
        }
    </script>
</body>
</html>
