<?php
// ===============================================
// FILE: /api/backend/dashboard.php
// PURPOSE: Provides user dashboard data — wallet stats,
// impacts summary, and recent transactions.
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
                INSERT INTO wallets (user_id, balance, total_deposited, total_withdrawn, total_donations, total_investments, holdlock_savings, total_earnings, pending_withdrawals)
                VALUES (?, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00)
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
        INSERT INTO wallets (user_id, balance, total_deposited, total_withdrawn, total_donations, total_investments, holdlock_savings, total_earnings, pending_withdrawals)
        VALUES (?, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00)
    ")->execute([$user_id]);
    $stmt->execute([$user_id]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
}
// ===========================================================
// DYNAMIC HOLDLOCK SAVINGS CHECK (syncs dashboard with real lock plans)
// ===========================================================
$lockStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS total_locked
    FROM holdlock
    WHERE user_id = ? AND status IN ('locked','unlock_pending')
");
$lockStmt->execute([$user_id]);
$totalLocked = (float)$lockStmt->fetchColumn();

// Update wallet holdlock_savings if not up-to-date
if (abs($totalLocked - (float)$wallet['holdlock_savings']) > 0.01) {
    $pdo->prepare("UPDATE wallets SET holdlock_savings = ? WHERE user_id = ?")
        ->execute([$totalLocked, $user_id]);
    $wallet['holdlock_savings'] = $totalLocked;
}
// ===========================================================
// FETCH USER IMPACT DATA
// ===========================================================
$stmt = $pdo->prepare("SELECT * FROM user_impacts WHERE user_id = ?");
$stmt->execute([$user_id]);
$impact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$impact) {
    $pdo->prepare("
        INSERT INTO user_impacts (user_id, total_contributions, people_helped, impact_score, communities_helped, packages_funded)
        VALUES (?, 0.00, 0, 0.00, 0, 0)
    ")->execute([$user_id]);
    $stmt->execute([$user_id]);
    $impact = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ===========================================================
// UPDATE IMPACT (if wallet contributions changed)
// ===========================================================
$total_contributions = (float)$wallet['total_donations'] + (float)$wallet['total_investments'] + (float)$wallet['holdlock_savings'];
if (abs((float)$impact['total_contributions'] - $total_contributions) > 0.01) {
    $pdo->prepare("UPDATE user_impacts SET total_contributions = ? WHERE user_id = ?")
        ->execute([$total_contributions, $user_id]);
    $impact['total_contributions'] = $total_contributions;
}

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
            'donations' => (float)$wallet['total_donations'],
            'investments' => (float)$wallet['total_investments'],
            'holdlock_savings' => (float)$wallet['holdlock_savings'],
            'total_earnings' => (float)$wallet['total_earnings'],
            'pending_withdrawals' => $pendingWithdrawalCount, // now counts requests
        ],
        'impacts' => [
            'total_contributions' => (float)$impact['total_contributions'],
            'people_helped' => (int)$impact['people_helped'],
            'impact_score' => (float)$impact['impact_score'],
            'communities_helped' => (int)$impact['communities_helped'],
            'packages_funded' => (int)$impact['packages_funded'],
        ],
        'recent_activity' => $recent_activity,
    ],
]);
exit;
?>
