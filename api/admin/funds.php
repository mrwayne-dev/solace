<?php
// FILE: /api/admin/funds.php
// ============================================================
// PURPOSE: Manage Mining Contract Plans (tiers) and Active
//          Investments (Admin View).
// Handles: Metrics, Plan CRUD, Plan List, Active Investment List
// ============================================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../config/database.php';

try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database Error in admin/funds.php: " . $e->getMessage());
        return false;
    }
}

// --- Metric Fetcher ---
function fetchInvestmentMetrics($pdo) {
    $metrics = [
        'total_active_invest' => 0.00,
        'total_roi_paid' => 0.00,
        'ongoing_plans_count' => 0,
        'next_maturity' => '—',
        'total_plans' => 0,
    ];

    try {
        $stmt = executeQuery($pdo, "
            SELECT
                COALESCE(SUM(CASE WHEN status = 'active' THEN amount ELSE 0 END), 0) AS total_active,
                COALESCE(SUM(roi_earned), 0) AS total_roi_paid,
                COUNT(DISTINCT user_id) AS ongoing_users,
                MIN(CASE WHEN status = 'active' AND maturity_date >= CURDATE() THEN maturity_date ELSE NULL END) AS next_maturity
            FROM investments
        ");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($row) {
            $metrics['total_active_invest'] = (float)$row['total_active'];
            $metrics['total_roi_paid'] = (float)$row['total_roi_paid'];
            $metrics['ongoing_plans_count'] = (int)$row['ongoing_users'];
            $metrics['next_maturity'] = $row['next_maturity'] ? date('M d, Y', strtotime($row['next_maturity'])) : '—';
        }

        $metrics['total_plans'] = $pdo->query("SELECT COUNT(id) FROM investment_plans")->fetchColumn() ?? 0;
    } catch (PDOException $e) {
        error_log("Investment Metric Fetch Error: " . $e->getMessage());
    }

    return $metrics;
}

// --- Plans (tiers) Fetcher ---
function fetchPlans($pdo) {
    $sql = "SELECT id, name, daily_profit_percent, duration_days, min_amount, max_amount,
                   referral_commission_percent, icon, color, status, created_at
            FROM investment_plans
            ORDER BY min_amount ASC";

    $stmt = executeQuery($pdo, $sql);
    $plans = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    return array_map(function ($p) {
        $daily = (float)$p['daily_profit_percent'];
        $days = (int)$p['duration_days'];
        return [
            'id' => (int)$p['id'],
            'name' => htmlspecialchars($p['name']),
            'daily_profit_percent' => $daily,
            'duration_days' => $days,
            'total_return_percent' => round($daily * $days, 2),
            'min_amount' => (float)$p['min_amount'],
            'max_amount' => $p['max_amount'] !== null ? (float)$p['max_amount'] : null,
            'referral_commission_percent' => (float)$p['referral_commission_percent'],
            'icon' => htmlspecialchars($p['icon']),
            'color' => htmlspecialchars($p['color']),
            'status' => htmlspecialchars($p['status']),
            'created_at' => date('Y-m-d', strtotime($p['created_at'])),
        ];
    }, $plans);
}

// --- Active Investments Fetcher (Paginated) ---
function fetchActiveInvestments($pdo, $page = 1, $perPage = 10, $search = '') {
    $sql = "FROM investments i
            JOIN users u ON i.user_id = u.id
            WHERE i.status = 'active'";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (u.full_name LIKE :s OR u.email LIKE :s OR i.plan_name LIKE :s)";
        $params[':s'] = "%$search%";
    }

    $countStmt = executeQuery($pdo, "SELECT COUNT(i.id) " . $sql, $params);
    $total = $countStmt ? (int)$countStmt->fetchColumn() : 0;

    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $limitSql = " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

    $dataSql = "SELECT
                    i.id, i.plan_name, i.amount, i.daily_profit_percent, i.duration_days,
                    i.days_paid, i.status, i.maturity_date, i.created_at,
                    COALESCE(u.full_name, u.name) AS user_name,
                    u.email AS user_email, u.id AS user_id
                " . $sql . "
                ORDER BY i.created_at DESC" . $limitSql;

    $stmt = executeQuery($pdo, $dataSql, $params);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $formatted = array_map(fn($r) => [
        'id' => (int)$r['id'],
        'user_id' => (int)$r['user_id'],
        'user_name' => htmlspecialchars($r['user_name']),
        'user_email' => htmlspecialchars($r['user_email']),
        'plan_name' => htmlspecialchars($r['plan_name']),
        'amount' => (float)number_format((float)$r['amount'], 2, '.', ''),
        'daily_profit_percent' => (float)$r['daily_profit_percent'],
        'duration_days' => (int)$r['duration_days'],
        'days_paid' => (int)$r['days_paid'],
        'status' => htmlspecialchars($r['status']),
        'date_started' => date('Y-m-d', strtotime($r['created_at'])),
        'maturity_date' => $r['maturity_date'] ? date('Y-m-d', strtotime($r['maturity_date'])) : 'N/A',
    ], $rows);

    return ['investments' => $formatted, 'current_page' => $page, 'total_pages' => $totalPages];
}

