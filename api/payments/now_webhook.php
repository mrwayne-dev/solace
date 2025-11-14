<?php
// ===============================================
// FILE: /api/payments/now_webhook.php
// PURPOSE: NOWPayments IPN webhook. Verifies signature,
//          updates transactions, credits wallet, and
//          sends email notifications.
// NOTES:
//  - NOWPayments includes a header 'x-nowpayments-sig'.
//  - Signature algorithm: HMAC-SHA512 on JSON.stringify(params, Object.keys(params).sort())
//  - This script implements canonical JSON encoding (sorted keys) before HMAC.
// ===============================================

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../backend/email.php'; // sendEmail() + templates

// Ensure we always return JSON when appropriate
header('Content-Type: application/json');

// Helper: read raw POST body
$rawBody = file_get_contents('php://input');

// Grab signature header (NOWPayments uses 'x-nowpayments-sig')
$signatureHeader = '';
foreach (getallheaders() as $k => $v) {
    if (strtolower($k) === 'x-nowpayments-sig') {
        $signatureHeader = trim($v);
        break;
    }
}

// Basic guard: require body + signature
if (empty($rawBody) || empty($signatureHeader)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing payload or signature']);
    exit;
}

// Decode incoming JSON to associative array for processing
$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    // invalid JSON
    http_response_code(400);
    error_log('NOWWebhook: Invalid JSON received: ' . $rawBody);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
    exit;
}

/**
 * Canonical JSON generator:
 * - Sorts object keys recursively (like JS Object.keys(...).sort())
 * - Encodes scalars in JSON-safe format
 * This function reproduces the exact stable string used for signing by NOWPayments.
 */
