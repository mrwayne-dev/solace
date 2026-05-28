<?php
/**
 * FILE: C:\mrwayne\web_dev\healthruncare\api\admin\process_deposit.php
 * PURPOSE: Handles the admin action to either complete or cancel a pending deposit,
 * including database updates and sending the relevant email notification.
 */

require_once("../../config/database.php"); 
require_once("../../api/utilities/email_temps.php"); 
require_once("../backend/email.php"); // Contains the sendEmail function

session_start();
header('Content-Type: application/json; charset=utf-8');

// 1. Admin Auth Check
if(!isset($_SESSION["admin_id"])){
    http_response_code(401);
    echo json_encode(["status"=>"error","message"=>"Unauthorized access"]);
    exit;
}

// 2. Input Validation
$data = json_decode(file_get_contents("php://input"), true);
$transaction_id = $data["id"] ?? null;
$action = $data["action"] ?? null; // 'complete' or 'cancel'
$reason = $data["reason"] ?? "No specific reason provided."; // Reason for cancellation

if (!$transaction_id || !in_array($action, ['complete', 'cancel'])) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Invalid action or transaction ID."]);
    exit;
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // 3. Fetch Transaction Details
    $stmt = $pdo->prepare("
        SELECT 
            t.user_id, t.amount, t.status, t.reference,
            u.full_name, u.email,
            w.balance, w.total_deposited 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN wallets w ON t.user_id = w.user_id
        WHERE t.id = :id AND t.type = 'deposit' AND t.status = 'pending'
    ");
    $stmt->execute([':id' => $transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        http_response_code(404);
        echo json_encode(["status"=>"error","message"=>"Pending deposit not found or already processed."]);
        $pdo->rollBack();
        exit;
    }

    $userId = $transaction['user_id'];
    $amount = (float)$transaction['amount'];
    $currentBalance = (float)$transaction['balance'];
    $totalDeposited = (float)$transaction['total_deposited'];
    $userEmail = $transaction['email'];
    $userName = $transaction['full_name'];
    $reference = $transaction['reference'];

    $newStatus = $action === 'complete' ? 'completed' : 'failed'; 

    // 4. Update Database
    // A. Update the transaction status
    $stmt = $pdo->prepare("UPDATE transactions SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $newStatus, ':id' => $transaction_id]);

    if ($action === 'complete') {
        // B. Update Wallet only if completed
        $newBalance = $currentBalance + $amount;
        $newTotalDeposited = $totalDeposited + $amount;
        
        $stmt = $pdo->prepare("
            UPDATE wallets 
            SET balance = :balance, total_deposited = :total_deposited 
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':balance' => $newBalance,
            ':total_deposited' => $newTotalDeposited,
            ':user_id' => $userId
        ]);
        
        // 💡 FIX: Set to 'deposit_confirmed' to match the desired template content.
        $emailTemplate = 'deposit_confirmed'; 
        $message = "Deposit of \$$amount has been successfully completed and credited to the user's wallet.";

    } else { // 'cancel' action
        $emailTemplate = 'deposit_cancelled';
        $message = "Deposit request for \$$amount has been cancelled.";
    }

$emailPlaceholders = [
    'user_name' => $userName,
    'amount' => number_format($amount, 2),
    'reference' => $reference,
];
if ($action === 'cancel') {
    $emailPlaceholders['reason'] = htmlspecialchars($reason);
}

    
    $emailTemplates = getEmailTemplates(); 
    
    $emailSuccess = sendEmail([
        'to' => $userEmail, 
        'template' => $emailTemplate, 
        'variables' => $emailPlaceholders
    ]);
    
    $pdo->commit();

    // 6. Final Response
    // Checks if the email failed to send (handles both boolean false and error array)
    $failed = ($emailSuccess === false) || (is_array($emailSuccess) && !$emailSuccess['success']);
    
    if ($failed) {
        $errorDetails = is_array($emailSuccess) ? ($emailSuccess['error'] ?? 'Unknown') : 'Returned boolean false';
        error_log("CRITICAL: Failed to send $action email to user $userId. Mailer Error: " . $errorDetails);
        $message .= " (Warning: Email notification failed to send.)";
    }

    echo json_encode(["status"=>"success", "message"=>$message]);

} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    error_log("Deposit Process Error: " . $e->getMessage());
    echo json_encode(["status"=>"error", "message"=>"Transaction failed: A server error occurred."]); 
}
?>