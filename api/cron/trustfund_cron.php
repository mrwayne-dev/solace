<?php
/**
 * ============================================================
 * HealthRunCare — TrustFund Auto-Maturity Cron (Final Production)
 * ============================================================
 * ✅ Credits all matured TrustFund plans automatically
 * ✅ Updates wallet + inserts transaction
 * ✅ Sends user + admin emails
 * ✅ Logs all activity to /api/cron/trustfund_cron.log
 * ============================================================
 * SCHEDULE: Run daily (e.g. via Windows Task Scheduler or Linux CRON)
 * Command: php D:\mrwayne\web_dev\healthruncare\api\cron\trustfund_cron.php
 * ============================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/../backend/email.php';

date_default_timezone_set('UTC');

// =============================
// LOGGING SETUP
// =============================
$logFile = __DIR__ . '/trustfund_cron.log';
file_put_contents($logFile, "\n\n[" . date('Y-m-d H:i:s') . "] CRON started\n", FILE_APPEND);

// =============================
// SECURITY — CLI or Local Only
// =============================
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    exit("Access Denied\n");
}

// =============================
// DB CONNECTION
// =============================
try {
    $pdo = getPDO();
} catch (Exception $e) {
    file_put_contents($logFile, "❌ DB Connection Failed: {$e->getMessage()}\n", FILE_APPEND);
    exit("❌ Database connection failed.\n");
}

// =============================
// FETCH MATURED TRUSTFUNDS
// =============================
$stmt = $pdo->prepare("
    SELECT t.*, u.full_name, u.email
    FROM trustfund t
    JOIN users u ON t.user_id = u.id
    WHERE t.status = 'active' 
      AND DATE(t.maturity_date) <= CURDATE()
");
$stmt->execute();
$maturedPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($maturedPlans)) {
    file_put_contents($logFile, "✅ No matured TrustFunds found.\n", FILE_APPEND);
    exit("✅ No matured TrustFunds found.\n");
}

// =============================
// PROCESS EACH MATURED PLAN
// =============================
$totalProcessed = 0;
$totalPayoutSum = 0;
$totalROISum = 0;

foreach ($maturedPlans as $plan) {
    $user_id = (int)$plan['user_id'];
    $trust_id = (int)$plan['id'];
    $amount = (float)$plan['amount'];
    $roi_percent = (float)$plan['roi_percent'];
    $roi_earned = round(($amount * $roi_percent / 100), 2);
    $payout = round($amount + $roi_earned, 2);
    $ref = 'TRF-MAT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

    try {
        $pdo->beginTransaction();

        // 1️⃣ Update trustfund record
        $pdo->prepare("
            UPDATE trustfund 
            SET status = 'matured', roi_earned = ?, updated_at = NOW()
            WHERE id = ?
        ")->execute([$roi_earned, $trust_id]);

        // 2️⃣ Credit wallet
        $pdo->prepare("
            UPDATE wallets 
            SET balance = balance + ?, total_earnings = total_earnings + ?
            WHERE user_id = ?
        ")->execute([$payout, $roi_earned, $user_id]);

        // 3️⃣ Record transaction
        $details = json_encode([
            'type' => 'trustfund_maturity',
            'trust_id' => $trust_id,
            'roi_percent' => $roi_percent,
            'roi_earned' => $roi_earned,
            'total_payout' => $payout
        ]);

        $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, reference, status, method, details, created_at)
            VALUES (?, 'trustfund', ?, ?, 'completed', 'wallet_address', ?, NOW())
        ")->execute([$user_id, $payout, $ref, $details]);

        $pdo->commit();

        $totalProcessed++;
        $totalPayoutSum += $payout;
        $totalROISum += $roi_earned;

        // 4️⃣ Send User Email
        try {
            sendEmail([
                'to' => $plan['email'],
                'template' => 'trustfund_matured',
                'variables' => [
                    'user_name' => $plan['full_name'],
                    'plan_name' => $plan['plan_name'],
                    'amount' => number_format($amount, 2),
                    'roi_earned' => number_format($roi_earned, 2),
                    'payout' => number_format($payout, 2),
                    'maturity_date' => date('M d, Y', strtotime($plan['maturity_date'])),
                    'reference' => $ref
                ]
            ]);

            file_put_contents($logFile, "📧 Email sent to {$plan['email']} for TrustFund #{$trust_id}\n", FILE_APPEND);

        } catch (Exception $mailErr) {
            file_put_contents($logFile, "⚠️ Email failed for TrustFund #{$trust_id}: {$mailErr->getMessage()}\n", FILE_APPEND);
        }

        file_put_contents($logFile, "✅ Processed TrustFund #{$trust_id} | ROI={$roi_earned} | Payout={$payout}\n", FILE_APPEND);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        file_put_contents($logFile, "❌ Error on TrustFund #{$trust_id}: {$e->getMessage()}\n", FILE_APPEND);
    }
}

// =============================
// ADMIN SUMMARY EMAIL
// =============================
try {
    $adminSummary = sprintf(
        "TrustFund Cron Summary (%s)\n\nMatured Plans: %d\nTotal ROI: $%.2f\nTotal Payout: $%.2f",
        date('Y-m-d H:i:s'), $totalProcessed, $totalROISum, $totalPayoutSum
    );

    sendEmail([
        'to' => ADMIN_CONTACT_EMAIL,
        'template' => 'admin_notification',
        'variables' => [
            'subject' => 'TrustFund Auto-Maturity Cron Summary',
            'content' => nl2br($adminSummary)
        ]
    ]);

    file_put_contents($logFile, "📧 Admin summary email sent.\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, "⚠️ Failed to send admin summary email: {$e->getMessage()}\n", FILE_APPEND);
}

// =============================
// CRON COMPLETE
// =============================
$summaryLine = sprintf(
    "✅ Completed — %d TrustFunds processed | Total ROI: $%.2f | Total Payout: $%.2f\n",
    $totalProcessed, $totalROISum, $totalPayoutSum
);
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $summaryLine\n", FILE_APPEND);

echo $summaryLine;
?>
