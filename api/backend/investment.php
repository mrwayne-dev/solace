<?php
// ===============================================
// FILE: /api/backend/investment.php
// PURPOSE: Mining-contract controller for Solace Mining
// MODEL:   Tiered plans (Bronze..VIP). Each contract pays a
//          fixed daily profit for `duration_days`; principal is
//          returned to the wallet on completion (see cron).
// ACTIONS: get_plans, get_summary, get_active, get_matured, start_investment
// ===============================================

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => false, // set true in production with HTTPS
    'cookie_samesite' => 'Strict',
]);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // tighten in production
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// includes
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/email.php'; // uses sendEmail()

// auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}
$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? ($_SESSION['name'] ?? 'User');
$user_email = $_SESSION['email'] ?? '';

// get pdo
try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// parse input
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
$action = trim($input['action'] ?? 'get_summary');

// helper responses
function jsonResponse($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

function generateReference($prefix = 'SLM-INV') {
    return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
}


// --------------------- ACTION: get_plans ---------------------
if ($action === 'get_plans') {
    $stmt = $pdo->query("SELECT * FROM investment_plans WHERE status = 'active' ORDER BY min_amount ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $plans = [];
    foreach ($rows as $r) {
        $daily = (float)$r['daily_profit_percent'];
        $days  = (int)$r['duration_days'];
        $plans[] = [
            'id'                  => (int)$r['id'],
            'name'                => $r['name'],
            'title'               => $r['name'], // backwards-compatible alias
            'summary'             => $r['summary'],
            'daily_profit_percent'=> $daily,
            'duration_days'       => $days,
            'total_return_percent'=> round($daily * $days, 2),
            'referral_commission_percent' => (float)$r['referral_commission_percent'],
            'min'                 => (float)$r['min_amount'],
            'max'                 => $r['max_amount'] !== null ? (float)$r['max_amount'] : null,
            'icon'                => $r['icon'],
            'color'               => $r['color'],
        ];
    }

    jsonResponse('success', 'Plans loaded.', ['plans' => $plans]);
}


// --------------------- ACTION: get_summary ---------------------
if ($action === 'get_summary') {
    try {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN status='active' THEN amount ELSE 0 END),0) AS total_active, COALESCE(SUM(roi_earned),0) AS total_roi, COUNT(CASE WHEN status='active' THEN 1 END) AS ongoing_count, MIN(CASE WHEN status='active' AND maturity_date >= CURDATE() THEN maturity_date ELSE NULL END) AS next_maturity FROM investments WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total_active = (float)($row['total_active'] ?? 0.00);
        $total_roi = (float)($row['total_roi'] ?? 0.00);
        $ongoing = (int)($row['ongoing_count'] ?? 0);
        $next_maturity = $row['next_maturity'] ? date('M d, Y', strtotime($row['next_maturity'])) : '—';

        $wstmt = $pdo->prepare("SELECT balance, total_investments, total_earnings, referral_earnings FROM wallets WHERE user_id = ?");
        $wstmt->execute([$user_id]);
        $wallet = $wstmt->fetch(PDO::FETCH_ASSOC) ?: ['balance' => 0.00, 'total_investments' => 0.00, 'total_earnings' => 0.00, 'referral_earnings' => 0.00];

        jsonResponse('success', 'Investment summary loaded.', [
            'summary' => [
                'active_investments_value' => round($total_active, 2),
                'total_roi' => round($total_roi, 2),
                'ongoing_plans_count' => $ongoing,
                'next_maturity' => $next_maturity,
            ],
            'wallet' => [
                'balance' => (float)$wallet['balance'],
                'total_investments' => (float)$wallet['total_investments'],
                'total_earnings' => (float)$wallet['total_earnings'],
                'referral_earnings' => (float)$wallet['referral_earnings'],
            ]
        ]);
    } catch (Exception $e) {
        error_log('Investment summary error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load investment summary.');
    }
}

// --------------------- ACTION: get_active ---------------------
if ($action === 'get_active') {
    try {
        $stmt = $pdo->prepare("SELECT id, plan_name, amount, daily_profit_percent, duration_days, days_paid, status, maturity_date, roi_earned, created_at FROM investments WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)$r['id'],
                'plan' => $r['plan_name'],
                'amount' => (float)$r['amount'],
                'daily_profit_percent' => (float)$r['daily_profit_percent'],
                'duration_days' => (int)$r['duration_days'],
                'days_paid' => (int)$r['days_paid'],
                'status' => $r['status'],
                'maturity_date' => $r['maturity_date'] ? date('M d, Y', strtotime($r['maturity_date'])) : null,
                'roi_earned' => (float)$r['roi_earned'],
                'date_started' => date('M d, Y', strtotime($r['created_at']))
            ];
        }
        jsonResponse('success', 'Active investments loaded.', ['investments' => $out]);
    } catch (Exception $e) {
        error_log('get_active error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load active investments.');
    }
}


// --------------------- ACTION: get_matured ---------------------
if ($action === 'get_matured') {
    $stmt = $pdo->prepare("SELECT id, plan_name, amount, daily_profit_percent, duration_days, roi_earned, maturity_date
        FROM investments WHERE user_id = ? AND status = 'completed' ORDER BY maturity_date DESC");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['total_payout'] = round((float)$r['amount'] + (float)$r['roi_earned'], 2);
        $r['maturity_date'] = $r['maturity_date'] ? date('M d, Y', strtotime($r['maturity_date'])) : '—';
    }
    jsonResponse('success', 'Matured plans loaded.', ['matured' => $rows]);
}


