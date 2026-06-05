<?php
// ========================================
// CARD USAGE API — TitanXHoldings
// Returns user-level distribution of activities
// ========================================

require_once '../../config/database.php';
require_once '../../config/constants.php';
session_start();

header('Content-Type: application/json');

// User authentication check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getPDO(); // ✅ your actual function name

    // Queries for each TitanXHoldings product — keys must match the dashboard chart consumer
    $queries = [
        'investment' => "SELECT COALESCE(SUM(amount),0)         FROM investments WHERE user_id = ?",
        'xlock'      => "SELECT COALESCE(SUM(amount),0)         FROM holdlock WHERE user_id = ?",
        'xweekly'    => "SELECT COALESCE(SUM(total_invested),0) FROM xweekly_programs WHERE user_id = ?",
        'xshares'    => "SELECT COALESCE(SUM(amount),0)         FROM xshares_holdings WHERE user_id = ?",
        'xgrid'      => "SELECT COALESCE(SUM(amount),0)         FROM infrastructure_contributions WHERE user_id = ?",
        'xrewards'   => "SELECT COALESCE(SUM(total_price),0)    FROM xrewards_orders WHERE user_id = ? AND status <> 'cancelled'",
    ];

    $totals = [];
    foreach ($queries as $key => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $totals[$key] = (float) $stmt->fetchColumn();
    }

    // Calculate percentage share
    $grandTotal = array_sum($totals);
    $percentages = [];
    if ($grandTotal > 0) {
        foreach ($totals as $key => $value) {
            $percentages[$key] = round(($value / $grandTotal) * 100, 2);
        }
    } else {
        // No activity yet
        foreach ($totals as $key => $_) {
            $percentages[$key] = 0;
        }
    }

    echo json_encode([
        'success' => true,
        'totals' => $totals,
        'percentages' => $percentages
    ]);

} catch (Exception $e) {
    error_log('card_usage.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Unable to load card usage right now.']);
    exit;
}
