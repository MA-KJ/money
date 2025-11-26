<?php
/**
 * Statistics helper functions
 */
require_once __DIR__ . '/database.php';

/**
 * Get monthly interest income for the last N months (defaults to 12)
 * Returns array: [ ['label' => 'Jan 2025', 'value' => 123.45], ... ]
 */
function getMonthlyInterestIncome($months = 12) {
    global $db;
    $months = max(1, min(36, (int)$months));

    // Build a list of months from current going back N-1 months
    $labels = [];
    $start = new DateTime('first day of this month');
    for ($i = $months - 1; $i >= 0; $i--) {
        $d = (clone $start)->modify("-{$i} months");
        $labels[] = [
            'year' => (int)$d->format('Y'),
            'month' => (int)$d->format('m'),
            'label' => $d->format('M Y')
        ];
    }

    // Query interest (only realized when loan is paid) grouped by date_paid
    $rows = $db->fetchAll(
        "SELECT YEAR(date_paid) y, MONTH(date_paid) m,
                SUM(total_payable - loan_amount) AS interest
         FROM loans
         WHERE status = 'paid' AND date_paid IS NOT NULL
               AND date_paid >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL ? MONTH)
         GROUP BY YEAR(date_paid), MONTH(date_paid)
         ORDER BY y, m",
        [$months - 1]
    );

    $map = [];
    foreach ($rows as $r) {
        $key = sprintf('%04d-%02d', $r['y'], $r['m']);
        $map[$key] = (float)$r['interest'];
    }

    $data = [];
    foreach ($labels as $l) {
        $key = sprintf('%04d-%02d', $l['year'], $l['month']);
        $data[] = [ 'label' => $l['label'], 'value' => $map[$key] ?? 0.0 ];
    }
    return $data;
}

/**
 * Get distribution of loan statuses
 * Returns ['paid' => X, 'unpaid' => Y, 'overdue' => Z, 'partially_paid' => W]
 */
function getLoanStatusDistribution() {
    global $db;
    $rows = $db->fetchAll(
        "SELECT status, COUNT(*) cnt FROM loans GROUP BY status"
    );
    $dist = [
        'paid' => 0,
        'unpaid' => 0,
        'overdue' => 0,
        'partially_paid' => 0,
    ];
    foreach ($rows as $r) {
        if (isset($dist[$r['status']])) {
            $dist[$r['status']] = (int)$r['cnt'];
        }
    }
    return $dist;
}

/**
 * Get interest income distribution by borrower for the last N months
 * Returns array of ['borrower_name' => 'John', 'interest' => 123.45]
 */
function getInterestByBorrower($months = 12, $limit = 12) {
    global $db;
    $months = max(1, min(36, (int)$months));
    $limit = max(5, min(100, (int)$limit));

    $rows = $db->fetchAll(
        "SELECT borrower_name, SUM(total_payable - loan_amount) AS interest
         FROM loans
         WHERE status = 'paid' AND date_paid IS NOT NULL
           AND date_paid >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL ? MONTH)
         GROUP BY borrower_name
         ORDER BY interest DESC
         LIMIT {$limit}",
        [$months - 1]
    );
    return array_map(function($r){
        return [ 'borrower_name' => $r['borrower_name'], 'interest' => (float)$r['interest'] ];
    }, $rows);
}

?>
