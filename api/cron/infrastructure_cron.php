<?php
/**
 * ============================================================
 * HealthRunCare — Infrastructure Auto-Maturity Cron (Final)
 * ============================================================
 * Location: /api/cron/infrastructure_cron.php
 *
 * ✅ Runs daily (via cron)
 * ✅ Finds all matured Infrastructure contributions (status='active')
 * ✅ Credits user wallet
 * ✅ Logs transaction
 * ✅ Sends "Infrastructure Matured" email
 * ✅ Marks as 'matured'
 * ============================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/../backend/email.php';

header('Content-Type: text/plain; charset=utf-8');

// Restrict execution to CLI or localhost for security
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    exit("Access Denied\n");
}

try {
    $pdo = getPDO();
} catch (Exception $e) {
    exit("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Reference Plans (same as backend)
$plansRef = [
    1 => ['name' => 'Basic Diagnostic Plan', 'roi_percent' => 9.0, 'duration_days' => 365],
    2 => ['name' => 'Imaging Growth Plan', 'roi_percent' => 13.5, 'duration_days' => 540],
    3 => ['name' => 'Advanced Radiology Plan', 'roi_percent' => 17.5, 'duration_days' => 730],
    4 => ['name' => 'Dialysis Infrastructure Plan', 'roi_percent' => 20.0, 'duration_days' => 900],
    5 => ['name' => 'Complete Operating Room Equipment Plan', 'roi_percent' => 22.5, 'duration_days' => 1095],
    6 => ['name' => 'Hospital Diagnostic Wing Installation Plan', 'roi_percent' => 29.0, 'duration_days' => 1095],
];

function add_days($date, $days) {
    $dt = new DateTime($date);
    $dt->add(new DateInterval("P{$days}D"));
    return $dt->format('Y-m-d');
}

echo "===============================\n";
echo " HRC Infrastructure Auto-Maturity CRON\n";
echo "===============================\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Fetch all active contributions that might have matured
    $query = $pdo->prepare("
        SELECT ic.*, u.full_name, u.email
        FROM infrastructure_contributions ic
        JOIN users u ON ic.user_id = u.id
        WHERE ic.status = 'active'
    ");
    $query->execute();
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "✅ No active contributions to check.\n";
        exit;
    }

    $processed = 0;
    foreach ($rows as $row) {
        $plan_id = intval($row['plan_id'] ?? 0);
        if (!$plan_id || !isset($plansRef[$plan_id])) continue;

        $plan = $plansRef[$plan_id];
        $created = $row['created_at'];
        $maturity = add_days($created, $plan['duration_days']);

        // Skip if not yet matured
        if (strtotime($maturity) > strtotime(date('Y-m-d'))) continue;

        $user_id = $row['user_id'];
        $amount = floatval($row['amount']);
        $roi_percent = floatval($plan['roi_percent']);
        $roi_earned = round(($amount * $roi_percent) / 100, 2);
        $payout = round($amount + $roi_earned, 2);
        $ref = 'INF-MAT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        try {
            $pdo->beginTransaction();

            // 1️⃣ Mark contribution as matured
            $upd = $pdo->prepare("
                UPDATE infrastructure_contributions
                SET roi_earned = ?, status = 'matured', updated_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([$roi_earned, $row['id']]);

            // 2️⃣ Credit wallet
            $wallet = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
            $wallet->execute([$payout, $user_id]);

            // 3️⃣ Log transaction
            $details = json_encode([
                'subtype' => 'maturity_unlock',
                'contribution_id' => $row['id'],
                'roi_earned' => $roi_earned,
                'total_payout' => $payout
            ]);

            $txn = $pdo->prepare("
                INSERT INTO transactions (user_id, type, amount, reference, status, method, details, created_at)
                VALUES (?, 'infrastructure', ?, ?, 'completed', 'wallet_address', ?, NOW())
            ");
            $txn->execute([$user_id, $payout, $ref, $details]);

            $pdo->commit();
            $processed++;

            echo "✅ Contribution #{$row['id']} ({$plan['name']}) matured and credited successfully.\n";

            // 4️⃣ Send email notification
            try {
                sendEmail([
                    'to' => $row['email'],
                    'template' => 'infrastructure_matured',
                    'variables' => [
                        'user_name' => $row['full_name'],
                        'plan_name' => $plan['name'],
                        'amount' => number_format($amount, 2),
                        'roi_earned' => number_format($roi_earned, 2),
                        'payout' => number_format($payout, 2),
                        'maturity_date' => $maturity,
                        'reference' => $ref
                    ]
                ]);
            } catch (Exception $mailError) {
                error_log("⚠️ Email failed for contribution ID {$row['id']}: " . $mailError->getMessage());
                echo "⚠️ Email sending failed for #{$row['id']}.\n";
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("❌ Infrastructure Cron Error [{$row['id']}] - " . $e->getMessage());
            echo "❌ Failed to process contribution #{$row['id']}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n✅ Completed processing. Total matured: {$processed}\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "❌ Cron failed: " . $e->getMessage() . "\n";
}
