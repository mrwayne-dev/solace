<?php
// ========================================
// CARD USAGE API — HealthRunCare
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

    // Queries for each project type
    $queries = [
        'charity'        => "SELECT COALESCE(SUM(amount),0) FROM charity_donations WHERE user_id = ?",
        'investment'     => "SELECT COALESCE(SUM(amount),0) FROM investments WHERE user_id = ?",
        'holdlock'       => "SELECT COALESCE(SUM(amount),0) FROM holdlock WHERE user_id = ?",
        'trustfund'      => "SELECT COALESCE(SUM(amount),0) FROM trustfund WHERE user_id = ?",
        'infrastructure' => "SELECT COALESCE(SUM(amount),0) FROM infrastructure_contributions WHERE user_id = ?",
        'maintenance'    => "SELECT COALESCE(SUM(amount),0) FROM maintenance WHERE user_id = ?"
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
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
