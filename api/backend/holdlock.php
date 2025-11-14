<?php
/**
 * ==========================================================
 * HealthRunCare — HoldLock Backend API (Fixed)
 * ==========================================================
 * Handles:
 *  - get_summary
 *  - get_plans
 *  - get_active
 *  - get_matured
 *  - start_holdlock
 *  - unlock
 * ==========================================================
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/email.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

$user_id = $_SESSION['user_id'];
// Prefer JSON body first (works for fetch with JSON)
// fallback to $_POST/$_GET if needed
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST ?? $_GET;
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? null;

try {
    $pdo = getPDO();
} catch (Exception $e) {
    // Do not expose DB details
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed.']);
    exit;
}


// Simple response helper
function respond($status, $message, $data = [])
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

if (!$action) respond('error', 'No action specified.');

switch ($action) {

    /* =====================================================
       1️⃣ SUMMARY
       ===================================================== */
    case 'get_summary':
        $summaryStmt = $pdo->prepare("
            SELECT 
                COUNT(*) AS active_locks_count,
                COALESCE(SUM(amount), 0) AS total_locked,
                COALESCE(SUM(roi_earned), 0) AS total_roi,
                MIN(maturity_date) AS next_maturity
            FROM holdlock 
            WHERE user_id = ? AND status = 'locked'
        ");
        $summaryStmt->execute([$user_id]);
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: ['active_locks_count' => 0, 'total_locked' => 0, 'total_roi' => 0, 'next_maturity' => null];

        $walletStmt = $pdo->prepare("SELECT balance, total_earnings FROM wallets WHERE user_id = ?");
        $walletStmt->execute([$user_id]);
        $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC) ?: ['balance' => 0, 'total_earnings' => 0];

        respond('success', 'Summary loaded.', ['summary' => $summary, 'wallet' => $wallet]);
        break;

    /* =====================================================
       2️⃣ PLANS
       ===================================================== */
    case 'get_plans':
        $plans = [
            ['id' => 1, 'name' => 'Flexi Health Lock Plan', 'roi_percent' => 3.5, 'duration_days' => 180, 'min_deposit' => 10000, 'max_deposit' => 100000, 'lock_period' => '6 months', 'payout_option' => 'At maturity'],
            ['id' => 2, 'name' => 'Standard Lock & Grow Plan', 'roi_percent' => 8.0, 'duration_days' => 365, 'min_deposit' => 20000, 'max_deposit' => 300000, 'lock_period' => '12 months', 'payout_option' => 'Annual or at maturity'],
            ['id' => 3, 'name' => 'Executive LockPlus Plan', 'roi_percent' => 16.0, 'duration_days' => 730, 'min_deposit' => 50000, 'max_deposit' => 500000, 'lock_period' => '24 months', 'payout_option' => 'Annual or at maturity'],
            ['id' => 4, 'name' => 'Prestige Capital Hold Plan', 'roi_percent' => 28.0, 'duration_days' => 1095, 'min_deposit' => 250000, 'max_deposit' => null, 'lock_period' => '36 months', 'payout_option' => 'Flexible'],
            ['id' => 5, 'name' => 'Lifetime Reserve Lock Plan', 'roi_percent' => 7.0, 'duration_days' => 3650, 'min_deposit' => 1000000, 'max_deposit' => null, 'lock_period' => 'Lifetime', 'payout_option' => 'Annual']
        ];
        respond('success', 'Plans loaded.', ['plans' => $plans]);
        break;

    /* =====================================================
       3️⃣ ACTIVE LOCKS
       ===================================================== */
    case 'get_active':
        $stmt = $pdo->prepare("SELECT * FROM holdlock WHERE user_id = ? AND status = 'locked' ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $locks = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        respond('success', 'Active locks loaded.', ['locks' => $locks]);
        break;

    /* =====================================================
       4️⃣ MATURED LOCKS
       ===================================================== */
    case 'get_matured':
        $stmt = $pdo->prepare("SELECT * FROM holdlock WHERE user_id = ? AND status = 'matured' ORDER BY maturity_date ASC");
        $stmt->execute([$user_id]);
        $matured = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        respond('success', 'Matured locks loaded.', ['matured' => $matured]);
        break;

    /* =====================================================
       5️⃣ START HOLDLOCK
       ===================================================== */
    case 'start_holdlock':
        // Use $input (JSON) values primarily
        $planId = intval($input['plan_id'] ?? $input['planId'] ?? ($_POST['plan_id'] ?? 0));
        $amount = floatval($input['amount'] ?? ($_POST['amount'] ?? 0));
        if ($planId <= 0 || $amount <= 0) respond('error', 'Invalid plan or amount.');

        // Check wallet balance
        $wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $wallet->execute([$user_id]);
        $walletData = $wallet->fetch(PDO::FETCH_ASSOC);
        if (!$walletData || floatval($walletData['balance']) < $amount) respond('error', 'Insufficient wallet balance.');

        // Plans (server-side source of truth)
        $plans = [
            1 => ['name' => 'Flexi Health Lock Plan', 'roi_percent' => 3.5, 'duration_days' => 180],
            2 => ['name' => 'Standard Lock & Grow Plan', 'roi_percent' => 8.0, 'duration_days' => 365],
            3 => ['name' => 'Executive LockPlus Plan', 'roi_percent' => 16.0, 'duration_days' => 730],
            4 => ['name' => 'Prestige Capital Hold Plan', 'roi_percent' => 28.0, 'duration_days' => 1095],
            5 => ['name' => 'Lifetime Reserve Lock Plan', 'roi_percent' => 7.0, 'duration_days' => 3650]
        ];
        $plan = $plans[$planId] ?? null;
        if (!$plan) respond('error', 'Invalid plan.');

        $roi = $plan['roi_percent'];
        $duration = $plan['duration_days'];
        // maturity_date as YYYY-MM-DD
        $maturity_date = (new DateTime())->add(new DateInterval("P{$duration}D"))->format('Y-m-d');

        try {
            $pdo->beginTransaction();

            // Deduct funds
            $upd = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
            $upd->execute([$amount, $user_id]);

            // Create record in holdlock
            $stmt = $pdo->prepare("
                INSERT INTO holdlock (user_id, plan_name, amount, roi_percent, duration_days, maturity_date, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'locked', NOW())
            ");
            $stmt->execute([$user_id, $plan['name'], $amount, $roi, $duration, $maturity_date]);
            $holdlockId = $pdo->lastInsertId();

            // Transaction record (standardized type + structured details)
            $ref = 'HLD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

            $txn = $pdo->prepare("
                INSERT INTO transactions (user_id, type, amount, reference, status, method, details)
                VALUES (?, 'holdlock', ?, ?, 'completed', 'wallet',
                    JSON_OBJECT(
                        'subtype', 'start',
                        'plan', ?,
                        'roi_percent', ?,
                        'duration_days', ?,
                        'maturity_date', ?
                    )
                )
            ");
            $txn->execute([$user_id, $amount, $ref, $plan['name'], $roi, $duration, $maturity_date]);


            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            // log and return generic error
            error_log("HoldLock start error: " . $e->getMessage());
            respond('error', 'Failed to start holdlock. Try again later.');
        }

        // ==================================================
        // EMAIL NOTIFICATION LOGIC
        // ==================================================
        try {
            $userStmt = $pdo->prepare("SELECT full_name, name, email FROM users WHERE id = ?");
            $userStmt->execute([$user_id]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if ($user && !empty($user['email'])) {
                $displayName = !empty($user['full_name']) ? $user['full_name'] : ($user['name'] ?? 'User');

                // User email (using template + variables)
                $uResult = sendEmail([
                    'to'       => $user['email'],
                    'template' => 'holdlock_started',
                    'variables'=> [
                        'user_name'     => $displayName,
                        'plan_name'     => $plan['name'],
                        'amount'        => number_format($amount, 2),
                        'roi_percent'   => $roi,
                        'duration_days' => $duration,
                        'penalty_percent'=> '10',
                        'maturity_date' => $maturity_date,
                        'reference'     => $ref
                    ]
                ]);
                if (!$uResult) {
                    error_log("HoldLock email to user failed for user_id {$user_id} ({$user['email']})");
                    file_put_contents(__DIR__ . '/../../logs/email.log', "[".date('Y-m-d H:i:s')."] HoldLock user email FAILED: user_id {$user_id} | email: {$user['email']}\n", FILE_APPEND);
                } else {
                    file_put_contents(__DIR__ . '/../../logs/email.log', "[".date('Y-m-d H:i:s')."] HoldLock user email SENT: user_id {$user_id} | email: {$user['email']} | ref: {$ref}\n", FILE_APPEND);
                }

                // Admin email
                $aResult = sendEmail([
                    'to'       => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_holdlock_notification',
                    'variables'=> [
                        'user_name' => $displayName,
                        'user_email'=> $user['email'],
                        'plan_name' => $plan['name'],
                        'amount'    => number_format($amount, 2),
                        'reference' => $ref
                    ]
                ]);
                if (!$aResult) {
                    error_log("HoldLock admin email failed for ref {$ref}");
                    file_put_contents(__DIR__ . '/../../logs/email.log', "[".date('Y-m-d H:i:s')."] HoldLock admin email FAILED: ref {$ref}\n", FILE_APPEND);
                } else {
                    file_put_contents(__DIR__ . '/../../logs/email.log', "[".date('Y-m-d H:i:s')."] HoldLock admin email SENT: ref {$ref}\n", FILE_APPEND);
                }
            } else {
                // Missing user email - log
                error_log("HoldLock: user not found or missing email for user_id {$user_id}");
                file_put_contents(__DIR__ . '/../../logs/email.log', "[".date('Y-m-d H:i:s')."] HoldLock: missing user email for user_id {$user_id}\n", FILE_APPEND);
            }
        } catch (Exception $e) {
            // Log but don't break API flow
            error_log("HoldLock email step error: " . $e->getMessage());
            file_put_contents(__DIR__ . '/../../logs/email.log', "[".date('Y-m-d H:i:s')."] HoldLock email exception: " . $e->getMessage() . "\n", FILE_APPEND);
        }

        respond('success', 'HoldLock started successfully.', ['reference' => $ref, 'holdlock_id' => $holdlockId ?? null]);
        break;


    /* =====================================================
       6️⃣ UNLOCK REQUEST
       ===================================================== */
    case 'unlock':
        $lockId = intval($input['holdlock_id'] ?? $input['holdlockId'] ?? ($_POST['holdlock_id'] ?? 0));
        $early = intval($input['early'] ?? ($_POST['early'] ?? 0));

        if ($lockId <= 0) respond('error', 'Invalid holdlock id.');

        $stmt = $pdo->prepare("SELECT * FROM holdlock WHERE id = ? AND user_id = ?");
        $stmt->execute([$lockId, $user_id]);
        $lock = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$lock) respond('error', 'HoldLock not found.');

        // Update status to unlock_pending (consistent with DB enum)
        $upd = $pdo->prepare("UPDATE holdlock SET status = 'unlock_pending', updated_at = NOW() WHERE id = ?");
        $upd->execute([$lockId]);

        respond('success', $early ? 'Early unlock initiated.' : 'Unlock request successful.');
        break;

    default:
        respond('error', 'Invalid action.');
}
