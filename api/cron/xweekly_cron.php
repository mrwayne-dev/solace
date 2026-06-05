<?php
// Restrict execution to CLI or localhost — prevents unauthenticated web triggering
// of financial batch processing (payouts/debits). Mirrors xgrid_cron.php.
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit("Access Denied\n");
}

$LOG_FILE = __DIR__ . '/../../logs/xweekly_cron.log';
file_put_contents($LOG_FILE, "[" . date('Y-m-d H:i:s') . "] X-Weekly cron started\n", FILE_APPEND);
// ============================================================
// FILE: /api/cron/xweekly_cron.php
// PURPOSE: Daily debit + ROI accrual for active X-Weekly programs
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
    error_log("X-Weekly Cron — DB connection failed: " . $e->getMessage());
    exit("DB connection failed.\n");
}

$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

$log = [];
$processedCount = 0;
$skippedCount   = 0;

// ------------------------------------------------------------
// STEP 1: Fetch active programs due for debit today
// ------------------------------------------------------------
$stmt = $pdo->prepare("
    SELECT xweekly_programs.*, users.email, users.full_name AS user_name
    FROM xweekly_programs
    JOIN users ON xweekly_programs.user_id = users.id
    WHERE xweekly_programs.status = 'active'
      AND xweekly_programs.next_debit_date <= ?
");
$stmt->execute([$today]);
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$programs) {
    file_put_contents($LOG_FILE, "[$now] No active X-Weekly programs due today.\n\n", FILE_APPEND);
    exit("No active X-Weekly programs due today.\n");
}

// ------------------------------------------------------------
// STEP 2: Process each due program
// ------------------------------------------------------------
foreach ($programs as $p) {
    $program_id    = (int)   $p['id'];
    $user_id       = (int)   $p['user_id'];
    $weekly_amount = (float) $p['weekly_amount'];
    $roi_percent   = (float) $p['roi_percent'];
    $user_email    = $p['email'];
    $user_name     = $p['user_name'] ?? 'User';

    // Pre-check wallet balance
    $wstmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $wstmt->execute([$user_id]);
    $wrow = $wstmt->fetch(PDO::FETCH_ASSOC);
    $balance = (float)($wrow['balance'] ?? 0);

    if ($balance < $weekly_amount) {
        $log[] = "Insufficient balance for user {$user_id} (program #{$program_id}), skipping. balance=\$" . number_format($balance, 2) . " needed=\$" . number_format($weekly_amount, 2);
        $skippedCount++;
        continue;
    }

    // Weekly slice of annualised ROI
    $roi_credit = round(($weekly_amount * $roi_percent / 100) / 52, 2);

    try {
        $pdo->beginTransaction();

        // Lock wallet
        $wlock = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
        $wlock->execute([$user_id]);
        $wallet = $wlock->fetch(PDO::FETCH_ASSOC);

        if (!$wallet || (float)$wallet['balance'] < $weekly_amount) {
            $pdo->rollBack();
            $log[] = "Race-condition skip for program #{$program_id} (balance changed under lock).";
            $skippedCount++;
            continue;
        }

        // Debit weekly contribution, credit ROI, bump cumulative trackers
        $pdo->prepare("UPDATE wallets
            SET balance = balance - ? + ?,
                total_investments = total_investments + ?,
                xweekly_invested  = xweekly_invested  + ?,
                total_earnings    = total_earnings    + ?
            WHERE user_id = ?")
            ->execute([$weekly_amount, $roi_credit, $weekly_amount, $weekly_amount, $roi_credit, $user_id]);

        // Advance program: cumulative trackers + next debit date
        $pdo->prepare("UPDATE xweekly_programs
            SET total_invested = total_invested + ?,
                total_earned   = total_earned   + ?,
                next_debit_date = DATE_ADD(next_debit_date, INTERVAL 7 DAY)
            WHERE id = ?")
            ->execute([$weekly_amount, $roi_credit, $program_id]);

        // Two transaction rows: one debit, one ROI credit
        $debitRef = 'TXH-WKL-DEBIT-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
        $roiRef   = 'TXH-ROI-WKL-'   . strtoupper(uniqid()) . '-' . rand(1000, 9999);

        $debitDetails = json_encode([
            'program_id'    => $program_id,
            'weekly_amount' => $weekly_amount,
            'kind'          => 'xweekly_recurring_debit',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
            VALUES (?, 'investment', 'wallet', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $weekly_amount, $debitRef, $debitDetails, $now]);

        $roiDetails = json_encode([
            'program_id' => $program_id,
            'roi_credit' => $roi_credit,
            'kind'       => 'xweekly_roi',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
            VALUES (?, 'roi', 'system', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $roi_credit, $roiRef, $roiDetails, $now]);

        $pdo->commit();
        $processedCount++;

        // Email
        if (function_exists('sendEmail')) {
            // Re-read program for fresh cumulative total
            $cstmt = $pdo->prepare("SELECT total_invested FROM xweekly_programs WHERE id = ?");
            $cstmt->execute([$program_id]);
            $cumulative = (float) ($cstmt->fetchColumn() ?: 0);

            sendEmail([
                'to' => $user_email,
                'template' => 'xweekly_debit',
                'variables' => [
                    'user_name'      => $user_name,
                    'weekly_amount'  => number_format($weekly_amount, 2),
                    'roi_credit'     => number_format($roi_credit, 2),
                    'total_invested' => number_format($cumulative, 2),
                    'next_debit'     => date('M d, Y', strtotime("$today +7 days")),
                    'reference'      => $debitRef,
                ]
            ]);
        }

        $log[] = "Processed program #{$program_id} for {$user_email}: -\$" . number_format($weekly_amount, 2)
               . " +\$" . number_format($roi_credit, 2) . " ROI";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $log[] = "Error processing program #{$program_id}: " . $e->getMessage();
    }
}

// ------------------------------------------------------------
// STEP 3: Summary
// ------------------------------------------------------------
$summary = sprintf(
    "[%s] X-Weekly cron complete — Processed: %d | Skipped: %d\n",
    date('Y-m-d H:i:s'),
    $processedCount,
    $skippedCount
);
file_put_contents($LOG_FILE, $summary . implode("\n", $log) . "\n\n", FILE_APPEND);

echo $summary;
