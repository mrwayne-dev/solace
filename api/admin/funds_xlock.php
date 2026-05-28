<?php
// FILE: /api/admin/funds_holdlock.php
// ============================================================
// PURPOSE: Manage HoldLock Savings Plans and Active Entries (Admin View)
// Handles: Metrics, Plan CRUD, Active HoldLock List (Paginated), Manual Payout
// ============================================================

session_start();
header('Content-Type: application/json');

// Ensure only authenticated admins can access this script
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

/**
 * Helper function to execute a prepared statement and return result set.
 */
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        // Log the actual error for debugging
        error_log("Database Error in admin/funds_holdlock.php: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

// --- Metric Fetcher ---
function fetchHoldlockMetrics($pdo) {
    $metrics = [
        'total_holdlock' => 0.00,
        'holdlock_users' => 0,
        'total_interest' => 0.00,
        'next_unlock' => '—',
    ];

    try {
        // Total Locked Balance (from wallets summary field)
        $metrics['total_holdlock'] = $pdo->query("SELECT COALESCE(SUM(holdlock_savings), 0) FROM wallets")->fetchColumn() ?? 0.00;
        
        // Active HoldLock Users & Total Interest Earned (from holdlock table)
        $stmt = executeQuery($pdo, "
            SELECT 
                COUNT(DISTINCT user_id) AS active_users,
                COALESCE(SUM(roi_earned), 0) AS total_roi_earned
            FROM holdlock
            WHERE status IN ('locked', 'unlock_pending')
        ");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($row) {
            $metrics['holdlock_users'] = (int)$row['active_users'];
            $metrics['total_interest'] = (float)$row['total_roi_earned'];
        }
        
        // Next Unlock Date (Earliest maturity date for active locks)
        $stmt = $pdo->query("
            SELECT MIN(maturity_date) 
            FROM holdlock 
            WHERE status IN ('locked', 'unlock_pending') AND maturity_date >= CURDATE()
        ");
        $next_date = $stmt->fetchColumn();
        $metrics['next_unlock'] = $next_date ? date('M d, Y', strtotime($next_date)) : '—';

    } catch (PDOException $e) {
        error_log("Holdlock Metric Fetch Error: " . $e->getMessage());
    }

    return $metrics;
}

// --- HoldLock Plans Fetcher ---
function fetchHoldlockPlans($pdo) {
    $sql = "SELECT 
                id, name, lock_period_text, duration_days, min_amount, max_amount, roi_range 
            FROM holdlock_plans 
            ORDER BY id ASC";

    $stmt = executeQuery($pdo, $sql);
    $plans = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    return array_map(function($p) {
        $days = (int)$p['duration_days'];
        $lock_display = $days > 0 ? $p['lock_period_text'] : 'N/A';
        
        return [
            'id' => (int)$p['id'],
            'name' => htmlspecialchars($p['name']),
            'lock_period_text' => htmlspecialchars($lock_display),
            'duration_days' => $days,
            'min_amount' => (float)$p['min_amount'],
            'max_amount' => (float)$p['max_amount'],
            'roi_range' => htmlspecialchars($p['roi_range']), 
            'status' => 'active', // TEMPORARY: Hardcoded active status for display
        ];
    }, $plans);
}

// --- Active HoldLock Savings Fetcher (Paginated) ---
function fetchActiveHoldlock($pdo, $page = 1, $perPage = 10, $search = '') {
    
    $sql = "FROM holdlock h
            JOIN users u ON h.user_id = u.id
            WHERE 1"; // Fetch all holdlock entries for admin view
    $params = [];
    
    // Apply search
    if (!empty($search)) {
        $searchWild = "%$search%";
        $sql .= " AND (u.full_name LIKE :s OR u.email LIKE :s OR h.plan_name LIKE :s)";
        $params[':s'] = $searchWild;
    }

    // 1. Count Total Records
    $countStmt = executeQuery($pdo, "SELECT COUNT(h.id) " . $sql, $params);
    $total = $countStmt ? (int)$countStmt->fetchColumn() : 0;

    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $limitSql = " LIMIT " . $perPage . " OFFSET " . $offset;

    // 2. Fetch HoldLock Entries (Paginated)
    $dataSql = "SELECT 
                    h.id,
                    h.plan_name,
                    h.amount,
                    h.roi_percent,
                    h.maturity_date,
                    h.status,
                    h.roi_earned,
                    COALESCE(u.full_name, u.name) AS user_name,
                    u.email AS user_email
                " . $sql . " 
                ORDER BY h.created_at DESC" . $limitSql;

    $stmt = executeQuery($pdo, $dataSql, $params);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    $formatted = array_map(fn($r) => [
        'id' => (int)$r['id'],
        'user_name' => htmlspecialchars($r['user_name']),
        'user_email' => htmlspecialchars($r['user_email']),
        'plan_name' => htmlspecialchars($r['plan_name'] ?: 'Custom Lock'),
        'amount' => (float)number_format((float)$r['amount'], 2, '.', ''),
        'roi_percent' => (float)$r['roi_percent'],
        'roi_earned' => (float)number_format((float)$r['roi_earned'], 2, '.', ''),
        'status' => htmlspecialchars($r['status']),
        'maturity_date' => $r['maturity_date'] ? date('Y-m-d', strtotime($r['maturity_date'])) : 'N/A'
    ], $rows);
    
    return [
        'holdlocks' => $formatted,
        'current_page' => $page,
        'total_pages' => $totalPages
    ];
}

// --- POST / Management Handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
    $action = strtolower(trim($input['action'] ?? ''));

    if ($action === 'add_plan' || $action === 'edit_plan') {
        $name = trim($input['name'] ?? '');
        $duration = (int)($input['duration'] ?? 0);
        $interest = (float)($input['interest'] ?? 0);
        $min_amount = (float)($input['min_amount'] ?? 0);
        // $status = trim($input['status'] ?? 'active'); // Removed due to missing DDL column
        $id = (int)($input['id'] ?? 0); 
        
        // --- Required fields validation ---
        if (empty($name) || $duration <= 0 || $interest <= 0 || $min_amount < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing required data (Name, Lock Period in days, Interest Rate). Min Amount can be 0.']);
            exit;
        }

        // Generate placeholders for required NOT NULL fields in holdlock_plans DDL
        $purpose = 'HoldLock Plan for admin.';
        $max_amount = $input['max_amount'] ?? 99999999.99; 
        $lock_period_text = "{$duration} days";
        // Convert single interest rate to a simple range for consistency with seeding data (e.g., 7% -> 7.00%)
        $roi_range = number_format($interest, 2) . '%';
        $risk = 'low';
        $payout = 'At maturity';
        $summary = 'A holdlock savings plan.';
        $icon = 'mdi:lock-outline';
        $color = 'green';
        
        try {
            if ($action === 'add_plan') {
                $sql = "INSERT INTO holdlock_plans 
                        (name, purpose, min_amount, max_amount, lock_period_text, duration_days, roi_range, risk, payout, summary, icon, color) 
                        VALUES (:name, :purpose, :min_amount, :max_amount, :lock_period_text, :duration, :roi_range, :risk, :payout, :summary, :icon, :color)";
                $params = [
                    ':name' => $name, 
                    ':purpose' => $purpose, 
                    ':min_amount' => number_format($min_amount, 2, '.', ''), 
                    ':max_amount' => number_format($max_amount, 2, '.', ''), 
                    ':lock_period_text' => $lock_period_text,
                    ':duration' => $duration,
                    ':roi_range' => $roi_range,
                    ':risk' => $risk,
                    ':payout' => $payout,
                    ':summary' => $summary,
                    ':icon' => $icon,
                    ':color' => $color,
                ];
                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'New HoldLock plan created successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create HoldLock plan.']);
                }
            } elseif ($action === 'edit_plan') {
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid plan ID for edit.']);
                    exit;
                }

                $sql = "UPDATE holdlock_plans SET 
                            name = :name, 
                            min_amount = :min_amount, 
                            max_amount = :max_amount, 
                            lock_period_text = :lock_period_text, 
                            duration_days = :duration, 
                            roi_range = :roi_range 
                        WHERE id = :id";
                $params = [
                    ':name' => $name, 
                    ':min_amount' => number_format($min_amount, 2, '.', ''), 
                    ':max_amount' => number_format($max_amount, 2, '.', ''), 
                    ':lock_period_text' => $lock_period_text,
                    ':duration' => $duration,
                    ':roi_range' => $roi_range,
                    ':id' => $id
                ];

                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'HoldLock plan updated successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update plan.']);
                }
            }
        } catch (Exception $e) {
            error_log("HoldLock Plan Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing HoldLock plan request.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid POST action specified.']);
    }
    exit;
}


