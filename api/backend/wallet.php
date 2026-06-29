<?php
ini_set('display_errors', 0);
error_reporting(0);
    // ===============================================
    // FILE: /api/backend/wallet.php
    // PURPOSE: Central wallet controller for Solace Mining
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
            // 0️⃣ GET ACTIVE DEPOSIT ADDRESSES (for the deposit form)
            // -------------------------------------------------------
            case 'get_deposit_addresses':
                $rows = $pdo->query("SELECT id, label, network, address FROM deposit_addresses WHERE is_active = 1 ORDER BY id DESC")
                            ->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as &$r) { $r['id'] = (int)$r['id']; }
                jsonResponse('success', 'Deposit addresses retrieved.', ['addresses' => $rows]);
                break;

            // -------------------------------------------------------
            // 1️⃣ INITIATE DEPOSIT (manual crypto: pick address, amount, proof)
            // -------------------------------------------------------
            case 'initiate_deposit':
                // Multipart form (proof upload) — read from $_POST / $_FILES
                $amount     = (float) ($_POST['amount'] ?? 0);
                $address_id = (int) ($_POST['address_id'] ?? 0);

                if ($amount <= 0) {
                    jsonResponse('error', 'Please enter a valid deposit amount.');
                }

                // Resolve the chosen (active) deposit address
                $addrStmt = $pdo->prepare("SELECT id, label, network, address FROM deposit_addresses WHERE id = ? AND is_active = 1");
                $addrStmt->execute([$address_id]);
                $addr = $addrStmt->fetch();
                if (!$addr) {
                    jsonResponse('error', 'Please select a valid deposit wallet.');
                }

                // Proof of payment is required
                if (empty($_FILES['proof']) || ($_FILES['proof']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    jsonResponse('error', 'Please attach your proof of payment (screenshot or PDF).');
                }
                $proof = $_FILES['proof'];
                $allowed = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','pdf'=>'application/pdf'];
                $ext = strtolower(pathinfo($proof['name'], PATHINFO_EXTENSION));
                if (!isset($allowed[$ext])) jsonResponse('error', 'Proof must be a JPG, PNG, WEBP or PDF file.');
                if ($proof['size'] > 5 * 1024 * 1024) jsonResponse('error', 'Proof file must be 5 MB or smaller.');
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $proof['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, array_values($allowed), true)) jsonResponse('error', 'That proof file type is not allowed.');

                $dir = __DIR__ . '/../../uploads/deposits';
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $proofName = 'deposit_' . $user_id . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($proof['tmp_name'], $dir . '/' . $proofName)) {
                    jsonResponse('error', 'Could not save your proof of payment. Please try again.');
                }
                $proofUrl = rtrim(APP_URL, '/') . '/uploads/deposits/' . $proofName;

                $reference = generateReference('SLM-DEP');
                $timestamp = date('Y-m-d H:i:s');
                $details = json_encode([
                    'initiated_at' => $timestamp,
                    'method'       => 'crypto',
                    'address_id'   => (int)$addr['id'],
                    'network'      => $addr['network'],
                    'address'      => $addr['address'],
                    'proof_url'    => $proofUrl,
                    'proof_file'   => $proofName,
                ]);

                $pdo->prepare("
                    INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                    VALUES (?, 'deposit', 'wallet_address', ?, ?, 'pending', ?, ?)
                ")->execute([$user_id, $amount, $reference, $details, $timestamp]);

                // Notify the user (pending) ...
                sendEmail([
                    'to' => $user_email,
                    'template' => 'deposit_initiated',
                    'variables' => [
                        'user_name' => $user_name,
                        'amount' => number_format($amount, 2),
                        'method' => $addr['network'] . ' (crypto)',
                        'reference' => $reference
                    ]
                ]);

                // ... and notify the admin to review + approve/cancel
                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_deposit_notification',
                    'variables' => [
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'amount' => number_format($amount, 2),
                        'method' => $addr['network'] . ' → ' . $addr['address'],
                        'reference' => $reference
                    ]
                ]);

                jsonResponse('success', 'Deposit submitted! It is now pending admin confirmation.', ['reference' => $reference]);
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

                // Enforce admin-set withdrawal lock
                $lockStmt = $pdo->prepare("SELECT withdrawals_locked, withdrawal_lock_reason FROM users WHERE id = ?");
                $lockStmt->execute([$user_id]);
                $lock = $lockStmt->fetch();
                if ($lock && (int)$lock['withdrawals_locked'] === 1) {
                    $reason = trim((string)($lock['withdrawal_lock_reason'] ?? ''));
                    jsonResponse('error', $reason !== ''
                        ? ('Withdrawals are currently restricted on your account: ' . $reason)
                        : 'Withdrawals are currently restricted on your account. Please contact support.');
                }

                $wallet = getUserWallet($pdo, $user_id);
                // Withdrawable = spendable capital (balance) + spendable profit (profit_balance)
                $capital = (float) ($wallet['balance'] ?? 0);
                $profit  = (float) ($wallet['profit_balance'] ?? 0);
                $available = round($capital + $profit, 2);
                if (!$wallet || $available < $amount) jsonResponse('error', 'Insufficient withdrawable balance.');

                // Draw from profit first, then capital — record the split so a later
                // cancellation refunds each bucket correctly.
                $fromProfit  = min($amount, $profit);
                $fromCapital = round($amount - $fromProfit, 2);

                $reference = generateReference('SLM-WD');
                $detailsJson = json_encode([
                    'method' => $method,
                    'withdraw_details' => $details,
                    'from_capital' => $fromCapital,
                    'from_profit' => $fromProfit,
                    'requested_at' => date('Y-m-d H:i:s')
                ]);

                $pdo->beginTransaction();
                try {
                    $pdo->prepare("UPDATE wallets SET balance = balance - ?, profit_balance = profit_balance - ?, pending_withdrawals = pending_withdrawals + ? WHERE user_id = ?")
                        ->execute([$fromCapital, $fromProfit, $amount, $user_id]);

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
                    $detailsHtml .= "<div style='padding-left:15px; border-left: 2px solid #004DC0; margin-left: 5px;'>";
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

                // --- Step 1: Aggregate ROI earnings from mining contracts ---
                $stmt = $pdo->prepare("SELECT COALESCE(SUM(roi_earned), 0) FROM investments WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $totalEarnings = (float)$stmt->fetchColumn();

                // --- Step 2: Aggregate currently-active invested principal ---
                $st = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM investments WHERE user_id = ? AND status = 'active'");
                $st->execute([$user_id]);
                $totalInvested = (float)$st->fetchColumn();

                // --- Step 3: Persist recomputed total earnings (single authoritative value) ---
                $upd = $pdo->prepare("UPDATE wallets SET total_earnings = ? WHERE user_id = ?");
                $upd->execute([$totalEarnings, $user_id]);

                // --- Step 4: Re-fetch wallet record (now includes updated total_earnings) ---
                $walletStmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
                $walletStmt->execute([$user_id]);
                $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

                // --- Step 5: Build full summary response for frontend ---
                $profitBalance = (float)($wallet['profit_balance'] ?? 0);
                $summary = [
                    'balance'              => (float)$wallet['balance'],        // Main Wallet = spendable capital
                    'profit_balance'       => $profitBalance,                   // spendable profit (withdrawable)
                    'available_balance'    => round((float)$wallet['balance'] + $profitBalance, 2), // total withdrawable
                    'total_deposited'      => (float)$wallet['total_deposited'],
                    'total_withdrawn'      => (float)$wallet['total_withdrawn'],
                    'total_investments'    => $totalInvested,
                    'total_invested'       => $totalInvested,
                    'referral_earnings'    => (float)$wallet['referral_earnings'],
                    'pending_withdrawals'  => (float)$wallet['pending_withdrawals'],
                    'total_earnings'       => $totalEarnings,                   // lifetime profit (display)
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