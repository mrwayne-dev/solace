<?php
require_once("../../config/database.php");
header('Content-Type: application/json');

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
