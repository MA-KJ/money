<?php
/**
 * Landing Page - Public Overview
 * Modern, responsive page describing the system with CTA to Login
 */
require_once 'includes/app.php';

// If logged in, send to dashboard directly
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$siteName = getSetting('site_name', SITE_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($siteName); ?> - Welcome</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    .hero {
      background: linear-gradient(135deg, #0d6efd, #6610f2);
      color: #fff;
      padding: 4rem 0;
    }
    .feature-icon {
      width: 48px; height: 48px;
      background: rgba(13, 110, 253, 0.1);
      color: #0d6efd;
      display: inline-flex; align-items: center; justify-content: center;
      border-radius: 12px;
      font-size: 1.5rem;
    }
    .card-shadow { box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 0; }
    .gradient-card { background: linear-gradient(180deg, #ffffff, #f8f9ff); }
  </style>
</head>
<body>
  <!-- Simple top bar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#"><i class="bi bi-bank2 text-primary"></i> <?php echo htmlspecialchars($siteName); ?></a>
      <div class="ms-auto">
        <a href="login.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Login</a>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-7">
          <h1 class="display-5 fw-bold">Track Loans, Payments, and Profit — Effortlessly</h1>
          <p class="lead mt-3">A secure, modern loan tracking system with real-time statistics, smart status updates, and professional reporting — built for speed, accuracy and simplicity.</p>
          <div class="mt-4 d-flex gap-2 flex-wrap">
            <a href="login.php" class="btn btn-light btn-lg text-primary fw-semibold"><i class="bi bi-box-arrow-in-right"></i> Proceed to Login</a>
            <a href="#features" class="btn btn-outline-light btn-lg"><i class="bi bi-info-circle"></i> Learn More</a>
          </div>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0">
          <div class="card card-shadow gradient-card">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3"><i class="bi bi-graph-up text-primary"></i> Quick Highlights</h5>
              <ul class="list-unstyled mb-0">
                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Dashboard with real-time KPIs</li>
                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Add loans with automatic calculations</li>
                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Mark full or partial payments</li>
                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Visual charts & exportable reports</li>
                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> User roles: Admin and Super Admin</li>
                <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Secure login, CSRF & audit logging</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="py-5 bg-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">Everything You Need to Manage Loans</h2>
        <p class="text-muted">Powerful features with a clean and modern interface.</p>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-4">
          <div class="card card-shadow h-100">
            <div class="card-body p-4">
              <div class="feature-icon mb-3"><i class="bi bi-speedometer2"></i></div>
              <h5 class="fw-bold">Smart Dashboard</h5>
              <p class="text-muted">Get instant insights into totals, paid vs unpaid, overdue items, and financial summaries.</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card card-shadow h-100">
            <div class="card-body p-4">
              <div class="feature-icon mb-3"><i class="bi bi-cash-coin"></i></div>
              <h5 class="fw-bold">Loan Automation</h5>
              <p class="text-muted">Automatic calculation of interest, due dates, totals and status updates.</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card card-shadow h-100">
            <div class="card-body p-4">
              <div class="feature-icon mb-3"><i class="bi bi-clipboard2-check"></i></div>
              <h5 class="fw-bold">Payment Tracking</h5>
              <p class="text-muted">Record full or partial payments, auto-update balances and statuses in real-time.</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card card-shadow h-100">
            <div class="card-body p-4">
              <div class="feature-icon mb-3"><i class="bi bi-bar-chart"></i></div>
              <h5 class="fw-bold">Charts & Analytics</h5>
              <p class="text-muted">Visualize performance with monthly income charts, status distribution and ROI metrics.</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card card-shadow h-100">
            <div class="card-body p-4">
              <div class="feature-icon mb-3"><i class="bi bi-file-earmark-text"></i></div>
              <h5 class="fw-bold">Professional Reports</h5>
              <p class="text-muted">Generate reports for 3, 6, 9 or 12 months. Export to PDF/Excel or print-ready.</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card card-shadow h-100">
            <div class="card-body p-4">
              <div class="feature-icon mb-3"><i class="bi bi-shield-lock"></i></div>
              <h5 class="fw-bold">Secure by Design</h5>
              <p class="text-muted">CSRF protection, password hashing, secure sessions and event logging baked-in.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center mt-5">
        <a href="login.php" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-in-right"></i> Get Started - Login</a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white py-4">
    <div class="container text-center">
      <div class="small">Created by <a href="https://activevision.42web.io/?i=1" target="_blank" class="text-white fw-bold text-decoration-none"><strong>ACTiveVision</strong></a></div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
