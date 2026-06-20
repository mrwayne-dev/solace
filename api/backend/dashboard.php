<?php
ini_set('display_errors', 0);
error_reporting(0);
// ===============================================
// FILE: /api/backend/dashboard.php
// PURPOSE: Provides user dashboard data — wallet stats,
// active contracts summary, and recent transactions.
// Supports SPA dashboard requests (via fetch or AJAX).
// ===============================================
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set true on HTTPS
    'cookie_samesite' => 'Strict',
]);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Restrict for production
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ---------------------------
// Include dependencies
// ---------------------------
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';

// ---------------------------
// Auth check
// ---------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ---------------------------
// Initialize DB connection
// ---------------------------
try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// ---------------------------
// Parse input (optional action param)
// ---------------------------
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? 'get_data';

// ===========================================================
// ACTION: GET WALLET (simple standalone for external modules)
// ===========================================================
if ($action === 'get_wallet') {
    try {
        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            // Auto-create wallet if not found
            $pdo->prepare("
                INSERT INTO wallets (user_id, balance, total_deposited, total_withdrawn, total_investments, total_earnings, referral_earnings, pending_withdrawals)
                VALUES (?, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00)
            ")->execute([$user_id]);
            $wallet = ['balance' => 0.00];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'wallet' => ['balance' => (float)$wallet['balance']]
            ]
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error fetching wallet.']);
        exit;
    }
}

// ===========================================================
// ACTION: GET DATA (main dashboard payload)
// ===========================================================
if ($action !== 'get_data') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    exit;
}

// ===========================================================
// FETCH WALLET DATA
// ===========================================================
$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wallet) {
    // Create a default wallet if none exists
    $pdo->prepare("
        INSERT INTO wallets (user_id, balance, total_deposited, total_withdrawn, total_investments, total_earnings, referral_earnings, pending_withdrawals)
        VALUES (?, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00)
    ")->execute([$user_id]);
    $stmt->execute([$user_id]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
}
// ===========================================================
// ACTIVE MINING CONTRACTS SUMMARY
// ===========================================================
$invStmt = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN status='active' THEN amount ELSE 0 END),0) AS active_value,
           COUNT(CASE WHEN status='active' THEN 1 END) AS active_count
    FROM investments WHERE user_id = ?
");
$invStmt->execute([$user_id]);
$inv = $invStmt->fetch(PDO::FETCH_ASSOC) ?: ['active_value' => 0, 'active_count' => 0];

// ===========================================================
// COUNT PENDING WITHDRAWALS
// ===========================================================
$countStmt = $pdo->prepare("SELECT COUNT(*) AS count FROM transactions WHERE user_id = ? AND type = 'withdraw' AND status = 'pending'");
$countStmt->execute([$user_id]);
$pendingWithdrawalCount = (int)$countStmt->fetchColumn();

// ===========================================================
// FETCH RECENT TRANSACTIONS
// ===========================================================
$stmt = $pdo->prepare("
    SELECT type, method, amount, status, reference, created_at
    FROM transactions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 8
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$recent_activity = [];
foreach ($transactions as $txn) {
    $recent_activity[] = [
        'type' => ucfirst($txn['type']),
        'method' => ucfirst(str_replace('_', ' ', $txn['method'] ?? '')),
        'amount' => (float)$txn['amount'],        'status' => ucfirst($txn['status']),
        'reference' => $txn['reference'],
        'date' => date('M d, Y', strtotime($txn['created_at'])),
    ];
}

// ===========================================================
// RESPONSE FORMAT
// ===========================================================
echo json_encode([
    'status' => 'success',
    'data' => [
        'wallet' => [
            'balance' => (float)$wallet['balance'],
            'total_deposited' => (float)$wallet['total_deposited'],
            'total_withdrawn' => (float)$wallet['total_withdrawn'],
            'investments' => (float)$wallet['total_investments'],
            'total_earnings' => (float)$wallet['total_earnings'],
            'referral_earnings' => (float)$wallet['referral_earnings'],
            'pending_withdrawals' => $pendingWithdrawalCount, // now counts requests
        ],
        'contracts' => [
            'active_value' => (float)$inv['active_value'],
            'active_count' => (int)$inv['active_count'],
        ],
        'recent_activity' => $recent_activity,
    ],
]);
exit;
?>
