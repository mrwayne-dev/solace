<?php
// Restrict execution to CLI or localhost — prevents unauthenticated web triggering
// of financial batch processing (payouts/debits). Mirrors xgrid_cron.php.
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit("Access Denied\n");
}

$LOG_FILE = __DIR__ . '/../../logs/xshares_cron.log';
file_put_contents($LOG_FILE, "[" . date('Y-m-d H:i:s') . "] X-Shares cron started\n", FILE_APPEND);
// ============================================================
// FILE: /api/cron/xshares_cron.php
// PURPOSE: Periodic ROI payouts + maturity handling for X-Shares holdings
// SCHEDULE: Run daily via CRON
// AUTHOR: TitanXHoldings Core
// ============================================================

// ------------------------------------------------------------
// Initialization
// ------------------------------------------------------------
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../backend/email.php';

date_default_timezone_set('UTC');

try {
    $pdo = getPDO();
} catch (Exception $e) {
    error_log("X-Shares Cron — DB connection failed: " . $e->getMessage());
    exit("DB connection failed.\n");
}

$today = date('Y-m-d');
$now   = date('Y-m-d H:i:s');

$log = [];
$periodicCount = 0;
$maturedCount  = 0;

// ============================================================
// PART A — Periodic ROI payouts
// ============================================================
$stmt = $pdo->prepare("
    SELECT xshares_holdings.*,
           xshares_assets.roi_percent       AS asset_roi_percent,
           xshares_assets.payout_schedule   AS payout_schedule,
           users.email,
           users.full_name                  AS user_name
    FROM xshares_holdings
    JOIN xshares_assets ON xshares_holdings.asset_id = xshares_assets.id
    JOIN users          ON xshares_holdings.user_id  = users.id
    WHERE xshares_holdings.status = 'active'
      AND xshares_holdings.payout_option = 'periodic'
");
$stmt->execute();
$periodics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// payout_schedule → (period_days, payouts_per_year)
$scheduleMap = [
    'weekly'    => [7,  52],
    'monthly'   => [30, 12],
    'quarterly' => [90, 4],
];

foreach ($periodics as $h) {
    $holding_id   = (int)   $h['id'];
    $user_id      = (int)   $h['user_id'];
    $asset_id     = (int)   $h['asset_id'];
    $amount       = (float) $h['amount'];
    $roi_earned   = (float) $h['roi_earned'];
    $roi_percent  = (float) $h['asset_roi_percent'];
    $schedule     = $h['payout_schedule'];
    $started_at   = $h['started_at'];
    $maturity     = $h['maturity_date']; // may be null
    $user_email   = $h['email'];
    $user_name    = $h['user_name'] ?? 'User';

    if (!isset($scheduleMap[$schedule])) {
        // 'maturity'-scheduled or unknown — Part B (or no-op) handles it
        continue;
    }
    [$period_days, $payouts_per_year] = $scheduleMap[$schedule];

    $per_payout = round(($amount * $roi_percent / 100) / $payouts_per_year, 2);
    if ($per_payout < 0.01) {
        continue;
    }

    // How many periods *should* have been paid by now?
    $started_ts = strtotime($started_at);
    if ($started_ts === false) continue;
    $days_elapsed   = (int) floor((strtotime($today) - $started_ts) / 86400);
    $expected_paid  = (int) floor($days_elapsed / $period_days);

    // How many were *actually* paid? (derived from roi_earned)
    $actual_paid = (int) round($roi_earned / $per_payout);

    $periods_due = $expected_paid - $actual_paid;
    if ($periods_due <= 0) continue;

    // Don't double-credit a holding whose maturity is also today/passed —
    // Part B will pay out principal + accumulated roi_earned in that case.
    if ($maturity !== null && strtotime($maturity) <= strtotime($today)) {
        continue;
    }

    $total_credit = round($per_payout * $periods_due, 2);

    try {
        $pdo->beginTransaction();

        $wstmt = $pdo->prepare("SELECT id FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        if (!$wstmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
        }

        // Credit wallet
        $pdo->prepare("UPDATE wallets
            SET balance = balance + ?,
                total_earnings = total_earnings + ?
            WHERE user_id = ?")
            ->execute([$total_credit, $total_credit, $user_id]);

        // Update holding's accumulated ROI
        $pdo->prepare("UPDATE xshares_holdings SET roi_earned = roi_earned + ? WHERE id = ?")
            ->execute([$total_credit, $holding_id]);

        // Transaction record
        $ref = 'TXH-ROI-SHR-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
        $details = json_encode([
            'holding_id'   => $holding_id,
            'asset_id'     => $asset_id,
            'per_payout'   => $per_payout,
            'periods_paid' => $periods_due,
            'schedule'     => $schedule,
            'kind'         => 'xshares_periodic_roi',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
            VALUES (?, 'roi', 'system', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $total_credit, $ref, $details, $now]);

        $pdo->commit();
        $periodicCount++;

        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'xshares_payout',
                'variables' => [
                    'user_name'   => $user_name,
                    'amount'      => number_format($total_credit, 2),
                    'schedule'    => $schedule,
                    'periods'     => $periods_due,
                    'roi_total'   => number_format($roi_earned + $total_credit, 2),
                    'reference'   => $ref,
                ]
            ]);
        }

        $log[] = "Periodic ROI paid for holding #{$holding_id} ({$user_email}): {$periods_due}× {$schedule} payout of \$" . number_format($per_payout, 2) . " = \$" . number_format($total_credit, 2);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $log[] = "Error paying periodic ROI on holding #{$holding_id}: " . $e->getMessage();
    }
}

