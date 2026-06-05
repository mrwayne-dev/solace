<?php
/**
 * ==========================================================
 * TitanXHoldings — HoldLock Backend API (DYNAMIC VERSION)
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

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on',
    'cookie_samesite' => 'Strict',
]);

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/email.php';

$user_id = intval($_SESSION['user_id']);
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST ?? $_GET;
$action = $input['action'] ?? null;

// Quick response helper
function respond($status, $message, $data = [])
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// Connect to DB
try {
    $pdo = getPDO();
} catch (Exception $e) {
    respond('error', 'Database connection failed.');
}

if (!$action) respond('error', 'No action specified.');


/* =====================================================
   1️⃣ SUMMARY
   ===================================================== */
if ($action === 'get_summary') {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS active_locks_count,
            COALESCE(SUM(amount), 0) AS total_locked,
            COALESCE(SUM(roi_earned), 0) AS total_roi,
            MIN(maturity_date) AS next_maturity
        FROM holdlock 
        WHERE user_id = ? AND status = 'locked'
    ");
    $stmt->execute([$user_id]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    $wallet = $pdo->prepare("SELECT balance, total_earnings FROM wallets WHERE user_id = ?");
    $wallet->execute([$user_id]);
    $walletData = $wallet->fetch(PDO::FETCH_ASSOC);

    respond('success', 'Summary loaded.', [
        'summary' => $summary,
        'wallet' => $walletData ?: ['balance' => 0, 'total_earnings' => 0]
    ]);
}


/* =====================================================
   2️⃣ GET PLANS (FROM DATABASE)
   ===================================================== */
if ($action === 'get_plans') {
    $stmt = $pdo->query("
        SELECT 
            id, name, purpose, income_source, min_amount, max_amount, 
            lock_period_text, duration_days, roi_range, 
            risk, payout, summary, icon, color
        FROM holdlock_plans
        ORDER BY id ASC
    ");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond('success', 'Plans loaded.', ['plans' => $plans]);
}



/* =====================================================
   3️⃣ ACTIVE LOCKS
   ===================================================== */
if ($action === 'get_active') {
    $stmt = $pdo->prepare("SELECT * FROM holdlock WHERE user_id = ? AND status = 'locked' ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    respond('success', 'Active locks loaded.', ['locks' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}


/* =====================================================
   4️⃣ MATURED LOCKS (ELIGIBLE FOR UNLOCK)
   ===================================================== */
if ($action === 'get_matured') {
    $stmt = $pdo->prepare("
        SELECT id, plan_name, amount, roi_earned, maturity_date 
        FROM holdlock 
        WHERE user_id = ? AND status = 'matured' 
        ORDER BY maturity_date ASC
    ");
    $stmt->execute([$user_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as &$r) {
        if ((float)$r['roi_earned'] <= 0) {
            $r['roi_earned'] = round($r['amount'] * 0.01, 2); // Default small earnings safeguard
        }
        $r['total_payout'] = round($r['roi_earned'] + $r['amount'], 2);
        $r['maturity_date'] = date('M d, Y', strtotime($r['maturity_date']));
    }

    respond('success', 'Matured locks loaded.', ['matured' => $records]);
}


/* =====================================================
   5️⃣ START HOLDLOCK (DYNAMIC VALIDATION)
   ===================================================== */
if ($action === 'start_holdlock') {
    $planId = intval($input['plan_id'] ?? 0);
    $amount = floatval($input['amount'] ?? 0);
    if ($planId <= 0 || $amount <= 0) respond('error', 'Invalid plan or amount.');

    // Wallet Balance Check
    $wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $wallet->execute([$user_id]);
    $bal = floatval($wallet->fetchColumn());
    if ($bal < $amount) respond('error', 'Insufficient wallet balance.');

    // Fetch plan from DB
    $stmt = $pdo->prepare("
        SELECT name, roi_range, duration_days, min_amount, max_amount
        FROM holdlock_plans WHERE id = ? LIMIT 1
    ");
    $stmt->execute([$planId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$plan) respond('error', 'Invalid plan selected.');

    // Validate deposit range
    $min = floatval($plan['min_amount']);
    $max = $plan['max_amount'] === null ? null : floatval($plan['max_amount']);

    if ($amount < $min) respond('error', "Minimum deposit is $$min.");
    if ($max !== null && $amount > $max) respond('error', "Maximum deposit is $$max.");

    // Parse ROI % from "3–4%"
    preg_match('/([\d.]+)/', $plan['roi_range'], $match);
    $roi_percent = isset($match[1]) ? floatval($match[1]) : 0;

    $duration = intval($plan['duration_days']);
    $maturity = $duration > 0 ? (new DateTime())->add(new DateInterval("P{$duration}D"))->format('Y-m-d') : null;

    try {
        $pdo->beginTransaction();

        // Deduct wallet
        $pdo->prepare("UPDATE wallets SET balance = balance - ?, total_investments = total_investments + ? WHERE user_id = ?")
            ->execute([$amount, $amount, $user_id]);

        // Create holdlock record
        $ps = $pdo->prepare("
            INSERT INTO holdlock (user_id, plan_name, amount, roi_percent, duration_days, maturity_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'locked', NOW())
        ");
        $ps->execute([$user_id, $plan['name'], $amount, $roi_percent, $duration, $maturity]);
        $lockId = $pdo->lastInsertId();

        // Add transaction record
        $ref = "HLD-" . strtoupper(substr(md5(uniqid(true)), 0, 10));
        $txn = $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, reference, status, method, details)
            VALUES (?, 'holdlock', ?, ?, 'completed', 'wallet',
            JSON_OBJECT('subtype','start','plan',?,'roi_percent',?,'duration_days',?,'maturity_date',?,'holdlock_id',?))
        ");
        $txn->execute([$user_id, $amount, $ref, $plan['name'], $roi_percent, $duration, $maturity, $lockId]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        respond('error', 'Failed to start HoldLock.');
    }


    // ============ SEND EMAIL NOTIFICATION ============
    $userQ = $pdo->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
    $userQ->execute([$user_id]);
    $user = $userQ->fetch(PDO::FETCH_ASSOC);

    sendEmail([
        'to' => $user['email'],
        'template' => 'holdlock_started',
        'variables' => [
            'user_name' => $user['name'],
            'plan_name' => $plan['name'],
            'amount' => number_format($amount, 2),
            'roi_percent' => $roi_percent,
            'duration_days' => $duration,
            'penalty_percent' => 0, // change if you’ll support early penalty later
            'maturity_date' => $maturity,
            'reference' => $ref
        ],
        'cc_admin' => true, // send admin a copy
        'admin_template' => 'admin_holdlock_notification'
    ]);

    respond('success', 'HoldLock started.', ['reference' => $ref, 'holdlock_id' => $lockId]);
}


/* =====================================================
   6️⃣ UNLOCK REQUEST
   ===================================================== */
if ($action === 'unlock') {
    $lockId = intval($input['holdlock_id'] ?? 0);
    $early = intval($input['early'] ?? 0);

    if ($lockId <= 0) respond('error', 'Invalid Lock ID.');

    $stmt = $pdo->prepare("SELECT id FROM holdlock WHERE id = ? AND user_id = ?");
    $stmt->execute([$lockId, $user_id]);
    if (!$stmt->fetch()) respond('error', 'HoldLock not found.');

    $stmt = $pdo->prepare("SELECT amount, roi_percent FROM holdlock WHERE id = ? AND user_id = ?");
    $stmt->execute([$lockId, $user_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $amount = floatval($data['amount']);
    $roi = floatval($data['roi_percent']);
    $earned = round($amount * ($roi/100), 2);
    $payout = $amount + $earned;

    $pdo->beginTransaction();

    $pdo->prepare("UPDATE holdlock SET status = 'unlocked', roi_earned = ?, updated_at = NOW() WHERE id = ?")
        ->execute([$earned, $lockId]);

    $pdo->prepare("UPDATE wallets SET balance = balance + ?, total_earnings = total_earnings + ? WHERE user_id = ?")
        ->execute([$payout, $earned, $user_id]);

    $pdo->commit();

    respond('success', 'HoldLock unlocked and funds credited.', [
        'payout' => $payout,
        'earned' => $earned
    ]);

}


respond('error', 'Invalid action.');
