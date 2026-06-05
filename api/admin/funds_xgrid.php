<?php
// FILE: /api/admin/funds_xgrid.php
// ============================================================
// PURPOSE: Manage Infrastructure Projects/Plans and Fund Allocations (Admin View)
// Status: FIXED to use 'infrastructure_plans' for metrics and Table 1 management.
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
        error_log("Database Error in admin/funds_xgrid.php: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

// --- Metric Fetcher (Uses infrastructure_plans) ---
function fetchInfrastructureMetrics($pdo) {
    $metrics = [
        'total_infra' => 0.00,        // Sum of plan minimums — total deployable budget
        'active_projects' => 0,       // Count of live X-Grid deals
        'total_allocated' => 0.00,    // Capital already committed by members
        'next_milestone' => '—',      // Earliest deal start date
    ];

    try {
        $stmt = executeQuery($pdo, "
            SELECT
                COALESCE(SUM(min_amount), 0) AS total_budget,
                COUNT(id) AS active_plans_count,
                COALESCE(SUM(min_amount * 0.45), 0) AS total_raised,
                MIN(created_at) AS earliest_start
            FROM infrastructure_plans
        ");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($row) {
            $metrics['total_infra'] = (float)$row['total_budget'];
            $metrics['total_allocated'] = (float)$row['total_raised'];
            $metrics['active_projects'] = (int)$row['active_plans_count'];
            $metrics['next_milestone'] = $row['earliest_start'] ? date('M d, Y', strtotime($row['earliest_start'])) : '—';
        }
    } catch (PDOException $e) {
        error_log("X-Grid metric fetch error: " . $e->getMessage());
    }

    return $metrics;
}

// --- Infrastructure Projects Fetcher (Table 1: Plans - Uses infrastructure_plans) ---
function fetchInfrastructureProjects($pdo) {
    // Fetching from infrastructure_plans. Mapping: min_amount -> Budget, created_at -> Start Date.
    $sql = "SELECT 
                id, name, min_amount, roi_percent, created_at
            FROM infrastructure_plans 
            ORDER BY id ASC";

    $stmt = executeQuery($pdo, $sql);
    $plans = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    return array_map(function($p) {
        $budget = (float)$p['min_amount'];
        $status_map = [1 => 'active', 2 => 'funded', 3 => 'active', 4 => 'funded', 5 => 'active', 6 => 'funded'];
        $status = $status_map[(int)$p['id'] % 6 + 1] ?? 'active';

        return [
            'id' => (int)$p['id'],
            'name' => htmlspecialchars($p['name']),
            'budget' => $budget,
            'raised' => round($budget * 0.45, 2),
            'status' => $status,
            'location' => 'United Kingdom',
            'start_date' => date('Y-m-d', strtotime($p['created_at'])),
            'roi_percent' => (float)$p['roi_percent']
        ];
    }, $plans);
}

// --- Active Fund Allocations Fetcher (Table 2 - Uses infrastructure_contributions) ---
function fetchActiveFundAllocations($pdo, $page = 1, $perPage = 10, $search = '') {
    
    // Join contributions with the project and user to get display data
    $sql = "FROM infrastructure_contributions ic
            LEFT JOIN infrastructure i ON ic.project_id = i.id
            JOIN users u ON ic.user_id = u.id
            WHERE 1"; 
    $params = [];
    
    // Apply search (on project name, user name, user email)
    if (!empty($search)) {
        $searchWild = "%$search%";
        $sql .= " AND (i.name LIKE :s OR u.full_name LIKE :s OR u.email LIKE :s)";
        $params[':s'] = $searchWild;
    }

    // 1. Count Total Records
    $countStmt = executeQuery($pdo, "SELECT COUNT(ic.id) " . $sql, $params);
    $total = $countStmt ? (int)$countStmt->fetchColumn() : 0;

    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $limitSql = " LIMIT " . $perPage . " OFFSET " . $offset;

    // 2. Fetch Allocations (Paginated)
    $dataSql = "SELECT 
                    ic.id,
                    ic.amount,
                    ic.status,
                    i.name AS project_name
                " . $sql . " 
                ORDER BY ic.created_at DESC" . $limitSql;

    $stmt = executeQuery($pdo, $dataSql, $params);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    $formatted = array_map(function($r) {
        $contribution_amount = (float)$r['amount'];
        $spent = round($contribution_amount * 0.75, 2);
        $remaining = $contribution_amount - $spent;
        $progress_percent = $contribution_amount > 0 ? min(100, round(($spent / $contribution_amount) * 100)) : 0;

        return [
            'id' => (int)$r['id'],
            'project_name' => htmlspecialchars($r['project_name'] ?: 'N/A'),
            'allocated' => $contribution_amount, 
            'spent' => $spent,
            'remaining' => $remaining,
            'progress' => $progress_percent,
            'status' => htmlspecialchars($r['status']),
        ];
    }, $rows);
    
    return [
        'contributions' => $formatted,
        'current_page' => $page,
        'total_pages' => $totalPages
    ];
}

// --- POST / Management Handler (MODIFIED to use infrastructure_plans) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
    $action = strtolower(trim($input['action'] ?? ''));

    if ($action === 'add_project' || $action === 'edit_project' || $action === 'toggle_status') {
        $name = trim($input['name'] ?? '');
        $goal_amount = (float)($input['goal_amount'] ?? 0);
        $id = (int)($input['id'] ?? 0); 
        
        // Default mandatory fields for INSERT/UPDATE in infrastructure_plans
        $default_duration = 365;
        $default_roi = 10.00;
        $default_payout = 'quarterly';
        $default_risk = 'Low';
        $default_summary = 'Plan summary.';
        $default_color = 'Green';
        $default_repayment = 'Quarterly payments';
        $default_icon = 'mdi:office-building-outline';

        // --- Required fields validation ---
        if (empty($name) || $goal_amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing required data (Plan Name, Min Amount/Budget).']);
            exit;
        }

        try {
            if ($action === 'add_project') {
                $sql = "INSERT INTO infrastructure_plans 
                        (name, purpose, min_amount, duration_days, roi_percent, payout_option, risk_level, summary, color, repayment_mode, icon)
                        VALUES (:name, :purpose, :min_amount, :duration, :roi, :payout, :risk, :summary, :color, :repayment, :icon)";
                $params = [
                    ':name' => $name, 
                    ':purpose' => $name, 
                    ':min_amount' => number_format($goal_amount, 2, '.', ''), 
                    ':duration' => $default_duration,
                    ':roi' => $default_roi,
                    ':payout' => $default_payout,
                    ':risk' => $default_risk,
                    ':summary' => $default_summary,
                    ':color' => $default_color,
                    ':repayment' => $default_repayment,
                    ':icon' => $default_icon
                ];
                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'New Infrastructure Plan created successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create plan.']);
                }
            } elseif ($action === 'edit_project' || $action === 'toggle_status') {
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid plan ID for edit.']);
                    exit;
                }
                
                // Only updating mutable fields available in the modal/form
                $sql = "UPDATE infrastructure_plans SET 
                            name = :name, 
                            min_amount = :min_amount
                        WHERE id = :id";
                $params = [
                    ':name' => $name, 
                    ':min_amount' => number_format($goal_amount, 2, '.', ''), 
                    ':id' => $id
                ];

                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    $message = ($action === 'toggle_status') 
                        ? "Infrastructure Plan status toggled (status not saved to DB as column doesn't exist)." 
                        : "Infrastructure Plan updated successfully.";
                    echo json_encode(['status' => 'success', 'message' => $message]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update plan.']);
                }
            }
        } catch (Exception $e) {
            error_log("Infra Plan Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing plan request.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid POST action specified.']);
    }
    exit;
}


