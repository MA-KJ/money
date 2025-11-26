<?php

/**
 * Navigation Component
 * Main navigation bar for authenticated users
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$user = getCurrentUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-bank"></i>
            <?php echo getSetting('site_name', 'Loan Tracker'); ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>"
                        href="dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'add-loan' ? 'active' : ''; ?>"
                        href="add-loan.php">
                        <i class="bi bi-plus-circle"></i>
                        Add Loan
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'loans' ? 'active' : ''; ?>"
                        href="loans.php">
                        <i class="bi bi-list-check"></i>
                        All Loans
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'statistics' ? 'active' : ''; ?>"
                        href="statistics.php">
                        <i class="bi bi-bar-chart"></i>
                        Statistics
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'reports' ? 'active' : ''; ?>"
                        href="reports.php">
                        <i class="bi bi-file-earmark-text"></i>
                        Reports
                    </a>
                </li>

                <?php if (isSuperAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['users', 'settings']) ? 'active' : ''; ?>"
                            href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i>
                            Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="users.php">
                                    <i class="bi bi-people"></i>
                                    Manage Users
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="bi bi-sliders"></i>
                                    Settings
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="logs.php">
                                    <i class="bi bi-file-text"></i>
                                    System Logs
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- User menu -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>
                        <?php if (isSuperAdmin()): ?>
                            <span class="badge bg-warning text-dark ms-1">Super Admin</span>
                        <?php elseif ($user['role'] === 'admin'): ?>
                            <span class="badge bg-info ms-1">Admin</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">
                                <?php echo htmlspecialchars($user['username']); ?>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                            </h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="bi bi-person"></i>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="change-password.php">
                                <i class="bi bi-lock"></i>
                                Change Password
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                Sign Out
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Quick stats bar for smaller screens -->
<div class="d-block d-lg-none mb-3">
    <div class="container-fluid">
        <div class="row g-2">
            <?php
            // Only show quick stats if loans.php is included
            if (function_exists('getLoanStatistics')) {
                $quickStats = getLoanStatistics('12_months');
            } else {
                $quickStats = ['total_loans' => 0, 'paid_loans' => 0, 'unpaid_loans' => 0, 'overdue_loans' => 0];
            }
            ?>
            <div class="col-6 col-sm-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center p-2">
                        <div class="fs-6 fw-bold"><?php echo $quickStats['total_loans']; ?></div>
                        <div class="small">Total Loans</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center p-2">
                        <div class="fs-6 fw-bold"><?php echo $quickStats['paid_loans']; ?></div>
                        <div class="small">Paid</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center p-2">
                        <div class="fs-6 fw-bold"><?php echo $quickStats['unpaid_loans']; ?></div>
                        <div class="small">Unpaid</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center p-2">
                        <div class="fs-6 fw-bold"><?php echo $quickStats['overdue_loans']; ?></div>
                        <div class="small">Overdue</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-3 mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <p class="mb-0">
                    Created by <a href="https://activevision.42web.io/?i=1" target="_blank" class="text-white fw-bold text-decoration-none"><strong>ACTiveVision</strong></a>
                </p>
            </div>
        </div>
    </div>
</footer>