// ============================================================
// PART B — Maturity handling
// ============================================================
$mstmt = $pdo->prepare("
    SELECT xshares_holdings.*,
           xshares_assets.roi_percent     AS asset_roi_percent,
           xshares_assets.payout_schedule AS payout_schedule,
           users.email,
           users.full_name                AS user_name
    FROM xshares_holdings
    JOIN xshares_assets ON xshares_holdings.asset_id = xshares_assets.id
    JOIN users          ON xshares_holdings.user_id  = users.id
    WHERE xshares_holdings.status = 'active'
      AND xshares_holdings.maturity_date IS NOT NULL
      AND xshares_holdings.maturity_date <= ?
");
$mstmt->execute([$today]);
$matured = $mstmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($matured as $h) {
    $holding_id    = (int)   $h['id'];
    $user_id       = (int)   $h['user_id'];
    $asset_id      = (int)   $h['asset_id'];
    $amount        = (float) $h['amount'];
    $roi_earned    = (float) $h['roi_earned'];
    $roi_percent   = (float) $h['asset_roi_percent'];
    $payout_option = $h['payout_option'];
    $user_email    = $h['email'];
    $user_name     = $h['user_name'] ?? 'User';

    try {
        $pdo->beginTransaction();

        // Lock the holding so concurrent runs don't double-mature it
        $hstmt = $pdo->prepare("SELECT status FROM xshares_holdings WHERE id = ? FOR UPDATE");
        $hstmt->execute([$holding_id]);
        $row = $hstmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['status'] !== 'active') {
            $pdo->rollBack();
            continue;
        }

        // For 'maturity' payout option, the full term ROI is realised now —
        // top up roi_earned to the full asset roi_percent of principal.
        if ($payout_option === 'maturity') {
            $final_roi  = round(($amount * $roi_percent / 100), 2);
            $delta_roi  = max(0, round($final_roi - $roi_earned, 2));
            if ($delta_roi > 0) {
                $pdo->prepare("UPDATE xshares_holdings SET roi_earned = roi_earned + ? WHERE id = ?")
                    ->execute([$delta_roi, $holding_id]);
                $roi_earned += $delta_roi;
            }
        }

        $payout = round($amount + $roi_earned, 2);

        // Lock wallet
        $wstmt = $pdo->prepare("SELECT id FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        if (!$wstmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
        }

        // Credit principal + accumulated ROI; track earnings delta
        $pdo->prepare("UPDATE wallets
            SET balance = balance + ?,
                total_earnings = total_earnings + ?
            WHERE user_id = ?")
            ->execute([$payout, $roi_earned, $user_id]);

        // Mark matured
        $pdo->prepare("UPDATE xshares_holdings SET status = 'matured' WHERE id = ?")
            ->execute([$holding_id]);

        $ref = 'TXH-MATURE-SHR-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
        $details = json_encode([
            'holding_id'    => $holding_id,
            'asset_id'      => $asset_id,
            'principal'     => $amount,
            'roi_earned'    => $roi_earned,
            'payout_option' => $payout_option,
            'kind'          => 'xshares_maturity',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
            VALUES (?, 'maturity', 'system', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $payout, $ref, $details, $now]);

        $pdo->commit();
        $maturedCount++;

        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'xshares_matured',
                'variables' => [
                    'user_name'  => $user_name,
                    'holding_id' => $holding_id,
                    'principal'  => number_format($amount, 2),
                    'roi_earned' => number_format($roi_earned, 2),
                    'payout'     => number_format($payout, 2),
                    'reference'  => $ref,
                ]
            ]);
        }

        $log[] = "Matured holding #{$holding_id} ({$user_email}): principal \$" . number_format($amount, 2)
               . " + roi \$" . number_format($roi_earned, 2) . " = \$" . number_format($payout, 2);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $log[] = "Error maturing holding #{$holding_id}: " . $e->getMessage();
    }
}

// ============================================================
// Summary
// ============================================================
$summary = sprintf(
    "[%s] X-Shares cron complete — Periodic payouts: %d | Matured: %d\n",
    date('Y-m-d H:i:s'),
    $periodicCount,
    $maturedCount
);
file_put_contents($LOG_FILE, $summary . implode("\n", $log) . "\n\n", FILE_APPEND);

echo $summary;
