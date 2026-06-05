<?php
// Restrict execution to CLI or localhost — prevents unauthenticated web triggering
// of financial batch processing (payouts/debits). Mirrors xgrid_cron.php.
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit("Access Denied\n");
}

/**
 * ======================================================
 * TitanXHoldings — HoldLock CRON Handler (Final)
 * Purpose: Auto-matures locked funds, applies ROI, handles early unlock penalties,
 *          credits user wallets, and logs transactions.
 * ======================================================
 * Recommended: run every 12–24 hours via CRON
 * Example: 0 2 * * * php /path/to/api/backend/holdlock_cron.php
 * ======================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/../backend/email.php';

date_default_timezone_set('UTC');
$logFile = __DIR__ . '/holdlock_cron.log';
file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] CRON started\n", FILE_APPEND);

try {
    $pdo = getPDO();
} catch (Exception $e) {
    exit("❌ DB Connection failed: " . $e->getMessage());
}

// === STEP 1: Fetch Active HoldLocks ===
$stmt = $pdo->prepare("
    SELECT h.*, u.email, u.full_name, w.id AS wallet_id
    FROM holdlock h
    JOIN users u ON h.user_id = u.id
    JOIN wallets w ON w.user_id = u.id
    WHERE h.status IN ('locked', 'unlock_pending')
");
$stmt->execute();
$holdlocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$holdlocks) {
    echo "✅ No active or pending holdlocks.\n";
    exit;
}

// === STEP 2: Plan Reference Data (fallback ROI/period/penalty) ===
$plans = [
    'Flexi Health Lock Plan' => ['roi_percent' => 3.5, 'duration_days' => 180, 'penalty_percent' => 1],
    'Standard Lock & Grow Plan' => ['roi_percent' => 8.0, 'duration_days' => 365, 'penalty_percent' => 1.5],
    'Executive LockPlus Plan' => ['roi_percent' => 16.0, 'duration_days' => 730, 'penalty_percent' => 2],
    'Prestige Capital Hold Plan' => ['roi_percent' => 28.0, 'duration_days' => 1095, 'penalty_percent' => 2.5],
    'Lifetime Reserve Lock Plan' => ['roi_percent' => 7.0, 'duration_days' => 3650, 'penalty_percent' => 1]
];

$today = new DateTime();
$processed = 0;

foreach ($holdlocks as $lock) {
    $userId = intval($lock['user_id']);
    $planName = $lock['plan_name'];
    $amount = floatval($lock['amount']);
    $status = $lock['status'];
    $lockId = intval($lock['id']);
    $maturityDate = $lock['maturity_date'] ? new DateTime($lock['maturity_date']) : null;
    $createdDate = new DateTime($lock['created_at']);
    $roiEarned = 0;
    $penalty = 0;
    $finalPayout = 0;

    // === Retrieve plan data fallback ===
    $roiPercent = floatval($lock['roi_percent']);
    $durationDays = intval($lock['duration_days']);
    $penaltyPercent = isset($plans[$planName]) ? $plans[$planName]['penalty_percent'] : 1.5;

    if ($roiPercent <= 0 && isset($plans[$planName])) $roiPercent = $plans[$planName]['roi_percent'];
    if ($durationDays <= 0 && isset($plans[$planName])) $durationDays = $plans[$planName]['duration_days'];

    // === Calculate ROI based on time elapsed ===
    $daysElapsed = $createdDate->diff($today)->days;
    $roiEarned = ($amount * ($roiPercent / 100)) * min($daysElapsed / $durationDays, 1);

    // === Maturity or early unlock ===
    if ($status === 'unlock_pending' && $maturityDate && $today < $maturityDate) {
        // Early unlock → apply penalty
        $penalty = $amount * ($penaltyPercent / 100);
        $roiEarned = $roiEarned * 0.5; // half ROI if unlocked early
        $finalPayout = ($amount - $penalty) + $roiEarned;
        $status = 'unlocked_early';
    } elseif ($maturityDate && $today >= $maturityDate) {
        // Fully matured
        $finalPayout = $amount + $roiEarned;
        $status = 'matured';
    } else {
        // Still ongoing
        $finalPayout = 0;
        $status = 'locked';
    }

    // === Update holdlock record ===
    $update = $pdo->prepare("
        UPDATE holdlock
        SET roi_earned = ?, penalty_applied = ?, status = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $update->execute([$roiEarned, $penalty, $status, $lockId]);

    // === Credit wallet if matured or unlocked early ===
    if (in_array($status, ['matured', 'unlocked_early'])) {
        $walletUpdate = $pdo->prepare("
            UPDATE wallets
            SET balance = balance + ?, total_earnings = total_earnings + ?
            WHERE user_id = ?
        ");
        $walletUpdate->execute([$finalPayout, $roiEarned, $userId]);

        // Record transaction
        $ref = 'HLD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        $txn = $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, reference, status, method, details)
            VALUES (?, 'holdlock', ?, ?, 'completed', 'system', JSON_OBJECT('plan', ?, 'roi', ?, 'penalty', ?, 'payout', ?))
        ");
        $txn->execute([$userId, $finalPayout, $ref, $planName, $roiEarned, $penalty, $finalPayout]);

        // Mark plan as completed
        $pdo->prepare("UPDATE holdlock SET status = 'completed' WHERE id = ?")->execute([$lockId]);

        // === Send Email Notification ===
        try {
            $templates = getEmailTemplates();
            $templateKey = ($status === 'unlocked_early') ? 'holdlock_unlocked_early' : 'holdlock_matured';
            $emailTemp = $templates[$templateKey] ?? null;

            if ($emailTemp) {
                $subject = $emailTemp['subject'];
                $body = str_replace(
                    ['{{name}}', '{{plan}}', '{{roi}}', '{{payout}}', '{{penalty}}'],
                    [
                        $lock['full_name'],
                        $planName,
                        number_format($roiEarned, 2),
                        number_format($finalPayout, 2),
                        number_format($penalty, 2)
                    ],
                    $emailTemp['html']
                );

                sendEmail([
  'to' => $lock['email'],
  'template' => $templateKey,
  'variables' => [
      'user_name' => $lock['full_name'],
      'plan_name' => $planName,
      'roi_earned' => number_format($roiEarned, 2),
      'penalty' => number_format($penalty, 2),
      'payout' => number_format($finalPayout, 2),
      'maturity_date' => $lock['maturity_date'] ?? '',
      'reference' => $ref
  ]
]);

            }
        } catch (Exception $mailErr) {
            error_log("📧 Mail send error for HoldLock ID {$lockId}: " . $mailErr->getMessage());
        }
    }

    $processed++;
}

echo "✅ HoldLock CRON completed successfully. Processed {$processed} entries.\n";
file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] ✅ Completed — {$processed} processed\n\n", FILE_APPEND);
