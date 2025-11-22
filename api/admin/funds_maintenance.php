<?php
// FILE: /api/admin/funds_maintenance.php
// ============================================================
// PURPOSE: Manage Maintenance Plans and User Tasks (Admin View)
// Handles: Metrics, Plan CRUD, Active/Completed Tasks List (Paginated)
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
        error_log("Database Error in admin/funds_maintenance.php: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

// --- Metric Fetcher ---
function fetchMaintenanceMetrics($pdo) {
    $metrics = [
        'total_maintenance' => 0.00, // Total Value of all active/past user tasks
        'active_tasks' => 0,         // Count of active user tasks
        'total_spent' => 0.00,       // Total ROI earned (paid out/accrued)
        'next_scheduled' => '—',     // Earliest next_payment_date
    ];

    try {
        $stmt = executeQuery($pdo, "
            SELECT 
                COALESCE(SUM(amount), 0) AS total_amount,
                COALESCE(SUM(roi_earned), 0) AS total_roi_earned,
                COUNT(CASE WHEN status = 'active' THEN id END) AS active_tasks_count,
                MIN(CASE WHEN status = 'active' THEN next_payment_date ELSE NULL END) AS earliest_next_payment
            FROM maintenance
        ");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($row) {
            $metrics['total_maintenance'] = (float)$row['total_amount'];
            $metrics['total_spent'] = (float)$row['total_roi_earned'];
            $metrics['active_tasks'] = (int)$row['active_tasks_count'];
            $metrics['next_scheduled'] = $row['earliest_next_payment'] ? date('M d, Y', strtotime($row['earliest_next_payment'])) : '—';
        }
    } catch (PDOException $e) {
        error_log("Maintenance Metric Fetch Error: " . $e->getMessage());
    }

    return $metrics;
}

// --- Maintenance Plans Fetcher (Table 1 - Admin CRUD) ---
function fetchMaintenancePlans($pdo) {
    $sql = "SELECT 
                id, name, min_amount, max_amount, duration_days, roi_percent
            FROM maintenance_plans
            ORDER BY id ASC";

    $stmt = executeQuery($pdo, $sql);
    $plans = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    return array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'name' => htmlspecialchars($p['name']),
            'min_amount' => (float)$p['min_amount'],
            'max_amount' => (float)$p['max_amount'],
            'duration_days' => (int)$p['duration_days'],
            'roi_percent' => (float)$p['roi_percent'],
            'status' => 'Active' // Default display status for plans
        ];
    }, $plans);
}

// --- User Maintenance Tasks Fetcher (Table 2: Active & Table 3: Completed) ---
function fetchUserMaintenanceTasks($pdo, $statuses = ['active'], $page = 1, $perPage = 10, $search = '') {
    
    $statusIn = "'" . implode("','", $statuses) . "'";
    
    // Base SQL for counting and fetching
    $sql = "FROM maintenance m
            JOIN users u ON m.user_id = u.id
            LEFT JOIN maintenance_plans mp ON m.plan_id = mp.id
            WHERE m.status IN ({$statusIn})"; 
    $params = [];
    
    // Apply search 
    if (!empty($search)) {
        $searchWild = "%$search%";
        $sql .= " AND (m.plan_name LIKE :s OR u.full_name LIKE :s OR u.email LIKE :s)";
        $params[':s'] = $searchWild;
    }

    // 1. Count Total Records
    $countStmt = executeQuery($pdo, "SELECT COUNT(m.id) " . $sql, $params);
    $total = $countStmt ? (int)$countStmt->fetchColumn() : 0;

    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $limitSql = " LIMIT " . $perPage . " OFFSET " . $offset;

    // 2. Fetch Tasks (Paginated)
    $dataSql = "SELECT 
                    m.id,
                    m.plan_name,
                    m.amount,
                    m.roi_earned,
                    m.status,
                    m.created_at,
                    m.updated_at,
                    m.next_payment_date,
                    m.maturity_date,
                    u.full_name AS user_name
                " . $sql . " 
                ORDER BY m.created_at DESC" . $limitSql;

    $stmt = executeQuery($pdo, $dataSql, $params);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    $formatted = array_map(function($r) use ($statuses) {
        $data = [
            'id' => (int)$r['id'],
            'plan_name' => htmlspecialchars($r['plan_name']),
            'user_name' => htmlspecialchars($r['user_name'] ?: 'N/A User'),
            'amount' => (float)$r['amount'],
            'roi_earned' => (float)$r['roi_earned'],
            'status' => htmlspecialchars($r['status']),
            'created_at' => date('Y-m-d', strtotime($r['created_at']))
        ];
        
        if (in_array('active', $statuses) || in_array('unlocked', $statuses)) {
            // For active tasks
            $data['next_payment_date'] = $r['next_payment_date'] ? date('M d, Y', strtotime($r['next_payment_date'])) : 'N/A';
        } else {
            // For completed/archived tasks
            // Using updated_at or maturity_date to signify completion date
            $data['completed_on'] = $r['maturity_date'] ? date('M d, Y', strtotime($r['maturity_date'])) : date('M d, Y', strtotime($r['updated_at']));
        }
        
        return $data;
    }, $rows);
    
    return [
        'tasks' => $formatted,
        'current_page' => $page,
        'total_pages' => $totalPages
    ];
}


