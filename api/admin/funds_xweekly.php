<?php
// ============================================================
// FILE: /api/admin/funds_xweekly.php
// PURPOSE: Admin controller for X-Weekly plans & program oversight
// ACTIONS: add_plan, edit_plan, toggle_plan,
//          get_programs, admin_pause_program, admin_cancel_program
// ============================================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../backend/email.php';

try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
$action = trim($input['action'] ?? '');

function jsonOut($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}


// --------------------- ACTION: add_plan ---------------------
if ($action === 'add_plan') {
    $plan_name   = trim((string)($input['plan_name'] ?? ''));
    $roi_percent = (float) ($input['roi_percent'] ?? 0);
    $min_weekly  = (float) ($input['min_weekly'] ?? 50);
    $max_weekly  = array_key_exists('max_weekly', $input) && $input['max_weekly'] !== '' && $input['max_weekly'] !== null
                   ? (float) $input['max_weekly'] : null;
    $description = trim((string)($input['description'] ?? ''));
    $status      = $input['status'] ?? 'active';

    if ($plan_name === '')   jsonOut('error', 'Plan name is required.');
    if ($roi_percent <= 0)   jsonOut('error', 'ROI must be greater than zero.');
    if ($min_weekly <= 0)    jsonOut('error', 'Minimum weekly amount must be greater than zero.');
    if ($max_weekly !== null && $max_weekly < $min_weekly) {
        jsonOut('error', 'Maximum weekly amount must be greater than or equal to the minimum.');
    }
    if (!in_array($status, ['active', 'inactive'], true)) jsonOut('error', 'Invalid status.');

    try {
        $pdo->prepare("INSERT INTO xweekly_plans
            (plan_name, roi_percent, min_weekly, max_weekly, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$plan_name, $roi_percent, $min_weekly, $max_weekly, $description, $status]);

        jsonOut('success', 'X-Weekly plan added.', ['plan_id' => (int) $pdo->lastInsertId()]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly add_plan: ' . $e->getMessage());
        jsonOut('error', 'Failed to add plan.');
    }
}


// --------------------- ACTION: edit_plan ---------------------
if ($action === 'edit_plan') {
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) jsonOut('error', 'Invalid plan id.');

    $allowed = ['plan_name', 'roi_percent', 'min_weekly', 'max_weekly', 'description', 'status'];
    $sets = [];
    $params = [];

    foreach ($allowed as $field) {
        if (!array_key_exists($field, $input)) continue;

        if ($field === 'max_weekly') {
            $val = ($input[$field] === '' || $input[$field] === null) ? null : (float) $input[$field];
        } elseif (in_array($field, ['roi_percent', 'min_weekly'], true)) {
            $val = (float) $input[$field];
        } else {
            $val = $input[$field] === null ? null : (string) $input[$field];
        }

        if ($field === 'status' && !in_array($val, ['active', 'inactive'], true)) {
            jsonOut('error', 'Invalid status.');
        }

        $sets[] = "$field = ?";
        $params[] = $val;
    }

    if (empty($sets)) jsonOut('error', 'No fields supplied to update.');

    $params[] = $id;
    try {
        $stmt = $pdo->prepare("UPDATE xweekly_plans SET " . implode(', ', $sets) . " WHERE id = ?");
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) jsonOut('error', 'Plan not found or nothing changed.');
        jsonOut('success', 'Plan updated.', ['plan_id' => $id]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly edit_plan: ' . $e->getMessage());
        jsonOut('error', 'Failed to update plan.');
    }
}


