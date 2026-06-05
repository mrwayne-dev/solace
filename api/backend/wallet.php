<?php
ini_set('display_errors', 0);
error_reporting(0);
    // ===============================================
    // FILE: /api/backend/wallet.php
    // PURPOSE: Central wallet controller for TitanXHoldings
    // DESCRIPTION:
    // Handles all wallet actions — deposits, withdrawals,
    // confirmations, and pending data retrieval.
    // Integrates with NOWPayments for crypto deposits,
    // updates wallet balances, and triggers notification emails.
    // ===============================================

    session_start();
    header('Content-Type: application/json');

    // ---------------------------
    // Security: Ensure user is logged in
    // ---------------------------
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    // ---------------------------
    // Includes
    // ---------------------------
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/env.php';        // must precede constants.php so .env APP_URL wins
    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/email.php';

    $pdo = getPDO();
    $user_id = (int) $_SESSION['user_id'];
    $user_name = $_SESSION['full_name'] ?? ($_SESSION['name'] ?? 'User');
    $user_email = $_SESSION['email'] ?? '';

    // ---------------------------
    // Parse incoming request
    // Supports: form POST, GET, and JSON fetch()
    // ---------------------------
    $parsedJsonBody = null;
    $action = null;

    // 1️⃣ Form POST
    if (isset($_POST['action']) && $_POST['action'] !== '') {
        $action = trim($_POST['action']);
    }

    // 2️⃣ GET param
    if (!$action && isset($_GET['action']) && $_GET['action'] !== '') {
        $action = trim($_GET['action']);
    }

    // 3️⃣ JSON body (fetch API)
    if (!$action) {
        $raw = @file_get_contents('php://input');
        if ($raw) {
            $json = @json_decode($raw, true);
            if (is_array($json)) {
                $parsedJsonBody = $json;
                if (!empty($json['action'])) {
                    $action = trim((string)$json['action']);
                }
            }
        }
    }

    $action = $action ?? null;

    // ---------------------------
    // Helper: respond + exit
    // ---------------------------
    function jsonResponse($status, $message, $data = []) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }

    // ---------------------------
    // Helper: reference & wallet utilities
    // ---------------------------
    function generateReference($prefix) {
        return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
    }

    function getUserWallet($pdo, $uid) {
        $stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
        $stmt->execute([$uid]);
        return $stmt->fetch();
    }

    function updateWalletBalance($pdo, $uid, $amount, $type = 'add') {
        $sql = ($type === 'add')
            ? "UPDATE wallets SET balance = balance + ? WHERE user_id = ?"
            : "UPDATE wallets SET balance = balance - ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$amount, $uid]);
    }

    // Optional: local debug log
    function logDebug($msg) {
        $logPath = __DIR__ . '/../../logs/wallet_debug.log';
        if (!is_dir(dirname($logPath))) mkdir(dirname($logPath), 0777, true);
        @file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
    }

    // ===========================================================
    // ACTION ROUTER
    // ===========================================================
    try {
        switch ($action) {

            // -------------------------------------------------------
            // 1️⃣ INITIATE DEPOSIT
            // -------------------------------------------------------
            case 'initiate_deposit':
                $data = $parsedJsonBody ?? (json_decode(file_get_contents('php://input'), true) ?: []);
                $amount = (float) ($data['amount'] ?? 0);
                $method = strtolower(trim((string)($data['method'] ?? '')));

                if ($amount <= 0 || !$method) {
                    jsonResponse('error', 'Invalid deposit details provided.');
                }

                $reference = generateReference('TXH-DEP');
                $timestamp = date('Y-m-d H:i:s');
                $details = json_encode(['initiated_at' => $timestamp, 'method' => $method]);

                $insert = $pdo->prepare("
                    INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                    VALUES (?, 'deposit', ?, ?, ?, 'pending', ?, ?)
                ");
                $insert->execute([$user_id, $method, $amount, $reference, $details, $timestamp]);

                // 🔹 Secure Exchange (NOWPayments)
                if ($method === 'secure_exchange') {
                    require_once __DIR__ . '/../payments/create_crypto_payment.php';
                    $response = createCryptoPayment($user_id, $user_email, $amount, $reference);

                    if (!is_array($response) || ($response['status'] ?? '') !== 'success') {
                        $errMsg = $response['message'] ?? 'Failed to create crypto payment. Please try again later.';
                        logDebug('NOWPayments error: ' . json_encode($response));
                        jsonResponse('error', $errMsg, $response['data'] ?? []);
                    }

                    $paymentUrl = $response['data']['payment_url'] ?? $response['data']['invoice_url'] ?? null;
                    if (!$paymentUrl) {
                        jsonResponse('error', 'Payment provider did not return a redirect URL.', $response);
                    }

                    jsonResponse('success', 'Redirecting to crypto payment...', [
                        'redirect_url' => $paymentUrl,
                        'reference' => $reference
                    ]);
                }

                // 🔹 Manual deposits (wire / cash)
                sendEmail([
                    'to' => $user_email,
                    'template' => 'deposit_initiated',
                    'variables' => [
                        'user_name' => $user_name,
                        'amount' => number_format($amount, 2),
                        'method' => ucfirst(str_replace('_', ' ', $method)),
                        'reference' => $reference
                    ]
                ]);

                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_deposit_notification',
                    'variables' => [
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'amount' => number_format($amount, 2),
                        'method' => ucfirst($method),
                        'reference' => $reference
                    ]
                ]);

                jsonResponse('success', 'Deposit request initiated successfully.', ['reference' => $reference]);
                break;

            // -------------------------------------------------------
            // 2️⃣ CONFIRM DEPOSIT PAYMENT ("I Have Paid")
            // -------------------------------------------------------
            case 'confirm_deposit_payment':
                $data = $parsedJsonBody ?? (json_decode(file_get_contents('php://input'), true) ?: []);
                $reference = trim((string)($data['reference'] ?? ''));
                if (!$reference) jsonResponse('error', 'Reference is required.');

                $stmt = $pdo->prepare("
                    SELECT * FROM transactions 
                    WHERE user_id = ? AND reference = ? AND type = 'deposit' AND status = 'pending'
                    LIMIT 1
                ");
                $stmt->execute([$user_id, $reference]);
                $txn = $stmt->fetch();
                if (!$txn) jsonResponse('error', 'No pending deposit found for this reference.');

                $details = json_decode($txn['details'] ?? '{}', true);
                if (!is_array($details)) $details = [];
                $details['user_marked_paid'] = true;
                $details['marked_paid_at'] = date('Y-m-d H:i:s');

                $upd = $pdo->prepare("UPDATE transactions SET details = ? WHERE id = ?");
                $upd->execute([json_encode($details), $txn['id']]);

                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_payment_confirmed',
                    'variables' => [
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'amount' => number_format($txn['amount'], 2),
                        'method' => ucfirst($txn['method']),
                        'reference' => $txn['reference'],
                        'details' => 'User confirmed payment manually.'
                    ]
                ]);

                jsonResponse('success', 'Deposit marked as paid. Please wait while we complete verification.');
                break;

            // -------------------------------------------------------
            // 3️⃣ WITHDRAW REQUEST
            // -------------------------------------------------------
            case 'withdraw_request':
                $data = $parsedJsonBody ?? (json_decode(file_get_contents('php://input'), true) ?: []);
                $amount = (float) ($data['amount'] ?? 0);
                $method = strtolower(trim((string)($data['method'] ?? '')));
                $details = $data['details'] ?? [];

                if ($amount <= 0 || !$method) jsonResponse('error', 'Invalid withdrawal details.');

                $wallet = getUserWallet($pdo, $user_id);
                if (!$wallet || $wallet['balance'] < $amount) jsonResponse('error', 'Insufficient wallet balance.');

                $reference = generateReference('TXH-WD');
                $detailsJson = json_encode([
                    'method' => $method,
                    'withdraw_details' => $details,
                    'requested_at' => date('Y-m-d H:i:s')
                ]);

                $pdo->beginTransaction();
                try {
                    $pdo->prepare("UPDATE wallets SET balance = balance - ?, pending_withdrawals = pending_withdrawals + ? WHERE user_id = ?")
                        ->execute([$amount, $amount, $user_id]);

                    $pdo->prepare("
                        INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                        VALUES (?, 'withdraw', ?, ?, ?, 'pending', ?, ?)
                    ")->execute([$user_id, $method, $amount, $reference, $detailsJson, date('Y-m-d H:i:s')]);

                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log('Withdraw request error: ' . $e->getMessage());
                    jsonResponse('error', 'Withdrawal processing failed. Please try again.');
                }

                // --- START MODIFIED LOGIC: Format Details for Admin Email ---
                $detailsHtml = '';
                $baseStyle = "style='margin: 6px 0;'";
                
                // Logic to format withdrawal details based on method
                if ($method === 'local_bank') {
                    $detailsHtml .= "<p {$baseStyle}><strong>-- Bank Details --</strong></p>";
                    $detailsHtml .= "<p {$baseStyle}><strong>Country:</strong> " . htmlspecialchars($details['country'] ?? 'N/A') . "</p>";
                    $detailsHtml .= "<p {$baseStyle}><strong>Bank Name:</strong> " . htmlspecialchars($details['bank_name'] ?? 'N/A') . "</p>";
                    $detailsHtml .= "<p {$baseStyle}><strong>Acct Holder:</strong> " . htmlspecialchars($details['account_holder'] ?? 'N/A') . "</p>";
                    if (!empty($details['iban'])) $detailsHtml .= "<p {$baseStyle}><strong>IBAN:</strong> " . htmlspecialchars($details['iban']) . "</p>";
                    if (!empty($details['bic'])) $detailsHtml .= "<p {$baseStyle}><strong>BIC/SWIFT:</strong> " . htmlspecialchars($details['bic']) . "</p>";
                    if (!empty($details['sort_code'])) $detailsHtml .= "<p {$baseStyle}><strong>Sort Code (UK):</strong> " . htmlspecialchars($details['sort_code']) . "</p>";
                    $detailsHtml .= "<p {$baseStyle}><strong>Currency:</strong> " . htmlspecialchars($details['currency'] ?? 'USD') . "</p>";
                    if (!empty($details['transaction_ref'])) $detailsHtml .= "<p {$baseStyle}><strong>User Ref:</strong> " . htmlspecialchars($details['transaction_ref']) . "</p>";
                } elseif ($method === 'wallet_address') {
                    $detailsHtml .= "<p {$baseStyle}><strong>-- Crypto Details --</strong></p>";
                    $detailsHtml .= "<p {$baseStyle}><strong>Coin:</strong> " . strtoupper(htmlspecialchars($details['coin'] ?? 'N/A')) . "</p>";
                    $detailsHtml .= "<p {$baseStyle}><strong>Wallet Address:</strong> " . htmlspecialchars($details['address'] ?? 'N/A') . "</p>";
                } elseif ($method === 'cash_mailing') {
                    $detailsHtml .= "<p {$baseStyle}><strong>-- Mailing Details --</strong></p>";
                    $detailsHtml .= "<div style='padding-left:15px; border-left: 2px solid #386641; margin-left: 5px;'>";
                    // Use nl2br for textarea content to preserve line breaks
                    $detailsHtml .= nl2br(htmlspecialchars($details['mail'] ?? 'N/A'));
                    $detailsHtml .= "</div>";
                } else {
                    $detailsHtml = "<p {$baseStyle}><strong>Details:</strong> No structured details provided for this method.</p>";
                }
                // --- END MODIFIED LOGIC ---

                sendEmail([
                    'to' => $user_email,
                    'template' => 'withdrawal_initiated',
                    'variables' => [
                        'user_name' => $user_name,
                        'amount' => number_format($amount, 2),
                        'method' => ucfirst(str_replace('_', ' ', $method)),
                        'reference' => $reference
                    ]
                ]);

                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_withdrawal_notification',
                    'variables' => [
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'amount' => number_format($amount, 2),
                        'method' => ucfirst($method),
                        'reference' => $reference,
                        'details_html' => $detailsHtml, // <-- New variable passed here
                    ]
                ]);

                jsonResponse('success', 'Withdrawal request submitted successfully.', ['reference' => $reference]);
                break;

            // -------------------------------------------------------
            // 4️⃣ GET PENDING DEPOSITS
            // -------------------------------------------------------
            case 'get_pending_deposits':
                $stmt = $pdo->prepare("
                    SELECT id, amount, method, reference, details, created_at 
                    FROM transactions 
                    WHERE user_id = ? AND type = 'deposit' AND status = 'pending'
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$user_id]);
                $rows = $stmt->fetchAll();
                jsonResponse('success', 'Pending deposits retrieved.', ['deposits' => $rows]);
                break;

            // -------------------------------------------------------
            // 🧾 5️⃣ GET WALLET SUMMARY (Full balance + combined earnings)
            // -------------------------------------------------------
            case 'get_wallet_summary':
                $wallet = getUserWallet($pdo, $user_id);
                if (!$wallet) jsonResponse('error', 'Wallet not found.');

                // --- Step 1: Aggregate all ROI earnings across active product tables ---
                // Each entry: [table, earnings_column]
                $earningsSources = [
                    ['investments',                  'roi_earned'],
                    ['holdlock',                     'roi_earned'],
                    ['infrastructure_contributions', 'roi_earned'],
                    ['xweekly_programs',             'total_earned'],
                    ['xshares_holdings',             'roi_earned'],
                ];

                $totalEarnings = 0;
                foreach ($earningsSources as [$table, $column]) {
                    $stmt = $pdo->prepare("SELECT COALESCE(SUM({$column}), 0) FROM {$table} WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $totalEarnings += (float)$stmt->fetchColumn();
                }

                // --- Step 2: Aggregate invested principal LIVE from each product table ---
                // The static wallet.* columns were not always kept in sync by the
                // create-flows, so we recompute the currently-held principal per
                // product directly from the source tables (current holdings only —
                // completed/unlocked positions have returned principal to balance).
                $investedSources = [
                    'total_investments' => "SELECT COALESCE(SUM(amount), 0) FROM investments WHERE user_id = ? AND status = 'active'",
                    'holdlock_savings'  => "SELECT COALESCE(SUM(amount), 0) FROM holdlock WHERE user_id = ? AND status IN ('locked','unlock_pending','matured')",
                    'xweekly_invested'  => "SELECT COALESCE(SUM(total_invested), 0) FROM xweekly_programs WHERE user_id = ? AND status IN ('active','paused')",
                    'xshares_invested'  => "SELECT COALESCE(SUM(amount), 0) FROM xshares_holdings WHERE user_id = ? AND status IN ('active','matured')",
                    'xgrid_invested'    => "SELECT COALESCE(SUM(amount), 0) FROM infrastructure_contributions WHERE user_id = ? AND status IN ('active','matured')",
                ];
                $invested = [];
                foreach ($investedSources as $key => $sql) {
                    $st = $pdo->prepare($sql);
                    $st->execute([$user_id]);
                    $invested[$key] = (float)$st->fetchColumn();
                }
                $totalInvested = array_sum($invested);

                // --- Step 3: Persist recomputed total earnings (single authoritative value).
                // NOTE: the invested principal is returned live below but NOT written back —
                // wallets.total_investments is maintained elsewhere as a cross-product GRAND
                // total (every create-flow + crons increment it), so overwriting it here with
                // a per-product figure would break dashboard.php / funds.php / cron accounting.
                $upd = $pdo->prepare("UPDATE wallets SET total_earnings = ? WHERE user_id = ?");
                $upd->execute([$totalEarnings, $user_id]);

                // --- Step 4: Re-fetch wallet record (now includes updated total_earnings) ---
                $walletStmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
                $walletStmt->execute([$user_id]);
                $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

                // --- Step 5: Build full summary response for frontend ---
                $summary = [
                    'balance'              => (float)$wallet['balance'],
                    'total_deposited'      => (float)$wallet['total_deposited'],
                    'total_withdrawn'      => (float)$wallet['total_withdrawn'],
                    'total_investments'    => $invested['total_investments'],
                    'holdlock_savings'     => $invested['holdlock_savings'],
                    'xweekly_invested'     => $invested['xweekly_invested'],
                    'xshares_invested'     => $invested['xshares_invested'],
                    'xgrid_invested'       => $invested['xgrid_invested'],
                    'total_invested'       => $totalInvested,
                    'pending_withdrawals'  => (float)$wallet['pending_withdrawals'],
                    'total_earnings'       => $totalEarnings,
                ];


                jsonResponse('success', 'Wallet summary retrieved successfully.', $summary);
                break;


                // -------------------------------------------------------
        // 6️⃣ GET DEPOSIT DETAILS FROM SETTINGS
        // -------------------------------------------------------
        case 'get_deposit_details':
            $data = $parsedJsonBody ?? (json_decode(file_get_contents('php://input'), true) ?: []);
            $method = strtolower(trim((string)($data['method'] ?? '')));

            if ($method === 'cash_mailing') {
                $column = 'cash_mailing_address';
            } elseif ($method === 'wallet_address') {
                $column = 'wallet_deposit_address';
            } else {
                jsonResponse('error', 'Invalid deposit method specified.');
            }

            // Fetch the deposit address/details from the settings table
            $stmt = $pdo->prepare("SELECT {$column} FROM settings LIMIT 1");
            $stmt->execute();
            $settings = $stmt->fetchColumn();

            if (empty($settings)) {
                jsonResponse('error', 'Support details not yet configured for this method.', ['details' => '']);
            }

            jsonResponse('success', 'Deposit details retrieved.', ['details' => $settings]);
            break;

                


            // -------------------------------------------------------
            // ❌ INVALID ACTION
            // -------------------------------------------------------
            default:
                jsonResponse('error', 'Invalid action specified.');
        }
    } catch (Exception $e) {
        error_log("Wallet API Exception: " . $e->getMessage());
        logDebug('Exception: ' . $e->getMessage());
        jsonResponse('error', 'Internal server error. Please try again later.');
    }
    ?>