// --- GET Requests (Initial Load, Search, Single Data Fetch) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $input = array_merge($_GET, $_POST); 
    $search = trim($input['search'] ?? '');
    $active_page = max(1, (int)($input['active_page'] ?? 1));
    $per_page = 10; 

    // Case 1: Fetch a single plan's details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'plan_details') {
        $plan_id = (int)($input['id'] ?? 0);
        $stmt = executeQuery($pdo, "SELECT id, name, min_amount, max_amount, duration_days, roi_range FROM holdlock_plans WHERE id = :id", [':id' => $plan_id]);
        $plan = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($plan) {
            // Attempt to parse a single rate for the modal input (e.g., '7-9%' -> 7.00, or '10.5%' -> 10.5)
            $rate_str = trim(str_replace('%', '', $plan['roi_range']));
            $interest_rate = (float)(strpos($rate_str, '-') !== false ? substr($rate_str, 0, strpos($rate_str, '-')) : $rate_str);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$plan['id'],
                    'name' => htmlspecialchars($plan['name']),
                    'min_amount' => (string)number_format((float)$plan['min_amount'], 2, '.', ''),
                    'max_amount' => (string)number_format((float)$plan['max_amount'], 2, '.', ''),
                    'lock_days' => (int)$plan['duration_days'],
                    'interest_rate' => max(0.00, $interest_rate), 
                    'status' => 'active', 
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'HoldLock Plan not found.']);
        }
        exit;
    }
    
    // Case 2: Main Dashboard Data Fetch (load_dashboard)
    $metrics = fetchHoldlockMetrics($pdo);
    $plans = fetchHoldlockPlans($pdo);
    $active_holdlock = fetchActiveHoldlock($pdo, $active_page, $per_page, $search);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'metrics' => $metrics,
            'plans' => $plans,
            'active_holdlock' => $active_holdlock['holdlocks'],
            'active_page' => $active_holdlock['current_page'],
            'active_total_pages' => $active_holdlock['total_pages']
        ]
    ]);
    exit;
}

// Default response if no action matched
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
exit;
?>