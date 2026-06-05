<?php
// ===============================================
// FILE: /api/backend/investment.php
// PURPOSE: Investment controller for TitanXHoldings
// ACTIONS: get_summary, get_plans, start_investment, get_active, unlock_investment
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

function generateReference($prefix = 'TXH-INV') {
    return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
}


// --------------------- ACTION: get_plans ---------------------
if ($action === 'get_plans') {
    $stmt = $pdo->query("SELECT * FROM investment_plans ORDER BY id ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $plans = [];
    foreach ($rows as $r) {
        $plans[] = [
            'id' => (int)$r['id'],
            'title' => $r['title'],
            'description' => $r['description'],
            'details' => $r['details'],
            'summary' => $r['summary'],

            // financials
            'roi_percent' => (float)$r['roi_percent'],
            'duration_days' => (int)$r['duration_days'],
            'payout_option' => $r['payout_option'],

            // ⚠️ Map DB → JS names:
            'min' => (float)$r['min_amount'],
            'max' => (float)$r['max_amount'],

            // extras
            'risk' => $r['risk'],
            'income' => $r['income'],
            'icon' => $r['icon'],
            'color' => $r['color']
        ];
    }

    jsonResponse('success', 'Plans loaded.', ['plans' => $plans]);
}


// --------------------- ACTION: get_summary ---------------------
if ($action === 'get_summary') {
    try {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total_active, COALESCE(SUM(roi_earned),0) AS total_roi, COUNT(CASE WHEN status='active' THEN 1 END) AS ongoing_count, MIN(CASE WHEN maturity_date >= CURDATE() THEN maturity_date ELSE NULL END) AS next_maturity FROM investments WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total_active = (float)($row['total_active'] ?? 0.00);
        $total_roi = (float)($row['total_roi'] ?? 0.00);
        $ongoing = (int)($row['ongoing_count'] ?? 0);
        $next_maturity = $row['next_maturity'] ? date('M d, Y', strtotime($row['next_maturity'])) : '—';

        $wstmt = $pdo->prepare("SELECT balance, total_investments, total_earnings FROM wallets WHERE user_id = ?");
        $wstmt->execute([$user_id]);
        $wallet = $wstmt->fetch(PDO::FETCH_ASSOC) ?: ['balance' => 0.00, 'total_investments' => 0.00, 'total_earnings' => 0.00];

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
        $stmt = $pdo->prepare("SELECT id, plan_name, amount, roi_percent, duration_days, status, maturity_date, roi_earned, created_at FROM investments WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)$r['id'],
                'plan' => $r['plan_name'],
                'amount' => (float)$r['amount'],
                'roi_percent' => (float)$r['roi_percent'],
                'duration_days' => (int)$r['duration_days'],
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
    $stmt = $pdo->prepare("SELECT id, plan_name, amount, roi_percent, roi_earned, maturity_date
        FROM investments WHERE user_id = ? AND status = 'active' AND maturity_date <= CURDATE()");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        if ((float)$r['roi_earned'] <= 0) {
            $r['roi_earned'] = round($r['amount'] * $r['roi_percent'] / 100, 2);
        }
        $r['total_payout'] = round($r['amount'] + $r['roi_earned'], 2);
        $r['maturity_date'] = date('M d, Y', strtotime($r['maturity_date']));
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
    $pstmt = $pdo->prepare("SELECT title, roi_percent, duration_days, min_amount, max_amount FROM investment_plans WHERE id = ? LIMIT 1");
    $pstmt->execute([$plan_id]);
    $plan = $pstmt->fetch(PDO::FETCH_ASSOC);
    if (!$plan) jsonResponse('error', 'Invalid plan selected.');

    // ✅ Range validation
    $min = (float)$plan['min_amount'];
    $max = (float)$plan['max_amount'];
    if ($amount < $min || $amount > $max) {
        jsonResponse('error', "Amount must be between $$min and $$max for this plan.");
    }

    $roi_percent = (float) $plan['roi_percent'];
    $duration_days = (int) $plan['duration_days'];

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

        $maturity_date = date('Y-m-d', strtotime("+{$duration_days} days"));
        $reference = generateReference('TXH-INV');
        $now = date('Y-m-d H:i:s');

        // Deduct wallet
        $pdo->prepare("UPDATE wallets SET balance = balance - ?, total_investments = total_investments + ? WHERE user_id = ?")
            ->execute([$amount, $amount, $user_id]);

        // Create investment
        $pdo->prepare("INSERT INTO investments (user_id, plan_name, amount, roi_percent, duration_days, status, maturity_date, roi_earned, created_at)
                       VALUES (?, ?, ?, ?, ?, 'active', ?, 0.00, ?)")
            ->execute([$user_id, $plan['title'], $amount, $roi_percent, $duration_days, $maturity_date, $now]);

        $investment_id = (int)$pdo->lastInsertId();

        // Transaction record
        $details = json_encode([
            'investment_id' => $investment_id,
            'plan_id' => $plan_id,
            'plan_name' => $plan['title']
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference, status, details, created_at)
                       VALUES (?, 'investment', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $amount, $reference, $details, $now]);

        $pdo->commit();

        // Emails
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'investment_confirmed',
                'variables' => [
                    'user_name' => $user_name,
                    'plan_name' => $plan['title'],
                    'amount' => number_format($amount, 2),
                    'roi_percent' => $roi_percent,
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
                        'plan_name' => $plan['title'],
                        'amount' => number_format($amount, 2),
                        'reference' => $reference,
                    ]
                ]);
            }
        }

        jsonResponse('success', 'Investment started successfully.', [
            'investment_id' => $investment_id,
            'reference' => $reference,
            'maturity_date' => date('M d, Y', strtotime($maturity_date))
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('start_investment error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to start investment. Please try again.');
    }
}

// --------------------- ACTION: unlock_investment ---------------------
if ($action === 'unlock_investment') {
    // (unchanged, logic already correct)
    $inv_id = (int) ($input['investment_id'] ?? 0);
    if ($inv_id <= 0) jsonResponse('error', 'Invalid investment id.');

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id, user_id, amount, roi_percent, duration_days, status, maturity_date, roi_earned FROM investments WHERE id = ? FOR UPDATE");
        $stmt->execute([$inv_id]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$inv) {
            $pdo->rollBack();
            jsonResponse('error', 'Investment not found.');
        }
        if ((int)$inv['user_id'] !== $user_id) {
            $pdo->rollBack();
            jsonResponse('error', 'Permission denied.');
        }
        if ($inv['status'] !== 'active') {
            $pdo->rollBack();
            jsonResponse('error', 'Investment is not active.');
        }
        if (strtotime($inv['maturity_date']) > strtotime(date('Y-m-d'))) {
            $pdo->rollBack();
            jsonResponse('error', 'Investment has not matured yet.');
        }

        $roi_earned = (float)$inv['roi_earned'] ?: round(((float)$inv['amount'] * (float)$inv['roi_percent'] / 100), 2);
        $total_payout = round((float)$inv['amount'] + $roi_earned, 2);

        $pdo->prepare("UPDATE investments SET status = 'completed', roi_earned = ? WHERE id = ?")
            ->execute([$roi_earned, $inv_id]);
        $pdo->prepare("UPDATE wallets SET balance = balance + ?, total_earnings = total_earnings + ? WHERE user_id = ?")
            ->execute([$total_payout, $roi_earned, $user_id]);

        $reference = generateReference('TXH-INVPAY');
        $details = json_encode(['investment_id' => $inv_id, 'payout' => $total_payout, 'roi' => $roi_earned]);
        $now = date('Y-m-d H:i:s');
        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference, status, details, created_at)
                       VALUES (?, 'investment', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $total_payout, $reference, $details, $now]);

        $pdo->commit();

        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'investment_matured',
                'variables' => [
                    'user_name' => $user_name,
                    'investment_id' => $inv_id,
                    'payout' => number_format($total_payout, 2),
                    'roi_earned' => number_format($roi_earned, 2),
                    'reference' => $reference
                ]
            ]);
        }

        jsonResponse('success', 'Investment unlocked and credited to your wallet.', [
            'payout' => $total_payout,
            'roi_earned' => $roi_earned,
            'reference' => $reference
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('unlock_investment error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to unlock investment. Please try again.');
    }
}

// default
http_response_code(400);
jsonResponse('error', 'Invalid action.');
