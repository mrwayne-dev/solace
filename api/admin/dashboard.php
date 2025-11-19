<?php
// FILE: /api/admin/dashboard.php
// PURPOSE: Provides global system statistics, metrics, and recent activity for the Admin Dashboard.

// ---------------------------
// Session & Headers
// ---------------------------
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Use safe setting
    'cookie_samesite' => 'Strict',
]);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// ---------------------------
// Include dependencies
// ---------------------------
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';

// ---------------------------
// **ADMIN** Auth Check
// ---------------------------
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Admin Unauthorized. Please log in.']);
    exit;
}

// ---------------------------
// Initialize DB connection
// ---------------------------
try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    error_log("Admin Dashboard DB Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// ---------------------------
// 1. Fetch Global Metrics for Cards & Alerts
// ---------------------------
$totalRevenue = 0.00;
$totalDonations = 0.00;
$activeInvestmentsCount = 0;
$totalUsers = 0;
$pendingDeposits = 0;
$pendingWithdrawals = 0;
$totalInvestedAmount = 0.00; 

try {
    // Metric 1: Total Revenue (all deposits into wallets)
    $totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_deposited), 0) FROM wallets")->fetchColumn();

    // Metric 2: Total Donations (sum of all donations made)
    $totalDonations = $pdo->query("SELECT COALESCE(SUM(total_donations), 0) FROM wallets")->fetchColumn();
    
    // Metric 3: Active Investments Count
    $activeInvestmentsCount = $pdo->query("SELECT COUNT(id) FROM investments WHERE status = 'active'")->fetchColumn();

    // Metric 4: Total Active Users
    $totalUsers = $pdo->query("SELECT COUNT(id) FROM users WHERE status = 'active'")->fetchColumn();

    // Pending Alerts: Deposits & Withdrawals (Count)
    $pendingDeposits = $pdo->query("SELECT COUNT(id) FROM transactions WHERE type = 'deposit' AND status = 'pending'")->fetchColumn();
    $pendingWithdrawals = $pdo->query("SELECT COUNT(id) FROM transactions WHERE type = 'withdraw' AND status = 'pending'")->fetchColumn();
    
    // Total Invested amount (used for chart comparison)
    $totalInvestedAmount = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM investments WHERE status = 'active'")->fetchColumn();
    
} catch (Exception $e) {
    error_log("Admin Metric Query Error: " . $e->getMessage());
}

// ---------------------------
// 2. Fetch Recent Transactions for the Table
// ---------------------------
$recentTransactions = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            t.type, t.amount, t.status, u.full_name, t.created_at
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
        LIMIT 8
    ");
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($transactions as $txn) {
        $recentTransactions[] = [
            'date' => date('M d, Y', strtotime($txn['created_at'])),
            'user' => htmlspecialchars($txn['full_name'] ?? 'System User'),
            // FIX: Format type to 'Capitalized First Letter'
            'type' => ucfirst(strtolower(htmlspecialchars($txn['type'] ?? 'N/A'))), 
            'amount' => (float)$txn['amount'],
            'status' => htmlspecialchars($txn['status'] ?? 'N/A'),
        ];
    }
} catch (Exception $e) {
    error_log("Admin Recent TXN Query Error: " . $e->getMessage());
}

// ---------------------------
// 3. Calculate Chart Ratios (Distribution of monetary values)
// ---------------------------
$chartSources = [
    'revenue_raw' => (float)$totalRevenue,
    'donations_raw' => (float)$totalDonations,
    'investments_raw' => (float)$totalInvestedAmount,
];

$chartMonetaryTotal = $chartSources['revenue_raw'] + $chartSources['donations_raw'] + $chartSources['investments_raw'];

$chartPercentages = [
    'revenue' => 0.0,
    'donations' => 0.0,
    'investments' => 0.0,
    'users' => 0.0,
];

if ($chartMonetaryTotal > 0) {
    $chartPercentages['revenue'] = round(($chartSources['revenue_raw'] / $chartMonetaryTotal) * 100, 1);
    $chartPercentages['donations'] = round(($chartSources['donations_raw'] / $chartMonetaryTotal) * 100, 1);
    $chartPercentages['investments'] = round(($chartSources['investments_raw'] / $chartMonetaryTotal) * 100, 1);
    
    // Assign remaining percentage to users/placeholder slice for a full 100% chart visualization
    $monetarySum = $chartPercentages['revenue'] + $chartPercentages['donations'] + $chartPercentages['investments'];
    $chartPercentages['users'] = round(max(0, 100 - $monetarySum), 1);
} else {
    // If no data, split for visualization or assign all to users
    $chartPercentages['users'] = 100.0;
}


// ---------------------------
// RESPONSE FORMAT
// ---------------------------
echo json_encode([
    'status' => 'success',
    'data' => [
        'metrics' => [
            'total_revenue' => (float)$totalRevenue,
            'total_donations' => (float)$totalDonations,
            'active_investments' => (int)$activeInvestmentsCount,
            'total_users' => (int)$totalUsers,
        ],
        'pending_alerts' => [
            'deposits' => (int)$pendingDeposits,
            'withdrawals' => (int)$pendingWithdrawals,
        ],
        'recent_activity' => $recentTransactions,
        'chart_data' => $chartPercentages,
    ],
]);
exit;
?>