// --- POST / Management Handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
    $action = strtolower(trim($input['action'] ?? ''));

    if ($action === 'add_plan' || $action === 'edit_plan') {
        $name = trim($input['name'] ?? '');
        $min_amount = (float)($input['min_amount'] ?? 0);
        $max_amount = ($input['max_amount'] ?? '') === '' ? null : (float)$input['max_amount'];
        $daily_profit = (float)($input['daily_profit_percent'] ?? 0);
        $duration = (int)($input['duration_days'] ?? 5);
        $ref_commission = (float)($input['referral_commission_percent'] ?? 10);
        $summary = $input['summary'] ?? '';
        $icon = $input['icon'] ?? 'mdi:pickaxe';
        $color = $input['color'] ?? 'Blue';
        $status = in_array(($input['status'] ?? 'active'), ['active', 'hidden'], true) ? $input['status'] : 'active';
        $id = (int)($input['id'] ?? 0);

        if ($name === '' || $duration <= 0 || $min_amount <= 0 || $daily_profit <= 0 || ($max_amount !== null && $min_amount > $max_amount)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing required data (Name, Duration, Daily Profit, valid min/max amounts).']);
            exit;
        }

        try {
            if ($action === 'add_plan') {
                $sql = "INSERT INTO investment_plans
                        (name, min_amount, max_amount, daily_profit_percent, duration_days, referral_commission_percent, summary, icon, color, status)
                        VALUES (:name, :min_amount, :max_amount, :daily, :duration, :ref, :summary, :icon, :color, :status)";
                $params = [
                    ':name' => $name, ':min_amount' => $min_amount, ':max_amount' => $max_amount,
                    ':daily' => $daily_profit, ':duration' => $duration, ':ref' => $ref_commission,
                    ':summary' => $summary, ':icon' => $icon, ':color' => $color, ':status' => $status,
                ];
                $stmt = executeQuery($pdo, $sql, $params);
                echo json_encode($stmt
                    ? ['status' => 'success', 'message' => 'New mining contract tier created successfully.']
                    : ['status' => 'error', 'message' => 'Failed to create plan.']);
            } else {
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid plan ID for edit.']);
                    exit;
                }
                $sql = "UPDATE investment_plans SET
                            name = :name, min_amount = :min_amount, max_amount = :max_amount,
                            daily_profit_percent = :daily, duration_days = :duration,
                            referral_commission_percent = :ref, summary = :summary,
                            icon = :icon, color = :color, status = :status
                        WHERE id = :id";
                $params = [
                    ':name' => $name, ':min_amount' => $min_amount, ':max_amount' => $max_amount,
                    ':daily' => $daily_profit, ':duration' => $duration, ':ref' => $ref_commission,
                    ':summary' => $summary, ':icon' => $icon, ':color' => $color, ':status' => $status,
                    ':id' => $id,
                ];
                $stmt = executeQuery($pdo, $sql, $params);
                echo json_encode($stmt
                    ? ['status' => 'success', 'message' => 'Mining contract tier updated successfully.']
                    : ['status' => 'error', 'message' => 'Failed to update plan.']);
            }
        } catch (Exception $e) {
            error_log("Plan Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing plan request.']);
        }
    } elseif ($action === 'edit_investment') {
        $inv_id = (int)($input['id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        $daily_profit = (float)($input['daily_profit_percent'] ?? 0);
        $status = trim($input['status'] ?? '');

        if ($inv_id <= 0 || empty($status)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid investment ID or missing status.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = executeQuery($pdo, "SELECT user_id, amount, status, roi_earned FROM investments WHERE id = :id FOR UPDATE", [':id' => $inv_id]);
            $inv = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

            if (!$inv) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Investment not found.']);
                exit;
            }

            $user_id = (int)$inv['user_id'];
            $old_status = $inv['status'];
            $old_amount = (float)$inv['amount'];

            executeQuery($pdo, "UPDATE investments SET amount = :amount, daily_profit_percent = :daily, status = :status WHERE id = :id", [
                ':amount' => number_format($amount, 2, '.', ''),
                ':daily' => number_format($daily_profit, 2, '.', ''),
                ':status' => $status,
                ':id' => $inv_id,
            ]);

            // Case A: manually completed → return principal + accrued ROI
            if ($status === 'completed' && $old_status !== 'completed') {
                $final_roi_earned = (float)$inv['roi_earned'];
                $total_payout = round($amount + $final_roi_earned, 2);

                executeQuery($pdo, "UPDATE wallets SET balance = balance + :payout, total_investments = GREATEST(total_investments - :amt, 0) WHERE user_id = :user_id",
                    [':payout' => $amount, ':amt' => $old_amount, ':user_id' => $user_id]);

                $reference = 'SLM-MATURE-' . strtoupper(uniqid());
                $details = json_encode(['investment_id' => $inv_id, 'admin_action' => 'manual_completion', 'principal_returned' => $amount]);
                executeQuery($pdo, "INSERT INTO transactions (user_id, type, amount, reference, status, details, method, created_at)
                                    VALUES (?, 'investment', ?, ?, 'completed', ?, 'system', NOW())",
                    [$user_id, $amount, $reference, $details]);
            }
            // Case B: manually cancelled → refund principal
            elseif ($status === 'cancelled' && $old_status === 'active') {
                executeQuery($pdo, "UPDATE wallets SET balance = balance + :amount, total_investments = GREATEST(total_investments - :amount, 0) WHERE user_id = :user_id",
                    [':amount' => $old_amount, ':user_id' => $user_id]);

                $reference = 'SLM-REFUND-' . strtoupper(uniqid());
                $details = json_encode(['investment_id' => $inv_id, 'admin_action' => 'manual_cancellation', 'refund_amount' => $old_amount]);
                executeQuery($pdo, "INSERT INTO transactions (user_id, type, amount, reference, status, details, method, created_at)
                                    VALUES (?, 'investment_refund', ?, ?, 'completed', ?, 'system', NOW())",
                    [$user_id, $old_amount, $reference, $details]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => "Investment ID {$inv_id} updated (status {$old_status} → {$status})."]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Edit Investment Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing investment update.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid POST action specified.']);
    }
    exit;
}


// --- GET Requests ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $input = array_merge($_GET, $_POST);
    $search = trim($input['search'] ?? '');
    $active_page = max(1, (int)($input['active_page'] ?? 1));
    $per_page = 10;

    // Case 1: single plan details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'plan_details') {
        $plan_id = (int)($input['id'] ?? 0);
        $stmt = executeQuery($pdo, "SELECT id, name, daily_profit_percent, duration_days, min_amount, max_amount, referral_commission_percent, summary, icon, color, status FROM investment_plans WHERE id = :id", [':id' => $plan_id]);
        $plan = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($plan) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$plan['id'],
                    'name' => htmlspecialchars($plan['name']),
                    'min_amount' => number_format((float)$plan['min_amount'], 2, '.', ''),
                    'max_amount' => $plan['max_amount'] !== null ? number_format((float)$plan['max_amount'], 2, '.', '') : '',
                    'daily_profit_percent' => number_format((float)$plan['daily_profit_percent'], 2, '.', ''),
                    'duration_days' => (int)$plan['duration_days'],
                    'referral_commission_percent' => number_format((float)$plan['referral_commission_percent'], 2, '.', ''),
                    'summary' => htmlspecialchars($plan['summary'] ?? ''),
                    'icon' => htmlspecialchars($plan['icon']),
                    'color' => htmlspecialchars($plan['color']),
                    'status' => htmlspecialchars($plan['status']),
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Plan not found.']);
        }
        exit;
    }

    // Case 2: single active investment details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'investment_details') {
        $inv_id = (int)($input['id'] ?? 0);
        $stmt = executeQuery($pdo, "SELECT i.id, i.plan_name, i.amount, i.daily_profit_percent, i.status, COALESCE(u.full_name, u.name) AS user_name, u.email AS user_email FROM investments i JOIN users u ON i.user_id = u.id WHERE i.id = :id", [':id' => $inv_id]);
        $inv = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($inv) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$inv['id'],
                    'user_display' => htmlspecialchars($inv['user_name']) . ' (' . htmlspecialchars($inv['user_email']) . ')',
                    'plan_name' => htmlspecialchars($inv['plan_name']),
                    'amount' => number_format((float)$inv['amount'], 2, '.', ''),
                    'daily_profit_percent' => number_format((float)$inv['daily_profit_percent'], 2, '.', ''),
                    'status' => htmlspecialchars($inv['status']),
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Investment not found.']);
        }
        exit;
    }

    // Case 3: Main dashboard data
    $metrics = fetchInvestmentMetrics($pdo);
    $plans = fetchPlans($pdo);
    $active_investments = fetchActiveInvestments($pdo, $active_page, $per_page, $search);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'metrics' => $metrics,
            'plans' => $plans,
            'active_investments' => $active_investments['investments'],
            'active_page' => $active_investments['current_page'],
            'active_total_pages' => $active_investments['total_pages'],
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
exit;
