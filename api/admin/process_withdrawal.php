<?php
/**
 * ============================================================
 * Solace Mining — PROCESS WITHDRAWAL ACTION (ADMIN)
 * ============================================================
 * POST: id, action (complete|cancel), [reason]
 * Actions:
 *  - complete = approve and send money (deduction already made)
 *  - cancel = return money to user wallet + notify user
 * Emails: withdrawal_approved, withdrawal_declined
 * ============================================================
 */

require_once("../../config/database.php");
require_once("../../api/utilities/email_temps.php"); 
require_once("../backend/email.php"); // Contains the sendEmail function
require_once("../../api/utilities/helpers.php");

session_start();
header('Content-Type: application/json');

// Admin Auth Check — this endpoint approves/cancels withdrawals and credits wallets.
if (!isset($_SESSION["admin_id"])) {
    logSecurityEvent('unauthorized_admin_access', ['endpoint' => 'process_withdrawal', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '']);
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Allow both JSON and form POST
$input = json_decode(file_get_contents("php://input"), true);

$id     = intval($input['id'] ?? ($_POST['id'] ?? 0));
$action = $input['action'] ?? ($_POST['action'] ?? '');
$reason = trim($input['reason'] ?? ($_POST['reason'] ?? ''));

if (!$id || !in_array($action, ['complete', 'cancel'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // Fetch withdrawal
    $stmt = $pdo->prepare("
        SELECT t.*, u.full_name, u.email 
        FROM transactions t
        JOIN users u ON u.id = t.user_id
        WHERE t.id = ? AND t.type = 'withdraw' AND t.status = 'pending'
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $txn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$txn) {
        throw new Exception("Withdrawal not found or already processed.");
    }

    $userId  = $txn['user_id'];
    $amount  = floatval($txn['amount']);
    $userEmail = $txn['email'];
    $userName  = $txn['full_name'];
    $reference = $txn['reference'];

    // --------------------------
    // COMPLETE WITHDRAWAL
    // --------------------------
    if ($action === 'complete') {

        $update = $pdo->prepare("UPDATE transactions SET status='completed' WHERE id=? LIMIT 1");
        $update->execute([$id]);

        // Email Notification
        sendEmail([
            'to' => $userEmail,
            'template' => 'withdrawal_approved',
            'variables' => [
                'user_name' => $userName,
                'amount'    => number_format($amount, 2),
                'method'    => $txn['method'],
                'reference' => $reference
            ]
        ]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Withdrawal completed']);
        exit;
    }

// --------------------------
// CANCEL WITHDRAWAL
// --------------------------
if ($action === 'cancel') {

    // 1. Return funds to wallet + reduce pending
    $wallet = $pdo->prepare("
        UPDATE wallets 
        SET balance = balance + ?, 
            pending_withdrawals = pending_withdrawals - ?
        WHERE user_id=?
    ");
    $wallet->execute([$amount, $amount, $userId]);

    // 2. Update transaction
    $update = $pdo->prepare("UPDATE transactions SET status='failed' WHERE id=? LIMIT 1");
    $update->execute([$id]);

sendEmail([
    'to' => $userEmail,
    'template' => 'withdrawal_declined',
    'variables' => [
        'user_name' => $userName,
        'amount'    => number_format($amount, 2),
        'reference' => $reference,
        'reason'    => $reason  
    ]
]);


    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Withdrawal cancelled and funds returned']);
    exit;
}


} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('process_withdrawal.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Could not process the withdrawal. Please try again.']);
}