// --- POST / Management Handler (Plan CRUD) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
    $action = strtolower(trim($input['action'] ?? ''));

    if ($action === 'add_plan' || $action === 'edit_plan') {
        $name = trim($input['name'] ?? '');
        $min_amount = (float)($input['min_amount'] ?? 0);
        $duration_days = (int)($input['duration_days'] ?? 0);
        $roi_percent = (float)($input['roi_percent'] ?? 0);
        $id = (int)($input['id'] ?? 0); 
        
        // --- Required fields validation ---
        if (empty($name) || $min_amount <= 0 || $duration_days <= 0 || $roi_percent <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing required data (Name, Min Amount, Duration, ROI).']);
            exit;
        }

        // Default missing fields for INSERT/UPDATE (using dummy values)
        $max_amount = (float)($input['max_amount'] ?? 9999999.99); 
        $summary = trim($input['summary'] ?? 'Default maintenance plan summary.');
        $risk = trim($input['risk'] ?? 'Low');
        $payout = trim($input['payout'] ?? 'Maturity');
        $color = trim($input['color'] ?? 'Green');
        $purpose = trim($input['purpose'] ?? $name);


        try {
            if ($action === 'add_plan') {
                $sql = "INSERT INTO maintenance_plans 
                        (name, purpose, min_amount, max_amount, duration_days, roi_percent, risk, payout, summary, color)
                        VALUES (:name, :purpose, :min_amount, :max_amount, :duration_days, :roi_percent, :risk, :payout, :summary, :color)";
                $params = [
                    ':name' => $name, 
                    ':purpose' => $purpose,
                    ':min_amount' => number_format($min_amount, 2, '.', ''), 
                    ':max_amount' => number_format($max_amount, 2, '.', ''), 
                    ':duration_days' => $duration_days,
                    ':roi_percent' => number_format($roi_percent, 2, '.', ''),
                    ':risk' => $risk,
                    ':payout' => $payout,
                    ':summary' => $summary,
                    ':color' => $color
                ];
                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'New Maintenance Plan created successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create plan.']);
                }
            } elseif ($action === 'edit_plan') {
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid plan ID for edit.']);
                    exit;
                }

                $sql = "UPDATE maintenance_plans SET 
                            name = :name, 
                            min_amount = :min_amount,
                            max_amount = :max_amount,
                            duration_days = :duration_days,
                            roi_percent = :roi_percent,
                            summary = :summary
                        WHERE id = :id";
                $params = [
                    ':name' => $name, 
                    ':min_amount' => number_format($min_amount, 2, '.', ''), 
                    ':max_amount' => number_format($max_amount, 2, '.', ''), 
                    ':duration_days' => $duration_days,
                    ':roi_percent' => number_format($roi_percent, 2, '.', ''),
                    ':summary' => $summary,
                    ':id' => $id
                ];

                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'Maintenance Plan updated successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update plan.']);
                }
            }
        } catch (Exception $e) {
            error_log("Maintenance Plan Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing plan request.']);
        }
    } elseif ($action === 'update_task_status') {
        $task_id = (int)($input['task_id'] ?? 0);
        $new_status = trim($input['status'] ?? '');
        
        if ($task_id <= 0 || !in_array($new_status, ['active', 'matured', 'unlocked', 'expired'])) {
             echo json_encode(['status' => 'error', 'message' => 'Invalid task ID or status.']);
             exit;
        }

        $sql = "UPDATE maintenance SET status = :status, updated_at = NOW() WHERE id = :id";
        $params = [':status' => $new_status, ':id' => $task_id];
        $stmt = executeQuery($pdo, $sql, $params);
        
        if ($stmt) {
             echo json_encode(['status' => 'success', 'message' => 'Maintenance task status updated successfully.']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Failed to update task status.']);
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
    
    // Pagination parameters
    $active_page = max(1, (int)($input['active_page'] ?? 1));
    $completed_page = max(1, (int)($input['completed_page'] ?? 1));
    $per_page = 10; 

    // Case 1: Fetch a single plan's details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'plan_details') {
        $plan_id = (int)($input['id'] ?? 0);
        $stmt = executeQuery($pdo, "SELECT id, name, min_amount, max_amount, duration_days, roi_percent, summary FROM maintenance_plans WHERE id = :id", [':id' => $plan_id]);
        $plan = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($plan) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$plan['id'],
                    'name' => htmlspecialchars($plan['name']),
                    'min_amount' => (string)number_format((float)$plan['min_amount'], 2, '.', ''),
                    'max_amount' => (string)number_format((float)$plan['max_amount'], 2, '.', ''),
                    'duration_days' => (int)$plan['duration_days'],
                    'roi_percent' => (string)number_format((float)$plan['roi_percent'], 2, '.', ''),
                    'summary' => htmlspecialchars($plan['summary'])
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Maintenance Plan not found.']);
        }
        exit;
    }
    
    // Case 2: Main Dashboard Data Fetch (load_dashboard)
    $metrics = fetchMaintenanceMetrics($pdo);
    $plans = fetchMaintenancePlans($pdo);
    
    $active_tasks = fetchUserMaintenanceTasks($pdo, ['active', 'unlocked'], $active_page, $per_page, $search);
    $completed_tasks = fetchUserMaintenanceTasks($pdo, ['matured', 'expired'], $completed_page, $per_page, $search);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'metrics' => $metrics,
            'plans' => $plans,
            'active_tasks' => $active_tasks['tasks'],
            'active_page' => $active_tasks['current_page'],
            'active_total_pages' => $active_tasks['total_pages'],
            'completed_tasks' => $completed_tasks['tasks'],
            'completed_page' => $completed_tasks['current_page'],
            'completed_total_pages' => $completed_tasks['total_pages']
        ]
    ]);
    exit;
}

// Default response if no action matched
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
exit;
?>