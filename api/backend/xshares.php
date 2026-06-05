<?php
// ===============================================
// FILE: /api/backend/xshares.php
// PURPOSE: X-Shares controller for TitanXHoldings
// ACTIONS: get_assets, get_summary, get_active, get_matured, start_xshares, unlock
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

// helper responses (guarded — see xweekly.php for rationale)
if (!function_exists('jsonResponse')) {
    function jsonResponse($status, $message, $data = []) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }
}

if (!function_exists('generateReference')) {
    function generateReference($prefix = 'TXH-SHR') {
        return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
    }
}


// --------------------- ACTION: get_assets ---------------------
if ($action === 'get_assets') {
    try {
        $stmt = $pdo->query("SELECT * FROM xshares_assets WHERE status = 'active' ORDER BY roi_percent DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', 'Assets loaded.', ['assets' => $rows]);
    } catch (Exception $e) {
        error_log('xshares get_assets error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load assets.');
    }
}


// --------------------- ACTION: get_summary ---------------------
if ($action === 'get_summary') {
    try {
        $stmt = $pdo->prepare("SELECT
            COALESCE(SUM(CASE WHEN status='active' THEN amount END), 0)  AS total_invested,
            COALESCE(SUM(roi_earned), 0)                                  AS total_earned,
            COUNT(CASE WHEN status='active' THEN 1 END)                   AS active_count
            FROM xshares_holdings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        jsonResponse('success', 'X-Shares summary loaded.', [
            'summary' => [
                'total_invested' => round((float)($row['total_invested'] ?? 0), 2),
                'total_earned'   => round((float)($row['total_earned'] ?? 0), 2),
                'active_count'   => (int)($row['active_count'] ?? 0),
            ]
        ]);
    } catch (Exception $e) {
        error_log('xshares get_summary error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load summary.');
    }
}


// --------------------- ACTION: get_active ---------------------
if ($action === 'get_active') {
    try {
        $stmt = $pdo->prepare("SELECT xshares_holdings.*,
                xshares_assets.asset_name, xshares_assets.ticker, xshares_assets.company,
                xshares_assets.current_price, xshares_assets.payout_schedule
            FROM xshares_holdings
            JOIN xshares_assets ON xshares_holdings.asset_id = xshares_assets.id
            WHERE xshares_holdings.user_id = ? AND xshares_holdings.status = 'active'
            ORDER BY xshares_holdings.started_at DESC");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', 'Active X-Shares holdings loaded.', ['holdings' => $rows]);
    } catch (Exception $e) {
        error_log('xshares get_active error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load active holdings.');
    }
}


// --------------------- ACTION: get_matured ---------------------
if ($action === 'get_matured') {
    try {
        $stmt = $pdo->prepare("SELECT xshares_holdings.*,
                xshares_assets.asset_name, xshares_assets.ticker, xshares_assets.company,
                xshares_assets.current_price, xshares_assets.payout_schedule
            FROM xshares_holdings
            JOIN xshares_assets ON xshares_holdings.asset_id = xshares_assets.id
            WHERE xshares_holdings.user_id = ? AND xshares_holdings.status IN ('matured','unlocked')
            ORDER BY xshares_holdings.started_at DESC");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', 'Matured X-Shares holdings loaded.', ['holdings' => $rows]);
    } catch (Exception $e) {
        error_log('xshares get_matured error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load matured holdings.');
    }
}


// --------------------- ACTION: start_xshares ---------------------
if ($action === 'start_xshares') {
    $asset_id = (int) ($input['asset_id'] ?? 0);
    $amount   = (float) ($input['amount'] ?? 0);
    $payout_option = trim($input['payout_option'] ?? 'periodic');

    if ($asset_id <= 0) jsonResponse('error', 'Invalid asset selected.');
    if ($amount <= 0) jsonResponse('error', 'Enter a valid investment amount.');
    if (!in_array($payout_option, ['periodic', 'maturity'], true)) {
        jsonResponse('error', 'Invalid payout option.');
    }

    try {
        // fetch asset
        $astmt = $pdo->prepare("SELECT id, asset_name, ticker, company, current_price, roi_percent, duration_days, min_amount, status FROM xshares_assets WHERE id = ?");
        $astmt->execute([$asset_id]);
        $asset = $astmt->fetch(PDO::FETCH_ASSOC);

        if (!$asset || $asset['status'] !== 'active') {
            jsonResponse('error', 'Asset not found or inactive.');
        }

        $min = (float) $asset['min_amount'];
        if ($amount < $min) {
            jsonResponse('error', "Minimum investment for {$asset['asset_name']} is $$min.");
        }

        $pdo->beginTransaction();

        // lock wallet
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

        $reference = generateReference('TXH-SHR');
        $now = date('Y-m-d H:i:s');
        $duration_days = $asset['duration_days'] !== null ? (int) $asset['duration_days'] : null;
        $maturity_date = $duration_days !== null ? date('Y-m-d', strtotime("+{$duration_days} days")) : null;
        $entry_price = $asset['current_price'] !== null ? (float) $asset['current_price'] : null;

        // Debit wallet, bump trackers
        $pdo->prepare("UPDATE wallets
            SET balance = balance - ?,
                total_investments = total_investments + ?,
                xshares_invested  = xshares_invested  + ?
            WHERE user_id = ?")
            ->execute([$amount, $amount, $amount, $user_id]);

        // Create holding
        $pdo->prepare("INSERT INTO xshares_holdings
            (user_id, asset_id, amount, entry_price, roi_earned, maturity_date, payout_option, status, reference, started_at)
            VALUES (?, ?, ?, ?, 0.00, ?, ?, 'active', ?, ?)")
            ->execute([$user_id, $asset_id, $amount, $entry_price, $maturity_date, $payout_option, $reference, $now]);

        $holding_id = (int) $pdo->lastInsertId();

        // Transaction record
        $details = json_encode([
            'holding_id'    => $holding_id,
            'asset_id'      => (int) $asset['id'],
            'asset_name'    => $asset['asset_name'],
            'ticker'        => $asset['ticker'],
            'payout_option' => $payout_option,
            'kind'          => 'xshares_purchase',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                       VALUES (?, 'investment', 'wallet', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $amount, $reference, $details, $now]);

        $pdo->commit();

        // Emails
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'xshares_started',
                'variables' => [
                    'user_name'     => $user_name,
                    'asset_name'    => $asset['asset_name'],
                    'ticker'        => $asset['ticker'],
                    'company'       => $asset['company'],
                    'amount'        => number_format($amount, 2),
                    'roi_percent'   => (float) $asset['roi_percent'],
                    'payout_option' => $payout_option,
                    'maturity_date' => $maturity_date ? date('M d, Y', strtotime($maturity_date)) : '—',
                    'reference'     => $reference,
                ]
            ]);

            if (defined('ADMIN_CONTACT_EMAIL')) {
                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_xshares_notification',
                    'variables' => [
                        'user_name'  => $user_name,
                        'user_email' => $user_email,
                        'asset_name' => $asset['asset_name'],
                        'ticker'     => $asset['ticker'],
                        'amount'     => number_format($amount, 2),
                        'reference'  => $reference,
                    ]
                ]);
            }
        }

        jsonResponse('success', 'X-Shares holding started successfully.', [
            'holding_id'    => $holding_id,
            'reference'     => $reference,
            'entry_price'   => $entry_price,
            'maturity_date' => $maturity_date ? date('M d, Y', strtotime($maturity_date)) : null,
            'payout_option' => $payout_option,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('xshares start_xshares error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to start X-Shares holding. Please try again.');
    }
}


// --------------------- ACTION: unlock ---------------------
if ($action === 'unlock') {
    $holding_id = (int) ($input['holding_id'] ?? 0);
    if ($holding_id <= 0) jsonResponse('error', 'Invalid holding id.');

    try {
        $pdo->beginTransaction();

        // lock the holding row
        $hstmt = $pdo->prepare("SELECT id, user_id, asset_id, amount, roi_earned, status FROM xshares_holdings WHERE id = ? FOR UPDATE");
        $hstmt->execute([$holding_id]);
        $holding = $hstmt->fetch(PDO::FETCH_ASSOC);

        if (!$holding) {
            $pdo->rollBack();
            jsonResponse('error', 'Holding not found.');
        }
        if ((int)$holding['user_id'] !== $user_id) {
            $pdo->rollBack();
            jsonResponse('error', 'Permission denied.');
        }
        if ($holding['status'] !== 'active') {
            $pdo->rollBack();
            jsonResponse('error', 'Holding is not active.');
        }

        $amount = (float) $holding['amount'];
        $roi_earned = (float) $holding['roi_earned'];
        $total_payout = round($amount + $roi_earned, 2);

        // lock wallet
        $wstmt = $pdo->prepare("SELECT id FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        if (!$wstmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
        }

        // Credit wallet
        $pdo->prepare("UPDATE wallets
            SET balance = balance + ?,
                total_earnings = total_earnings + ?
            WHERE user_id = ?")
            ->execute([$total_payout, $roi_earned, $user_id]);

        // Mark holding unlocked
        $pdo->prepare("UPDATE xshares_holdings SET status = 'unlocked' WHERE id = ?")
            ->execute([$holding_id]);

        $reference = generateReference('TXH-MATURE-SHR');
        $now = date('Y-m-d H:i:s');
        $details = json_encode([
            'holding_id'  => $holding_id,
            'asset_id'    => (int) $holding['asset_id'],
            'principal'   => $amount,
            'roi_earned'  => $roi_earned,
            'kind'        => 'xshares_unlock',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                       VALUES (?, 'maturity', 'wallet', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $total_payout, $reference, $details, $now]);

        $pdo->commit();

        // Email
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'xshares_matured',
                'variables' => [
                    'user_name'  => $user_name,
                    'holding_id' => $holding_id,
                    'principal'  => number_format($amount, 2),
                    'roi_earned' => number_format($roi_earned, 2),
                    'payout'     => number_format($total_payout, 2),
                    'reference'  => $reference,
                ]
            ]);
        }

        jsonResponse('success', 'X-Shares holding unlocked and credited to your wallet.', [
            'holding_id' => $holding_id,
            'principal'  => $amount,
            'roi_earned' => $roi_earned,
            'payout'     => $total_payout,
            'reference'  => $reference,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('xshares unlock error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to unlock holding. Please try again.');
    }
}


// default
http_response_code(400);
jsonResponse('error', 'Invalid action.');
