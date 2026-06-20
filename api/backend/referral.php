<?php
// ===============================================
// FILE: /api/backend/referral.php
// PURPOSE: Referral Center controller for Solace Mining
// ACTIONS: get_overview, get_history, get_referrals
// Commission is credited at investment time (see investment.php).
// ===============================================

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => false,
    'cookie_samesite' => 'Strict',
]);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/env.php';        // APP_URL
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utilities/helpers.php';    // ensureReferralCode()

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}
$user_id = (int) $_SESSION['user_id'];

try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
$action = trim($input['action'] ?? 'get_overview');

function jsonResponse($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// --------------------- ACTION: get_overview ---------------------
if ($action === 'get_overview') {
    $code = ensureReferralCode($pdo, $user_id);

    $totals = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total_earned, COUNT(*) AS payouts FROM referral_earnings WHERE user_id = ?");
    $totals->execute([$user_id]);
    $t = $totals->fetch(PDO::FETCH_ASSOC);

    $count = $pdo->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_user_id = ?");
    $count->execute([$user_id]);
    $referredCount = (int)$count->fetchColumn();

    $wstmt = $pdo->prepare("SELECT referral_earnings FROM wallets WHERE user_id = ?");
    $wstmt->execute([$user_id]);
    $walletRef = (float)($wstmt->fetchColumn() ?: 0);

    jsonResponse('success', 'Referral overview loaded.', [
        'referral_code' => $code,
        'referral_link' => rtrim(APP_URL, '/') . '/register?ref=' . $code,
        'total_referrals' => $referredCount,
        'total_earnings' => round((float)$t['total_earned'], 2),
        'wallet_referral_earnings' => $walletRef,
        'commission_payouts' => (int)$t['payouts'],
    ]);
}

// --------------------- ACTION: get_history ---------------------
if ($action === 'get_history') {
    $stmt = $pdo->prepare("
        SELECT re.amount, re.commission_percent, re.status, re.reference, re.created_at,
               u.name AS referred_name, u.email AS referred_email
        FROM referral_earnings re
        LEFT JOIN users u ON u.id = re.referred_user_id
        WHERE re.user_id = ?
        ORDER BY re.created_at DESC
        LIMIT 100
    ");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'amount' => (float)$r['amount'],
            'commission_percent' => (float)$r['commission_percent'],
            'status' => $r['status'],
            'reference' => $r['reference'],
            'referred_name' => $r['referred_name'] ?? '—',
            'referred_email' => $r['referred_email'] ?? '—',
            'date' => date('M d, Y', strtotime($r['created_at'])),
        ];
    }
    jsonResponse('success', 'Referral history loaded.', ['history' => $out]);
}

// --------------------- ACTION: get_referrals ---------------------
if ($action === 'get_referrals') {
    $stmt = $pdo->prepare("
        SELECT u.name, u.email, u.created_at,
               COALESCE((SELECT SUM(amount) FROM referral_earnings re WHERE re.referred_user_id = u.id AND re.user_id = ?),0) AS earned
        FROM referrals r
        JOIN users u ON u.id = r.referred_user_id
        WHERE r.referrer_user_id = ?
        ORDER BY u.created_at DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'name' => $r['name'],
            'email' => $r['email'],
            'joined' => date('M d, Y', strtotime($r['created_at'])),
            'earned' => (float)$r['earned'],
        ];
    }
    jsonResponse('success', 'Referrals loaded.', ['referrals' => $out]);
}

http_response_code(400);
jsonResponse('error', 'Invalid action.');
