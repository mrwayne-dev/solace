<?php
file_put_contents(__DIR__ . '/maintenance_log.txt', "[" . date('Y-m-d H:i:s') . "] Cron executed\n", FILE_APPEND);

/**
 * ============================================================
 * HealthRunCare — Maintenance Auto-Maturity Cron (Final)
 * ============================================================
 * Location: /api/cron/maintenance_cron.php
 *
 * ✅ Runs daily (via cron)
 * ✅ Finds all matured Maintenance Development plans
 * ✅ Credits user wallet
 * ✅ Logs transaction
 * ✅ Sends "maintenance_matured" and admin email
 * ✅ Marks as 'matured'
 * ============================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/../backend/email.php';

// Secure execution — only CLI or localhost
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    exit("Access Denied\n");
}

try {
    $pdo = getPDO();
} catch (Exception $e) {
    exit("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// ============================================================
// PLAN REFERENCES — match those used in backend/maintenance.php
// ============================================================
$plansRef = [
    1 => ['name' => 'Maintenance Support Starter Plan', 'roi_percent' => 5.5,  'duration_days' => 9 * 30],
    2 => ['name' => 'Standard Equipment Care Plan', 'roi_percent' => 9.0,  'duration_days' => 12 * 30],
    3 => ['name' => 'Infrastructure Development Plan', 'roi_percent' => 16.5, 'duration_days' => 24 * 30],
    4 => ['name' => 'Premium Equipment Sustainability Plan', 'roi_percent' => 25.0, 'duration_days' => 36 * 30],
    5 => ['name' => 'Lifetime Equipment Trust Plan', 'roi_percent' => 7.0, 'duration_days' => 365 * 1000],
];

function add_days($date, $days) {
    $dt = new DateTime($date);
    $dt->add(new DateInterval("P{$days}D"));
    return $dt->format('Y-m-d');
}

echo "===============================\n";
echo " HRC Maintenance Auto-Maturity CRON\n";
echo "===============================\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Fetch all potential matured maintenance plans
    $query = $pdo->prepare("
        SELECT m.*, u.full_name, u.email
        FROM maintenance m
        JOIN users u ON m.user_id = u.id
        WHERE m.status = 'active'
    ");
    $query->execute();
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "✅ No active maintenance plans found for maturity check.\n";
        exit;
    }

    $processed = 0;
    foreach ($rows as $row) {
        $plan_id = intval($row['plan_id'] ?? 0);
        if (!$plan_id || !isset($plansRef[$plan_id])) continue;

        $plan = $plansRef[$plan_id];
        $created = $row['created_at'];
        $maturity = add_days($created, $plan['duration_days']);

        // skip if not yet matured
        if (strtotime($maturity) > strtotime(date('Y-m-d'))) continue;

        $user_id = $row['user_id'];
        $amount = floatval($row['amount']);
        $roi_percent = floatval($plan['roi_percent']);
        $roi_earned = round(($amount * $roi_percent) / 100, 2);
        $payout = round($amount + $roi_earned, 2);
        $ref = 'MNT-MAT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        try {
            $pdo->beginTransaction();

            // Update maintenance row
            $upd = $pdo->prepare("
                UPDATE maintenance
                SET roi_earned = ?, status = 'matured', updated_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([$roi_earned, $row['id']]);

            // Credit wallet
            $wallet = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
            $wallet->execute([$payout, $user_id]);

            // Log transaction
            $details = json_encode([
                'subtype' => 'maturity_unlock',
                'maintenance_id' => $row['id'],
                'roi_earned' => $roi_earned,
                'total_payout' => $payout
            ]);

            $txn = $pdo->prepare("
                INSERT INTO transactions (user_id, type, amount, reference, status, method, details, created_at)
                VALUES (?, 'maintenance', ?, ?, 'completed', 'wallet_address', ?, NOW())
            ");
            $txn->execute([$user_id, $payout, $ref, $details]);

            $pdo->commit();

            $processed++;
            echo "✅ Maintenance #{$row['id']} ({$plan['name']}) matured and credited.\n";

            // Send email notification
            try {
                sendEmail([
                    'to' => $row['email'],
                    'template' => 'maintenance_matured',
                    'variables' => [
                        'user_name' => $row['full_name'],
                        'plan_name' => $plan['name'],
                        'amount' => number_format($amount, 2),
                        'roi_earned' => number_format($roi_earned, 2),
                        'total_payout' => number_format($payout, 2),
                        'maturity_date' => $maturity,
                        'reference' => $ref
                    ]
                ]);

                // Admin notification
                if (defined('ADMIN_CONTACT_EMAIL') && ADMIN_CONTACT_EMAIL) {
                    sendEmail([
                        'to' => ADMIN_CONTACT_EMAIL,
                        'template' => 'admin_maintenance_notification',
                        'variables' => [
                            'user_name' => $row['full_name'],
                            'user_email' => $row['email'],
                            'plan_name' => $plan['name'],
                            'amount' => number_format($amount, 2),
                            'reference' => $ref
                        ]
                    ]);
                }
            } catch (Exception $mailError) {
                error_log("⚠️ Maintenance Email failed for ID {$row['id']}: " . $mailError->getMessage());
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("❌ Maintenance Cron Error [{$row['id']}] - " . $e->getMessage());
            echo "❌ Failed to process maintenance #{$row['id']} — " . $e->getMessage() . "\n";
        }
    }

    echo "\n✅ Completed processing. Total matured: {$processed}\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "❌ Cron failed: " . $e->getMessage() . "\n";
}
