<?php
// ========================================
// CARD USAGE API — Solace Mining
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
    $pdo = getPDO();

    // Distribution buckets — keys must match the dashboard chart consumer
    // (available balance vs. active mining contracts vs. referral earnings)
    $totals = ['balance' => 0.0, 'invested' => 0.0, 'referral' => 0.0];

    $stmt = $pdo->prepare("SELECT balance, referral_earnings FROM wallets WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['balance' => 0, 'referral_earnings' => 0];
    $totals['balance']  = (float)$w['balance'];
    $totals['referral'] = (float)$w['referral_earnings'];

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM investments WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $totals['invested'] = (float)$stmt->fetchColumn();

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
