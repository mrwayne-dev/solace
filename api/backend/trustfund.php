    <?php
    ob_start(); 
    /**
     * ======================================================
     * HealthRunCare — TrustFund Backend API (Final)
     * ======================================================
     * Handles:
     *  - get_summary
     *  - get_plans
     *  - get_active
     *  - get_matured
     *  - start_trustfund
     *  - unlock
     * ======================================================
     */

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../utilities/email_temps.php';
    require_once __DIR__ . '/email.php';

    session_start();
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Parse input (JSON or POST)
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? $_POST ?? $_GET;
    $action = $input['action'] ?? null;

    try {
        $pdo = getPDO();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
        exit;
    }

    // JSON responder
    function respond($status, $message, $data = [])
    {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }

    if (!$action) respond('error', 'No action specified.');

    // ============================================
    // ACTION SWITCH
    // ============================================
    switch ($action) {

        /* ======================================
        1️⃣ SUMMARY
        ====================================== */
        case 'get_summary':
            $summaryStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) AS active_trusts,
                    COALESCE(SUM(amount), 0) AS total_invested,
                    COALESCE(SUM(roi_earned), 0) AS total_roi,
                    MIN(maturity_date) AS next_payout
                FROM trustfund
                WHERE user_id = ? AND (status IS NULL OR status IN ('active','unlock_pending','matured'))
            ");
            $summaryStmt->execute([$user_id]);
            $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [
                'active_trusts' => 0,
                'total_invested' => 0,
                'total_roi' => 0,
                'next_payout' => null
            ];

            $walletStmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
            $walletStmt->execute([$user_id]);
            $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC) ?: ['balance' => 0];

            // Format next payout like "May 2, 2026"
            if (!empty($summary['next_payout'])) {
                $summary['next_payout'] = date('M j, Y', strtotime($summary['next_payout']));
            } else {
                $summary['next_payout'] = '—';
            }

            respond('success', 'Summary loaded', ['summary' => $summary, 'wallet' => $wallet]);
            break;

        /* ======================================
        2️⃣ PLANS
        ====================================== */
        case 'get_plans':
        $stmt = $pdo->prepare("SELECT * FROM trustfund_plans ORDER BY id ASC");
        $stmt->execute();
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respond('success', 'Plans loaded', ['plans' => $plans]);
        break;


        /* ======================================
        3️⃣ ACTIVE TRUSTS
        ====================================== */
        case 'get_active':
            $stmt = $pdo->prepare("SELECT * FROM trustfund WHERE user_id = ? AND (status IS NULL OR status = 'active') ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $trusts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($trusts as &$t) {
                if (!empty($t['created_at'])) {
                    $t['created_at'] = date('M j, Y', strtotime($t['created_at']));
                }
            }
            respond('success', 'Active trusts loaded', ['trusts' => $trusts]);

            break;

        /* ======================================
        4️⃣ MATURED TRUSTS
        ====================================== */
        case 'get_matured':
            $stmt = $pdo->prepare("SELECT *, (amount + roi_earned) AS total_payout FROM trustfund WHERE user_id = ? AND status = 'matured' ORDER BY maturity_date ASC");
            $stmt->execute([$user_id]);
            $trusts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format maturity_date for all matured trustfunds
            foreach ($trusts as &$t) {
                if (!empty($t['maturity_date'])) {
                    $t['maturity_date'] = date('M j, Y', strtotime($t['maturity_date']));
                }
            }

            respond('success', 'Matured trusts loaded', ['trusts' => $trusts]);

            break;

        /* ======================================
        5️⃣ START TRUSTFUND
        ====================================== */
        case 'start_trustfund':
            $planId = intval($input['plan_id'] ?? 0);
            $amount = floatval($input['amount'] ?? 0);
            if ($planId <= 0 || $amount <= 0) respond('error', 'Invalid plan or amount.');

            // Get wallet
            $wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
            $wallet->execute([$user_id]);
            $walletData = $wallet->fetch(PDO::FETCH_ASSOC);
            if (!$walletData || $walletData['balance'] < $amount) respond('error', 'Insufficient wallet balance.');

            // Plans reference
            $plans = [
                1 => ['name' => 'Child Education Growth Plan', 'roi_percent' => 25.0, 'duration_days' => 1095, 'penalty_percent' => 1.50],
                2 => ['name' => 'Legacy Wealth Trust Plan', 'roi_percent' => 55.0, 'duration_days' => 1825, 'penalty_percent' => 1.50],
                3 => ['name' => 'Business Succession Trust Plan', 'roi_percent' => 48.0, 'duration_days' => 1460, 'penalty_percent' => 1.50],
                4 => ['name' => 'Medical Protection Trust Plan', 'roi_percent' => 18.0, 'duration_days' => 1095, 'penalty_percent' => 1.50],
                5 => ['name' => 'Future Builders Business Plan', 'roi_percent' => 38.0, 'duration_days' => 1460, 'penalty_percent' => 1.50],
                6 => ['name' => 'Guardian Trust Income Plan', 'roi_percent' => 35.0, 'duration_days' => 1825, 'penalty_percent' => 1.50],
                7 => ['name' => 'Perpetual Legacy Trust Plan', 'roi_percent' => 11.0, 'duration_days' => 9999, 'penalty_percent' => 1.50],
            ];

            $plan = $plans[$planId] ?? null;
            if (!$plan) respond('error', 'Invalid plan selected.');

            $roi = $plan['roi_percent'];
            $duration = $plan['duration_days'];
            $penalty_percent = $plan['penalty_percent'] ?? 1.50;
            $maturity_date = (new DateTime())->add(new DateInterval("P{$duration}D"))->format('Y-m-d');

            try {
                $pdo->beginTransaction();

                // Deduct wallet
                $upd = $pdo->prepare("
                    UPDATE wallets 
                    SET balance = balance - ?, total_investments = total_investments + ? 
                    WHERE user_id = ?
                ");
                $upd->execute([$amount, $amount, $user_id]);

                // Insert trustfund record
                $stmt = $pdo->prepare("
                    INSERT INTO trustfund (user_id, plan_name, amount, roi_percent, duration_days, penalty_percent, maturity_date, roi_earned, payout_option, created_at, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NOW(), 'active')
                ");
                $stmt->execute([$user_id, $plan['name'], $amount, $roi, $duration, $penalty_percent, $maturity_date, $plan['payout_option'] ?? 'maturity']);
                $trustId = $pdo->lastInsertId();

                // Transaction record
                $ref = 'TRF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
                $details = json_encode([
                    'plan_name' => $plan['name'],
                    'roi_percent' => $roi,
                    'duration_days' => $duration,
                    'maturity_date' => $maturity_date,
                    'penalty_percent' => $penalty_percent
                ]);

                $txn = $pdo->prepare("
                    INSERT INTO transactions (user_id, type, amount, reference, status, method, details)
                    VALUES (?, 'trustfund', ?, ?, 'completed', 'wallet_address', ?)
                ");
                $txn->execute([$user_id, $amount, $ref, $details]);

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log("TrustFund start error: " . $e->getMessage());
                respond('error', 'Failed to start trust fund. Try again later.');
            }

            // Send Emails
            try {
                $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $userStmt->execute([$user_id]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    sendEmail([
                        'to' => $user['email'],
                        'template' => 'trustfund_started',
                        'variables' => [
                            'user_name' => $user['full_name'] ?? 'User',
                            'plan_name' => $plan['name'],
                            'amount' => number_format($amount, 2),
                            'roi_percent' => $roi,
                            'maturity_date' => $maturity_date,
                            'reference' => $ref,
                            'penalty_percent' => $penalty_percent
                        ]
                    ]);

                    sendEmail([
                        'to' => ADMIN_CONTACT_EMAIL,
                        'template' => 'admin_trustfund_notification',
                        'variables' => [
                            'user_name' => $user['full_name'] ?? 'User',
                            'user_email' => $user['email'],
                            'plan_name' => $plan['name'],
                            'amount' => number_format($amount, 2),
                            'reference' => $ref
                        ]
                    ]);
                }
            } catch (Exception $e) {
                error_log("TrustFund email error: " . $e->getMessage());
            }

            respond('success', 'TrustFund started successfully.', ['reference' => $ref, 'trust_id' => $trustId]);
            break;

        /* ======================================
        6️⃣ UNLOCK (EARLY OR MATURE)
        ====================================== */
        case 'unlock':
            $trustId = intval($input['trust_id'] ?? 0);
            $early = intval($input['early'] ?? 0);
            if ($trustId <= 0) respond('error', 'Invalid trust ID.');

            $trustStmt = $pdo->prepare("SELECT * FROM trustfund WHERE id = ? AND user_id = ?");
            $trustStmt->execute([$trustId, $user_id]);
            $trust = $trustStmt->fetch(PDO::FETCH_ASSOC);
            if (!$trust) respond('error', 'Trust fund not found.');

            $status = $early ? 'unlocked_early' : 'completed';
            $roi_earned = floatval($trust['roi_earned']);
            $penalty_applied = 0;
            $payout = 0;

            if ($early) {
                $days_locked = (new DateTime())->diff(new DateTime($trust['created_at']))->days;
                $factor = min($days_locked / $trust['duration_days'], 1);
                $roi_earned = $trust['amount'] * $trust['roi_percent'] / 100 * $factor;
                $penalty_applied = $trust['amount'] * $trust['penalty_percent'] / 100 * $factor;
                $payout = $trust['amount'] + $roi_earned - $penalty_applied;

                $updTrust = $pdo->prepare("UPDATE trustfund SET status = ?, roi_earned = ?, penalty_applied = ?, updated_at = NOW() WHERE id = ?");
                $updTrust->execute([$status, $roi_earned, $penalty_applied, $trustId]);
            } else {
                $payout = $trust['amount'] + $roi_earned;
                $updTrust = $pdo->prepare("UPDATE trustfund SET status = ?, updated_at = NOW() WHERE id = ?");
                $updTrust->execute([$status, $trustId]);
            }

            // Credit wallet
            $credit = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
            $credit->execute([$payout, $user_id]);

            // Log transaction
            $ref = 'TRF-UNL-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $details = json_encode([
                'subtype' => $early ? 'early_unlock' : 'maturity_unlock',
                'trust_id' => $trustId,
                'roi_earned' => $roi_earned,
                'penalty_applied' => $penalty_applied,
                'total_payout' => $payout
            ]);

            $txn = $pdo->prepare("
                INSERT INTO transactions (user_id, type, amount, reference, status, method, details)
                VALUES (?, 'trustfund', ?, ?, 'completed', 'wallet_address', ?)
            ");
            $txn->execute([$user_id, $payout, $ref, $details]);

            // Send Emails
            try {
                $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $userStmt->execute([$user_id]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $template = $early ? 'trustfund_unlocked_early' : 'trustfund_matured';
                    $emailVars = [
                        'user_name' => $user['full_name'] ?? 'User',
                        'plan_name' => $trust['plan_name'],
                        'amount' => number_format($trust['amount'], 2),
                        'roi_earned' => number_format($roi_earned, 2),
                        'total_payout' => number_format($payout, 2),
                        'maturity_date' => $trust['maturity_date'],
                        'reference' => $ref
                    ];
                    if ($early) {
                        $emailVars['penalty'] = number_format($penalty_applied, 2);
                    }

                    sendEmail([
                        'to' => $user['email'],
                        'template' => $template,
                        'variables' => $emailVars
                    ]);
                }
            } catch (Exception $e) {
                error_log("TrustFund unlock email error: " . $e->getMessage());
            }

            respond('success', 'TrustFund unlocked successfully.', ['payout' => $payout, 'reference' => $ref]);
            break;

        default:
            respond('error', 'Invalid action.');
    }
ob_end_clean();