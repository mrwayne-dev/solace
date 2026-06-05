<?php
require_once("../../config/database.php");
require_once("../../api/utilities/helpers.php");
session_start();
header('Content-Type: application/json');

// Admin Auth Check — exposes all users' pending withdrawals (PII + amounts).
if (!isset($_SESSION["admin_id"])) {
    logSecurityEvent('unauthorized_admin_access', ['endpoint' => 'get_pending_withdrawals', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '']);
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

try {
    $pdo = getPDO();
$stmt = $pdo->prepare("
    SELECT t.id, u.full_name as user, t.amount, 
    DATE_FORMAT(t.created_at, '%Y-%m-%d %H:%i') as date
    FROM transactions t 
    JOIN users u ON t.user_id = u.id
    WHERE t.type = 'withdraw' 
    AND t.status = 'pending'
    ORDER BY t.created_at DESC
");
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Could not fetch withdrawals'
    ]);
}
