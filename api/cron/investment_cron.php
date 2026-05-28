<?php
file_put_contents(__DIR__ . '/investment_cron.log', "[" . date('Y-m-d H:i:s') . "] Cron started\n", FILE_APPEND);
// ============================================================
// FILE: /api/cron/investment_cron.php
// PURPOSE: Weekly automated investment ROI updater & maturities handler
// SCHEDULE: Run weekly (or daily) via CRON
// AUTHOR: HealthRunCare Core
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
    error_log("Investment Cron — DB connection failed: " . $e->getMessage());
    exit("DB connection failed.\n");
}

$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

$log = [];
$processedCount = 0;
$maturedCount = 0;

// ------------------------------------------------------------
// STEP 1: Fetch all active investments
// ------------------------------------------------------------
$stmt = $pdo->query("
    SELECT inv.id, inv.user_id, inv.amount, inv.roi_percent, inv.duration_days, inv.status, 
           inv.roi_earned, inv.maturity_date, inv.plan_name, inv.created_at,
           u.email, u.full_name AS user_name
    FROM investments inv
    JOIN users u ON u.id = inv.user_id
    WHERE inv.status = 'active'
");
$investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$investments) {
    exit("No active investments found.\n");
}

// ------------------------------------------------------------
// STEP 2: Loop through investments and process weekly ROI
// ------------------------------------------------------------
foreach ($investments as $inv) {
    $user_id = (int)$inv['user_id'];
    $investment_id = (int)$inv['id'];
    $amount = (float)$inv['amount'];
    $roi_percent = (float)$inv['roi_percent'];
    $duration_days = (int)$inv['duration_days'];
    $roi_earned = (float)$inv['roi_earned'];
    $plan_name = $inv['plan_name'];
    $user_email = $inv['email'];
    $user_name = $inv['user_name'] ?? 'User';
    $maturity_date = $inv['maturity_date'];

    // Skip if already matured
    if (strtotime($maturity_date) <= strtotime($today)) {
        // Handle maturity completion
        $maturedCount++;
        $pdo->beginTransaction();
        try {
            // Calculate final ROI
            $final_roi = round(($amount * $roi_percent / 100), 2);
            $total_payout = $amount + $final_roi;

            // Update investment status
            $pdo->prepare("UPDATE investments SET status='completed', roi_earned=? WHERE id=?")
                ->execute([$final_roi, $investment_id]);

            // Credit wallet
            $pdo->prepare("UPDATE wallets SET balance = balance + ?, total_earnings = total_earnings + ? WHERE user_id = ?")
                ->execute([$total_payout, $final_roi, $user_id]);

            // Insert payout transaction
            $ref = 'HRC-MATURE-' . strtoupper(uniqid());
            $details = json_encode(['investment_id' => $investment_id, 'payout' => $total_payout]);
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference, status, details, created_at) 
                VALUES (?, 'investment', ?, ?, 'completed', ?, ?)")
                ->execute([$user_id, $total_payout, $ref, $details, $now]);

            $pdo->commit();

            // Email notification
            if (function_exists('sendEmail')) {
                sendEmail([
                    'to' => $user_email,
                    'template' => 'investment_matured',
                    'variables' => [
                        'user_name' => $user_name,
                        'plan_name' => $plan_name,
                        'amount' => number_format($amount, 2),
                        'roi_earned' => number_format($final_roi, 2),
                        'total_payout' => number_format($total_payout, 2),
                        'maturity_date' => date('M d, Y', strtotime($maturity_date))
                    ]
                ]);
            }

            $log[] = "Matured: Investment #$investment_id ($plan_name) for $user_email";
        } catch (Exception $e) {
            $pdo->rollBack();
            $log[] = "Error finalizing investment #$investment_id: " . $e->getMessage();
        }
        continue;
    }

    // ------------------------------------------------------------
    // STEP 3: Calculate this week’s ROI increment
    // ------------------------------------------------------------
    $weekly_roi = round(($amount * $roi_percent / 100) / ($duration_days / 7), 2);

    // Skip trivial earnings (less than 0.01)
    if ($weekly_roi < 0.01) continue;

    $pdo->beginTransaction();
    try {
        // Update investment ROI
        $pdo->prepare("UPDATE investments SET roi_earned = roi_earned + ? WHERE id = ?")
            ->execute([$weekly_roi, $investment_id]);

        // Update wallet
        $pdo->prepare("UPDATE wallets SET balance = balance + ?, total_earnings = total_earnings + ? WHERE user_id = ?")
            ->execute([$weekly_roi, $weekly_roi, $user_id]);

        // Insert transaction record
        $ref = 'HRC-ROI-' . strtoupper(uniqid());
        $details = json_encode(['investment_id' => $investment_id, 'weekly_roi' => $weekly_roi]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference, status, details, created_at)
            VALUES (?, 'investment', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $weekly_roi, $ref, $details, $now]);

        $pdo->commit();
        $processedCount++;

        // Send weekly email (optional but nice UX)
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'weekly_investment_update',
                'variables' => [
                    'user_name' => $user_name,
                    'plan_name' => $plan_name,
                    'weekly_roi' => number_format($weekly_roi, 2),
                    'total_roi' => number_format($roi_earned + $weekly_roi, 2),
                    'next_maturity' => date('M d, Y', strtotime($maturity_date))
                ]
            ]);
        }

        $log[] = "Updated weekly ROI for investment #$investment_id ($plan_name) — +$" . number_format($weekly_roi, 2);

    } catch (Exception $e) {
        $pdo->rollBack();
        $log[] = "Error processing investment #$investment_id: " . $e->getMessage();
    }
}

// ------------------------------------------------------------
// STEP 4: Log summary
// ------------------------------------------------------------
$summary = sprintf(
    "[%s] Weekly ROI Update Complete — Processed: %d | Matured: %d\n",
    date('Y-m-d H:i:s'),
    $processedCount,
    $maturedCount
);
file_put_contents(__DIR__ . '/investment_cron.log', $summary . implode("\n", $log) . "\n\n", FILE_APPEND);

echo $summary;
