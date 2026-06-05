<?php
// ===============================================
// FILE: /api/backend/xweekly.php
// PURPOSE: X-Weekly controller for TitanXHoldings
// ACTIONS: get_plans, get_summary, get_active, enrol, pause, resume, cancel
// ===============================================

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => false, // set true in production with HTTPS
    'cookie_samesite' => 'Strict',
]);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // tighten in production
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// includes
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/email.php'; // uses sendEmail()

// auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}
$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? ($_SESSION['name'] ?? 'User');
$user_email = $_SESSION['email'] ?? '';

// get pdo
try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// parse input
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
$action = trim($input['action'] ?? 'get_summary');

// helper responses (guarded so this file is safe to include alongside other endpoints
// that declare the same helpers, e.g. api/backend/investment.php)
if (!function_exists('jsonResponse')) {
    function jsonResponse($status, $message, $data = []) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }
}

if (!function_exists('generateReference')) {
    function generateReference($prefix = 'TXH-WKL') {
        return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
    }
}


// --------------------- ACTION: get_plans ---------------------
if ($action === 'get_plans') {
    try {
        $stmt = $pdo->query("SELECT * FROM xweekly_plans WHERE status = 'active' ORDER BY roi_percent ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', 'Plans loaded.', ['plans' => $rows]);
    } catch (Exception $e) {
        error_log('xweekly get_plans error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load plans.');
    }
}


// --------------------- ACTION: get_summary ---------------------
if ($action === 'get_summary') {
    try {
        $stmt = $pdo->prepare("SELECT
            COALESCE(SUM(total_invested),0) AS total_invested,
            COALESCE(SUM(total_earned),0)   AS total_earned,
            COUNT(CASE WHEN status='active' THEN 1 END) AS active_count,
            COUNT(CASE WHEN status='paused' THEN 1 END) AS paused_count
            FROM xweekly_programs WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        jsonResponse('success', 'X-Weekly summary loaded.', [
            'summary' => [
                'total_invested' => round((float)($row['total_invested'] ?? 0), 2),
                'total_earned'   => round((float)($row['total_earned'] ?? 0), 2),
                'active_count'   => (int)($row['active_count'] ?? 0),
                'paused_count'   => (int)($row['paused_count'] ?? 0),
            ]
        ]);
    } catch (Exception $e) {
        error_log('xweekly get_summary error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load summary.');
    }
}


// --------------------- ACTION: get_active ---------------------
if ($action === 'get_active') {
    try {
        $stmt = $pdo->prepare("SELECT xweekly_programs.*, xweekly_plans.plan_name
            FROM xweekly_programs
            LEFT JOIN xweekly_plans ON xweekly_programs.roi_percent = xweekly_plans.roi_percent
            WHERE xweekly_programs.user_id = ? AND xweekly_programs.status IN ('active','paused')
            ORDER BY xweekly_programs.started_at DESC");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', 'Active X-Weekly programs loaded.', ['programs' => $rows]);
    } catch (Exception $e) {
        error_log('xweekly get_active error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load active programs.');
    }
}


// --------------------- ACTION: enrol ---------------------
if ($action === 'enrol') {
    $weekly_amount = (float) ($input['weekly_amount'] ?? 0);
    $plan_id = (int) ($input['plan_id'] ?? 0);

    if ($plan_id <= 0) jsonResponse('error', 'Invalid plan selected.');
    if ($weekly_amount <= 0) jsonResponse('error', 'Enter a valid weekly amount.');

    try {
        // fetch plan
        $pstmt = $pdo->prepare("SELECT id, plan_name, roi_percent, min_weekly, max_weekly, status FROM xweekly_plans WHERE id = ?");
        $pstmt->execute([$plan_id]);
        $plan = $pstmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan || $plan['status'] !== 'active') {
            jsonResponse('error', 'Plan not found or inactive.');
        }

        $min = (float) $plan['min_weekly'];
        $max = $plan['max_weekly'] !== null ? (float) $plan['max_weekly'] : null;

        if ($weekly_amount < $min || ($max !== null && $weekly_amount > $max)) {
            $range = $max !== null ? "$$min – $$max" : "at least $$min";
            jsonResponse('error', "Weekly amount must be {$range} for this plan.");
        }

        $roi_percent = (float) $plan['roi_percent'];

        $pdo->beginTransaction();

        // lock wallet
        $wstmt = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        $wallet = $wstmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
            $wstmt->execute([$user_id]);
            $wallet = $wstmt->fetch(PDO::FETCH_ASSOC);
        }

        if ((float)$wallet['balance'] < $weekly_amount) {
            $pdo->rollBack();
            jsonResponse('error', 'Insufficient wallet balance.');
        }

        $reference = generateReference('TXH-WKL');
        $now = date('Y-m-d H:i:s');

        // Debit wallet, bump trackers
        $pdo->prepare("UPDATE wallets
            SET balance = balance - ?,
                total_investments = total_investments + ?,
                xweekly_invested  = xweekly_invested  + ?
            WHERE user_id = ?")
            ->execute([$weekly_amount, $weekly_amount, $weekly_amount, $user_id]);

        // Create program
        $pdo->prepare("INSERT INTO xweekly_programs
            (user_id, weekly_amount, total_invested, total_earned, roi_percent, status, next_debit_date, started_at, updated_at)
            VALUES (?, ?, ?, 0.00, ?, 'active', DATE_ADD(CURDATE(), INTERVAL 7 DAY), ?, ?)")
            ->execute([$user_id, $weekly_amount, $weekly_amount, $roi_percent, $now, $now]);

        $program_id = (int) $pdo->lastInsertId();

        // Transaction record
        $details = json_encode([
            'program_id' => $program_id,
            'plan_id'    => (int) $plan['id'],
            'plan_name'  => $plan['plan_name'],
            'kind'       => 'xweekly_enrolment',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                       VALUES (?, 'investment', 'wallet', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $weekly_amount, $reference, $details, $now]);

        $pdo->commit();

        // Emails
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'xweekly_enrolled',
                'variables' => [
                    'user_name'     => $user_name,
                    'plan_name'     => $plan['plan_name'],
                    'weekly_amount' => number_format($weekly_amount, 2),
                    'roi_percent'   => $roi_percent,
                    'next_debit'    => date('M d, Y', strtotime('+7 days')),
                    'reference'     => $reference,
                ]
            ]);

            if (defined('ADMIN_CONTACT_EMAIL')) {
                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_xweekly_notification',
                    'variables' => [
                        'user_name'     => $user_name,
                        'user_email'    => $user_email,
                        'plan_name'     => $plan['plan_name'],
                        'weekly_amount' => number_format($weekly_amount, 2),
                        'reference'     => $reference,
                    ]
                ]);
            }
        }

        jsonResponse('success', 'X-Weekly program started successfully.', [
            'program_id' => $program_id,
            'reference'  => $reference,
            'next_debit_date' => date('M d, Y', strtotime('+7 days')),
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('xweekly enrol error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to enrol in X-Weekly. Please try again.');
    }
}


// --------------------- ACTION: pause ---------------------
if ($action === 'pause') {
    $program_id = (int) ($input['program_id'] ?? 0);
    if ($program_id <= 0) jsonResponse('error', 'Invalid program id.');

    try {
        $stmt = $pdo->prepare("UPDATE xweekly_programs SET status = 'paused' WHERE id = ? AND user_id = ?");
        $stmt->execute([$program_id, $user_id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse('error', 'Program not found or not owned by you.');
        }

        jsonResponse('success', 'Program paused.', ['program_id' => $program_id]);
    } catch (Exception $e) {
        error_log('xweekly pause error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to pause program.');
    }
}


// --------------------- ACTION: resume ---------------------
if ($action === 'resume') {
    $program_id = (int) ($input['program_id'] ?? 0);
    if ($program_id <= 0) jsonResponse('error', 'Invalid program id.');

    try {
        $stmt = $pdo->prepare("UPDATE xweekly_programs
            SET status = 'active', next_debit_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            WHERE id = ? AND user_id = ?");
        $stmt->execute([$program_id, $user_id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse('error', 'Program not found or not owned by you.');
        }

        jsonResponse('success', 'Program resumed.', [
            'program_id' => $program_id,
            'next_debit_date' => date('M d, Y', strtotime('+7 days')),
        ]);
    } catch (Exception $e) {
        error_log('xweekly resume error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to resume program.');
    }
}


// --------------------- ACTION: cancel ---------------------
if ($action === 'cancel') {
    $program_id = (int) ($input['program_id'] ?? 0);
    if ($program_id <= 0) jsonResponse('error', 'Invalid program id.');

    try {
        $stmt = $pdo->prepare("UPDATE xweekly_programs SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$program_id, $user_id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse('error', 'Program not found or not owned by you.');
        }

        jsonResponse('success', 'Program cancelled. Funds already invested remain active.', [
            'program_id' => $program_id,
        ]);
    } catch (Exception $e) {
        error_log('xweekly cancel error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to cancel program.');
    }
}


// default
http_response_code(400);
jsonResponse('error', 'Invalid action.');