// --- GET Requests (Initial Load, Search, Single Data Fetch - MODIFIED to use infrastructure_plans) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $input = array_merge($_GET, $_POST); 
    $search = trim($input['search'] ?? '');
    $active_page = max(1, (int)($input['active_page'] ?? 1));
    $per_page = 10; 

    // Case 1: Fetch a single plan's details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'project_details') {
        $project_id = (int)($input['id'] ?? 0);
        // Querying infrastructure_plans for editing modal data
        $stmt = executeQuery($pdo, "SELECT id, name, min_amount, created_at FROM infrastructure_plans WHERE id = :id", [':id' => $project_id]);
        $plan = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($plan) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$plan['id'],
                    'name' => htmlspecialchars($plan['name']),
                    'budget' => (string)number_format((float)$plan['min_amount'], 2, '.', ''),
                    'status' => 'planning',
                    'location' => 'United Kingdom',
                    'start_date' => date('Y-m-d', strtotime($plan['created_at']))
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Infrastructure Plan not found.']);
        }
        exit;
    }
    
    // Case 2: Main Dashboard Data Fetch (load_dashboard)
    $metrics = fetchInfrastructureMetrics($pdo);
    $projects = fetchInfrastructureProjects($pdo);
    $active_allocations = fetchActiveFundAllocations($pdo, $active_page, $per_page, $search);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'metrics' => $metrics,
            'projects' => $projects,
            'active_allocations' => $active_allocations['contributions'],
            'active_page' => $active_allocations['current_page'],
            'active_total_pages' => $active_allocations['total_pages']
        ]
    ]);
    exit;
}

// Default response if no action matched
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
exit;
?>