// --------------------- ACTION: start_investment ---------------------
if ($action === 'start_investment') {
    $plan_id = (int) ($input['plan_id'] ?? 0);
    $amount = (float) ($input['amount'] ?? 0);

    if ($plan_id <= 0) jsonResponse('error', 'Invalid plan selected.');
    if ($amount <= 0) jsonResponse('error', 'Enter a valid investment amount.');

    // Fetch the selected plan from the catalog
    $pstmt = $pdo->prepare("SELECT name, daily_profit_percent, duration_days, referral_commission_percent, min_amount, max_amount FROM investment_plans WHERE id = ? AND status = 'active' LIMIT 1");
    $pstmt->execute([$plan_id]);
    $plan = $pstmt->fetch(PDO::FETCH_ASSOC);
    if (!$plan) jsonResponse('error', 'Invalid plan selected.');

    // Range validation (max NULL = unlimited)
    $min = (float)$plan['min_amount'];
    $max = $plan['max_amount'] !== null ? (float)$plan['max_amount'] : null;
    if ($amount < $min || ($max !== null && $amount > $max)) {
        $maxLabel = $max !== null ? '$' . number_format($max, 2) : 'unlimited';
        jsonResponse('error', "Amount must be between $" . number_format($min, 2) . " and {$maxLabel} for this plan.");
    }

    $daily_profit = (float) $plan['daily_profit_percent'];
    $duration_days = (int) $plan['duration_days'];
    $ref_commission = (float) $plan['referral_commission_percent'];

    try {
        $pdo->beginTransaction();

        // fetch wallet
        $wstmt = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        $wallet = $wstmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
            $wstmt->execute([$user_id]);
            $wallet = $wstmt->fetch(PDO::FETCH_ASSOC);
        }

        if ((float)$wallet['balance'] < $amount) {
            $pdo->rollBack();
            jsonResponse('error', 'Insufficient wallet balance.');
        }

        $start_date = date('Y-m-d');
        $maturity_date = date('Y-m-d', strtotime("+{$duration_days} days"));
        $reference = generateReference('SLM-INV');
        $now = date('Y-m-d H:i:s');

        // Deduct wallet
        $pdo->prepare("UPDATE wallets SET balance = balance - ?, total_investments = total_investments + ? WHERE user_id = ?")
            ->execute([$amount, $amount, $user_id]);

        // Create investment
        $pdo->prepare("INSERT INTO investments (user_id, plan_id, plan_name, amount, daily_profit_percent, duration_days, days_paid, status, start_date, maturity_date, last_payout_date, roi_earned, reference, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, 0, 'active', ?, ?, ?, 0.00, ?, ?)")
            ->execute([$user_id, $plan_id, $plan['name'], $amount, $daily_profit, $duration_days, $start_date, $maturity_date, $start_date, $reference, $now]);

        $investment_id = (int)$pdo->lastInsertId();

        // Transaction record
        $details = json_encode([
            'investment_id' => $investment_id,
            'plan_id' => $plan_id,
            'plan_name' => $plan['name']
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference, status, details, created_at)
                       VALUES (?, 'investment', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $amount, $reference, $details, $now]);

        // --- Referral commission: pay the referrer (if any) on this investment ---
        $rstmt = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
        $rstmt->execute([$user_id]);
        $referrer_id = (int)($rstmt->fetchColumn() ?: 0);
        if ($referrer_id > 0 && $ref_commission > 0) {
            $commission = round($amount * $ref_commission / 100, 2);
            if ($commission > 0) {
                $refReference = generateReference('SLM-REF');
                // ensure referrer wallet exists
                $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)
                               ON DUPLICATE KEY UPDATE user_id = user_id")->execute([$referrer_id]);
                $pdo->prepare("UPDATE wallets SET balance = balance + ?, referral_earnings = referral_earnings + ? WHERE user_id = ?")
                    ->execute([$commission, $commission, $referrer_id]);
                $pdo->prepare("INSERT INTO referral_earnings (user_id, referred_user_id, source_investment_id, amount, commission_percent, status, reference, created_at)
                               VALUES (?, ?, ?, ?, ?, 'credited', ?, ?)")
                    ->execute([$referrer_id, $user_id, $investment_id, $commission, $ref_commission, $refReference, $now]);
                $refDetails = json_encode(['from_user_id' => $user_id, 'investment_id' => $investment_id, 'commission_percent' => $ref_commission]);
                $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                               VALUES (?, 'referral', 'system', ?, ?, 'completed', ?, ?)")
                    ->execute([$referrer_id, $commission, $refReference, $refDetails, $now]);
            }
        }

        $pdo->commit();

        // Emails
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'investment_confirmed',
                'variables' => [
                    'user_name' => $user_name,
                    'plan_name' => $plan['name'],
                    'amount' => number_format($amount, 2),
                    'roi_percent' => $daily_profit . '% daily',
                    'duration_days' => $duration_days,
                    'maturity_date' => date('M d, Y', strtotime($maturity_date)),
                    'reference' => $reference,
                ]
            ]);

            if (defined('ADMIN_CONTACT_EMAIL')) {
                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_investment_notification',
                    'variables' => [
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'plan_name' => $plan['name'],
                        'amount' => number_format($amount, 2),
                        'reference' => $reference,
                    ]
                ]);
            }
        }

        jsonResponse('success', 'Mining contract started successfully.', [
            'investment_id' => $investment_id,
            'reference' => $reference,
            'maturity_date' => date('M d, Y', strtotime($maturity_date))
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('start_investment error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to start mining contract. Please try again.');
    }
}

// default
http_response_code(400);
jsonResponse('error', 'Invalid action.');
