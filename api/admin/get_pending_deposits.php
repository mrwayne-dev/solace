<?php
// FILE: /api/admin/get_pending_deposits.php

require_once("../../config/database.php");
session_start();

// 1. Admin Auth Check
if(!isset($_SESSION["admin_id"])){
    http_response_code(401);
    echo json_encode(["status"=>"error","message"=>"Unauthorized access"]);
    exit;
}

try {
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT
            t.id,
            t.reference,
            t.amount,
            t.details,
            t.created_at,
            u.full_name,
            u.email
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE t.type = 'deposit' AND t.status = 'pending'
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedDeposits = [];
    foreach($deposits as $dep) {
        $d = json_decode($dep['details'] ?? '{}', true) ?: [];
        $formattedDeposits[] = [
            'id' => (int)$dep['id'],
            'reference' => htmlspecialchars($dep['reference']),
            'amount' => (float)$dep['amount'],
            'date' => date('M d, Y H:i', strtotime($dep['created_at'])),
            'user' => htmlspecialchars($dep['full_name']) . ' (' . htmlspecialchars($dep['email']) . ')',
            // Manual-deposit review fields
            'network' => htmlspecialchars($d['network'] ?? '—'),
            'address' => htmlspecialchars($d['address'] ?? '—'),
            'proof_url' => $d['proof_url'] ?? null,
        ];
    }

    echo json_encode(["status"=>"success", "data"=>$formattedDeposits]);

} catch(Exception $e) {
    error_log("Deposit Fetch Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status"=>"error", "message"=>"Server error: Could not fetch deposits."]);
}
?>