// --------------------- ACTION: toggle_plan ---------------------
if ($action === 'toggle_plan') {
    $id     = (int) ($input['id'] ?? 0);
    $status = $input['status'] ?? '';
    if ($id <= 0) jsonOut('error', 'Invalid plan id.');
    if (!in_array($status, ['active', 'inactive'], true)) jsonOut('error', 'Invalid status.');

    try {
        $stmt = $pdo->prepare("UPDATE xweekly_plans SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        if ($stmt->rowCount() === 0) jsonOut('error', 'Plan not found.');
        jsonOut('success', 'Plan status updated.', ['plan_id' => $id, 'status' => $status]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly toggle_plan: ' . $e->getMessage());
        jsonOut('error', 'Failed to update plan status.');
    }
}


// --------------------- ACTION: get_programs ---------------------
if ($action === 'get_programs') {
    $status_filter = $input['status'] ?? null;

    try {
        $sql = "SELECT xweekly_programs.*,
                       users.full_name AS user_name, users.email AS user_email
                FROM xweekly_programs
                JOIN users ON xweekly_programs.user_id = users.id";
        $params = [];
        if ($status_filter !== null && $status_filter !== '' && $status_filter !== 'all') {
            $sql .= " WHERE xweekly_programs.status = ?";
            $params[] = $status_filter;
        }
        $sql .= " ORDER BY xweekly_programs.started_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonOut('success', 'Programs loaded.', ['programs' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly get_programs: ' . $e->getMessage());
        jsonOut('error', 'Failed to load programs.');
    }
}


// --------------------- ACTION: admin_pause_program ---------------------
if ($action === 'admin_pause_program') {
    $id     = (int) ($input['id'] ?? 0);
    $reason = trim((string)($input['reason'] ?? ''));
    if ($id <= 0) jsonOut('error', 'Invalid program id.');

    try {
        // Fetch program + owning user for the notification
        $pstmt = $pdo->prepare("SELECT xweekly_programs.id, xweekly_programs.status,
                xweekly_programs.weekly_amount,
                users.full_name AS user_name, users.email AS user_email
            FROM xweekly_programs
            JOIN users ON xweekly_programs.user_id = users.id
            WHERE xweekly_programs.id = ?");
        $pstmt->execute([$id]);
        $program = $pstmt->fetch(PDO::FETCH_ASSOC);
        if (!$program) jsonOut('error', 'Program not found.');
        if ($program['status'] === 'paused') jsonOut('error', 'Program is already paused.');
        if ($program['status'] === 'cancelled') jsonOut('error', 'Cancelled program cannot be paused.');

        $pdo->prepare("UPDATE xweekly_programs SET status = 'paused' WHERE id = ?")
            ->execute([$id]);

        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $program['user_email'],
                'template' => 'xweekly_admin_paused',
                'variables' => [
                    'user_name'     => $program['user_name'] ?? 'User',
                    'program_id'    => $id,
                    'weekly_amount' => number_format((float)$program['weekly_amount'], 2),
                    'reason'        => $reason !== '' ? $reason : '—',
                ]
            ]);
        }

        jsonOut('success', 'Program paused.', ['program_id' => $id]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly admin_pause_program: ' . $e->getMessage());
        jsonOut('error', 'Failed to pause program.');
    }
}


// --------------------- ACTION: admin_cancel_program ---------------------
if ($action === 'admin_cancel_program') {
    $id     = (int) ($input['id'] ?? 0);
    $reason = trim((string)($input['reason'] ?? ''));
    if ($id <= 0) jsonOut('error', 'Invalid program id.');

    try {
        // Fetch program + owning user for the notification
        $pstmt = $pdo->prepare("SELECT xweekly_programs.id, xweekly_programs.status,
                xweekly_programs.weekly_amount, xweekly_programs.total_invested,
                users.full_name AS user_name, users.email AS user_email
            FROM xweekly_programs
            JOIN users ON xweekly_programs.user_id = users.id
            WHERE xweekly_programs.id = ?");
        $pstmt->execute([$id]);
        $program = $pstmt->fetch(PDO::FETCH_ASSOC);
        if (!$program) jsonOut('error', 'Program not found.');
        if ($program['status'] === 'cancelled') jsonOut('error', 'Program is already cancelled.');

        $pdo->prepare("UPDATE xweekly_programs SET status = 'cancelled' WHERE id = ?")
            ->execute([$id]);

        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $program['user_email'],
                'template' => 'xweekly_admin_cancelled',
                'variables' => [
                    'user_name'      => $program['user_name'] ?? 'User',
                    'program_id'     => $id,
                    'weekly_amount'  => number_format((float)$program['weekly_amount'], 2),
                    'total_invested' => number_format((float)$program['total_invested'], 2),
                    'reason'         => $reason !== '' ? $reason : '—',
                ]
            ]);
        }

        jsonOut('success', 'Program cancelled. Funds already invested remain active.', ['program_id' => $id]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly admin_cancel_program: ' . $e->getMessage());
        jsonOut('error', 'Failed to cancel program.');
    }
}


// --------------------- ACTION: get_plans (admin) ---------------------
if ($action === 'get_plans') {
    try {
        $stmt = $pdo->query("SELECT * FROM xweekly_plans ORDER BY status DESC, roi_percent ASC");
        jsonOut('success', 'Plans loaded.', ['plans' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly get_plans: ' . $e->getMessage());
        jsonOut('error', 'Failed to load plans.');
    }
}


// --------------------- ACTION: get_metrics ---------------------
if ($action === 'get_metrics') {
    try {
        $row = $pdo->query("SELECT
            COALESCE(SUM(total_invested), 0) AS total_invested,
            COALESCE(SUM(total_earned), 0)   AS total_paid,
            COUNT(DISTINCT user_id)          AS total_members,
            COUNT(CASE WHEN status='active' THEN 1 END) AS active_programs
            FROM xweekly_programs")->fetch(PDO::FETCH_ASSOC) ?: [];

        $nextDebit = $pdo->query("SELECT MIN(next_debit_date) FROM xweekly_programs WHERE status='active' AND next_debit_date >= CURDATE()")->fetchColumn();

        jsonOut('success', 'Metrics loaded.', ['metrics' => [
            'total_invested'   => round((float)($row['total_invested'] ?? 0), 2),
            'total_paid'       => round((float)($row['total_paid'] ?? 0), 2),
            'active_programs'  => (int)($row['active_programs'] ?? 0),
            'total_members'    => (int)($row['total_members'] ?? 0),
            'next_debit'       => $nextDebit ? date('M d, Y', strtotime($nextDebit)) : '—',
        ]]);
    } catch (Exception $e) {
        error_log('admin funds_xweekly get_metrics: ' . $e->getMessage());
        jsonOut('error', 'Failed to load metrics.');
    }
}


// default
http_response_code(400);
jsonOut('error', 'Invalid action.');
