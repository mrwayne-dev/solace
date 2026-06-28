<?php
// Restrict execution to CLI or localhost — prevents unauthenticated web triggering
// of financial batch processing (payouts).
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit("Access Denied\n");
}

file_put_contents(__DIR__ . '/../../logs/investment_cron.log', "[" . date('Y-m-d H:i:s') . "] Cron started\n", FILE_APPEND);
// ============================================================
// FILE: /api/cron/investment_cron.php
// PURPOSE: Daily mining-contract profit accrual + maturity handler.
//          Each active contract accrues `daily_profit_percent` of its
//          principal per day for `duration_days`. Profit is LOCKED
//          (tracked on the contract + the lifetime earnings counter)
//          and is NOT spendable during the term. On the final day the
//          principal AND all accrued profit are released to the wallet
//          balance together, and the contract is marked completed.
// SCHEDULE: Run once daily via CRON.
// AUTHOR: Solace Mining Core
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../backend/email.php';

date_default_timezone_set('UTC');

try {
    $pdo = getPDO();
} catch (Exception $e) {
    error_log("Investment Cron — DB connection failed: " . $e->getMessage());
    exit("DB connection failed.\n");
}

$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

$log = [];
$processedCount = 0;
$maturedCount = 0;

// ------------------------------------------------------------
// Fetch all active contracts that are due for accrual today
// ------------------------------------------------------------
$stmt = $pdo->query("
    SELECT inv.id, inv.user_id, inv.amount, inv.daily_profit_percent, inv.duration_days,
           inv.days_paid, inv.roi_earned, inv.last_payout_date, inv.plan_name,
           u.email, u.full_name AS user_name
    FROM investments inv
    JOIN users u ON u.id = inv.user_id
    WHERE inv.status = 'active'
");
$investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$investments) {
    exit("No active investments found.\n");
}

foreach ($investments as $inv) {
    $user_id       = (int)$inv['user_id'];
    $investment_id = (int)$inv['id'];
    $amount        = (float)$inv['amount'];
    $daily_pct     = (float)$inv['daily_profit_percent'];
    $duration_days = (int)$inv['duration_days'];
    $days_paid     = (int)$inv['days_paid'];
    $plan_name     = $inv['plan_name'];
    $user_email    = $inv['email'];
    $user_name     = $inv['user_name'] ?? 'User';
    $prior_roi     = (float)$inv['roi_earned'];
    $last_payout   = $inv['last_payout_date'] ?: $today;

    // How many days have elapsed since the last accrual?
    $elapsed = (int) floor((strtotime($today) - strtotime($last_payout)) / 86400);
    $remaining = $duration_days - $days_paid;
    $days_to_accrue = min($elapsed, $remaining);

    if ($days_to_accrue <= 0) {
        continue; // already accrued today, or nothing left to pay
    }

    $daily_amount = round($amount * $daily_pct / 100, 2);
    $accrual = round($daily_amount * $days_to_accrue, 2);
    $new_days_paid = $days_paid + $days_to_accrue;
    $total_roi = round($prior_roi + $accrual, 2);
    $is_complete = ($new_days_paid >= $duration_days);

    $pdo->beginTransaction();
    try {
        // Accrue profit on the CONTRACT (locked) and grow the lifetime earnings
        // counter so "Total Earnings" climbs daily. The spendable wallet balance
        // is deliberately NOT touched until the contract matures.
        $pdo->prepare("UPDATE investments SET roi_earned = ?, days_paid = ?, last_payout_date = ? WHERE id = ?")
            ->execute([$total_roi, $new_days_paid, $today, $investment_id]);

        $pdo->prepare("UPDATE wallets SET total_earnings = total_earnings + ? WHERE user_id = ?")
            ->execute([$accrual, $user_id]);

        // On completion, release principal + ALL accrued profit to the wallet at once.
        if ($is_complete) {
            $payout = round($amount + $total_roi, 2);
            $pdo->prepare("UPDATE investments SET status = 'completed' WHERE id = ?")->execute([$investment_id]);
            $pdo->prepare("UPDATE wallets SET balance = balance + ?, total_investments = GREATEST(total_investments - ?, 0) WHERE user_id = ?")
                ->execute([$payout, $amount, $user_id]);

            $mref = 'SLM-MATURE-' . strtoupper(uniqid());
            $mdetails = json_encode(['investment_id' => $investment_id, 'principal_returned' => $amount, 'profit_released' => $total_roi]);
            $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                VALUES (?, 'investment', 'system', ?, ?, 'completed', ?, ?)")
                ->execute([$user_id, $payout, $mref, $mdetails, $now]);

            $maturedCount++;
        }

        $pdo->commit();
        $processedCount++;

        if ($is_complete && function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'investment_matured',
                'variables' => [
                    'user_name' => $user_name,
                    'plan_name' => $plan_name,
                    'amount' => number_format($amount, 2),
                    'roi_earned' => number_format($total_roi, 2),
                    'total_payout' => number_format($amount + $total_roi, 2),
                    'maturity_date' => date('M d, Y'),
                ]
            ]);
        }

        $log[] = ($is_complete ? "Matured (released)" : "Accrued (locked)") . ": contract #$investment_id ($plan_name) +$" . number_format($accrual, 2) . " [roi=$" . number_format($total_roi, 2) . "]";
    } catch (Exception $e) {
        $pdo->rollBack();
        $log[] = "Error processing contract #$investment_id: " . $e->getMessage();
    }
}

$summary = sprintf(
    "[%s] Daily Profit Accrual Complete — Processed: %d | Completed: %d\n",
    date('Y-m-d H:i:s'),
    $processedCount,
    $maturedCount
);
file_put_contents(__DIR__ . '/../../logs/investment_cron.log', $summary . implode("\n", $log) . "\n\n", FILE_APPEND);

echo $summary;