function canonicalJson($data) {
    // For associative arrays / objects: sort keys
    if (is_array($data)) {
        // Check if array is associative
        $isAssoc = array_keys($data) !== range(0, count($data) - 1);

        if ($isAssoc) {
            $keys = array_keys($data);
            sort($keys, SORT_STRING);
            $parts = [];
            foreach ($keys as $k) {
                $parts[] = json_encode((string)$k, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) 
                         . ':' . canonicalJson($data[$k]);
            }
            return '{' . implode(',', $parts) . '}';
        } else {
            // Indexed array — preserve order
            $parts = [];
            foreach ($data as $item) {
                $parts[] = canonicalJson($item);
            }
            return '[' . implode(',', $parts) . ']';
        }
    } else {
        // Scalar: use json_encode for correct escaping of strings, booleans, numbers
        // Force consistent JSON encoding flags
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

// Recreate canonical JSON string from parsed payload
$canonical = canonicalJson($payload);

// Compute HMAC-SHA512 using your IPN secret
$expectedSig = hash_hmac('sha512', $canonical, NOWPAY_IPN_SECRET);

// Compare signatures in a timing-safe manner
if (!hash_equals($expectedSig, $signatureHeader)) {
    // Signature mismatch -> log and reject
    error_log("NOWWebhook: Signature verification failed. Expected: {$expectedSig} Received: {$signatureHeader}");
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// At this point the request is verified. Proceed to handle the payload.
try {
    $pdo = getPDO();
} catch (Exception $e) {
    error_log('NOWWebhook: DB connection error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB connection error']);
    exit;
}

/**
 * Determine payment status in payload.
 * NOWPayments commonly uses 'payment_status' or 'status' keys; fall back defensively.
 */
$paymentStatus = $payload['payment_status'] ?? $payload['status'] ?? null;
$orderId = $payload['order_id'] ?? $payload['orderId'] ?? $payload['orderId'] ?? null; // our 'reference'
$providerPaymentId = $payload['payment_id'] ?? $payload['invoice_id'] ?? $payload['id'] ?? null;
$paidAmount = $payload['price_amount'] ?? $payload['amount'] ?? null; // amount in fiat (if provided)

/* Sanity checks */
if (empty($orderId)) {
    error_log('NOWWebhook: Missing order_id in IPN payload: ' . json_encode($payload));
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing order_id']);
    exit;
}

// Fetch corresponding transaction
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE reference = ? LIMIT 1");
$stmt->execute([$orderId]);
$txn = $stmt->fetch();

if (!$txn) {
    // no transaction — might be created differently. Log and return 404.
    error_log("NOWWebhook: Transaction not found for reference {$orderId}");
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit;
}

// Idempotency: if transaction already completed, return OK.
if (strtolower($txn['status']) === 'completed') {
    // Update provider info optionally, but do not credit again.
    error_log("NOWWebhook: Transaction {$orderId} already completed — ignoring duplicate IPN.");
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Already processed']);
    exit;
}

// Prepare to update transaction details and possibly credit wallet
$existingDetails = json_decode($txn['details'] ?? '{}', true);
if (!is_array($existingDetails)) $existingDetails = [];

$existingDetails['provider'] = 'nowpayments';
$existingDetails['provider_payload'] = $payload;
$existingDetails['provider_payment_id'] = $providerPaymentId ?? ($existingDetails['provider_payment_id'] ?? null);
$existingDetails['ipn_received_at'] = date('Y-m-d H:i:s');

$shouldComplete = false;

// Determine if payment status represents a successful final state.
// NOWPayments uses 'finished' as final success in many cases; include common variants.
$successStatuses = ['finished', 'confirmed', 'successful', 'paid', 'payment_received'];
if ($paymentStatus !== null) {
    $paymentStatusNormalized = strtolower((string)$paymentStatus);
    if (in_array($paymentStatusNormalized, $successStatuses, true)) {
        $shouldComplete = true;
    }
} else {
    // Fallback: sometimes payload includes 'payment_status' nested or uses 'pay_amount' -> assume success if provider says 'is_paid' or similar
    if (!empty($payload['is_paid']) || !empty($payload['paid'])) {
        $shouldComplete = true;
    }
}

// If the payload includes an amount, we can use it to be safe. Otherwise fall back to txn.amount.
$amountToCredit = $txn['amount'];
if (!empty($paidAmount) && is_numeric($paidAmount)) {
    // sometimes price_amount is string — cast to float
    $amountToCredit = (float)$paidAmount;
}

// If it's a successful payment, mark transaction completed, credit wallet and notify user
if ($shouldComplete) {
    try {
        $pdo->beginTransaction();

        // 1) Update transactions: set status = completed, attach details
        $upd = $pdo->prepare("UPDATE transactions SET status = 'completed', details = ? WHERE id = ?");
        $existingDetails['completed_at'] = date('Y-m-d H:i:s');
        $existingDetails['provider_final_status'] = $paymentStatus;
        $upd->execute([json_encode($existingDetails), $txn['id']]);

        // 2) Credit the user's wallet
        // - Increase balance
        // - Increase total_deposited
        // Using single UPDATE to avoid race conditions
        $updateWallet = $pdo->prepare("UPDATE wallets 
            SET balance = balance + ?, total_deposited = total_deposited + ? 
            WHERE user_id = ?");
        $updateWallet->execute([$amountToCredit, $amountToCredit, $txn['user_id']]);

        // 3) Optionally update transactions row details further if needed (already done)
        // 4) Commit
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('NOWWebhook: Failed to credit wallet: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to process payment']);
        exit;
    }

    // Send Deposit Confirmed email to user
    // Fetch user info to populate email template (email may not be present in txn row)
    $userStmt = $pdo->prepare("SELECT id, name, full_name, email FROM users WHERE id = ? LIMIT 1");
    $userStmt->execute([$txn['user_id']]);
    $user = $userStmt->fetch();

    $userEmail = $user['email'] ?? ($existingDetails['buyer_email'] ?? null);
    $userName = $user['full_name'] ?? $user['name'] ?? 'User';

    if (!empty($userEmail)) {
        // send deposit_confirmed template
        sendEmail([
            'to' => $userEmail,
            'template' => 'deposit_confirmed',
            'variables' => [
                'user_name' => $userName,
                'amount' => number_format($amountToCredit, 2),
                'reference' => $orderId
            ]
        ]);
    } else {
        error_log("NOWWebhook: No user email to send confirmation for txn {$orderId}");
    }

    // Also notify admin optionally
    sendEmail([
        'to' => ADMIN_CONTACT_EMAIL,
        'template' => 'admin_payment_confirmed',
        'variables' => [
            'user_name' => $userName,
            'user_email' => $userEmail ?? 'N/A',
            'amount' => number_format($amountToCredit, 2),
            'method' => 'secure_exchange',
            'reference' => $orderId,
            'details' => 'Auto-confirmed via NOWPayments IPN.'
        ]
    ]);

    // Success response for NOWPayments
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Payment processed and wallet credited']);
    exit;
}

// If not a final success state, update transaction details and keep pending
try {
    $upd = $pdo->prepare("UPDATE transactions SET details = ? WHERE id = ?");
    $existingDetails['last_ipn_status'] = $paymentStatus;
    $upd->execute([json_encode($existingDetails), $txn['id']]);
} catch (Exception $e) {
    error_log('NOWWebhook: Failed to update transaction details for non-final status: ' . $e->getMessage());
}

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'IPN received (not completed)']);
exit;
