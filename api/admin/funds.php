<?php
// FILE: /api/admin/funds.php
// ============================================================
// PURPOSE: Manage Investment Plans and Active Investments (Admin View)
// Handles: Metrics, Plan CRUD, Plan List, Active Investment List (Paginated)
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
        // Investment Summary Metrics
        $stmt = executeQuery($pdo, "
            SELECT 
                COALESCE(SUM(CASE WHEN status = 'active' THEN amount ELSE 0 END), 0) AS total_active,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN roi_earned ELSE 0 END), 0) AS total_roi_paid,
                COUNT(DISTINCT user_id) AS ongoing_users,
                MIN(CASE WHEN status = 'active' AND maturity_date >= CURDATE() THEN maturity_date ELSE NULL END) AS next_maturity 
            FROM investments
        ");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($row) {
            $metrics['total_active_invest'] = (float)$row['total_active'];
            // Total ROI Paid includes all roi_earned on completed investments
            $metrics['total_roi_paid'] = (float)$row['total_roi_paid'];
            // Ongoing Plans is counted as unique users with active investments
            $metrics['ongoing_plans_count'] = (int)$row['ongoing_users']; 
            $metrics['next_maturity'] = $row['next_maturity'] ? date('M d, Y', strtotime($row['next_maturity'])) : '—';
        }

        // Total Plans Count
        $metrics['total_plans'] = $pdo->query("SELECT COUNT(id) FROM investment_plans")->fetchColumn() ?? 0;

    } catch (PDOException $e) {
        error_log("Investment Metric Fetch Error: " . $e->getMessage());
    }

    return $metrics;
}

// --- Investment Plans Fetcher ---
function fetchPlans($pdo) {
    // FIX: Removed 'status' from SELECT list as it is missing from the provided DDL schema.
    $sql = "SELECT 
                id, title, roi_percent, duration_days, min_amount, max_amount, risk, created_at 
            FROM investment_plans 
            ORDER BY id ASC";

    $stmt = executeQuery($pdo, $sql);
    $plans = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    return array_map(function($p) {
        $base_roi = (float)$p['roi_percent'];
        // Use a 10% tolerance for the display range min/max
        $roi_min = number_format($base_roi * 0.9, 2);
        $roi_max = number_format($base_roi * 1.1, 2);
        
        $days = (int)$p['duration_days'];
        if ($days >= 365 && $days % 365 === 0) {
            $term_display = ($days / 365) . ' Year(s)';
        } elseif ($days >= 30 && $days % 30 === 0) {
             $term_display = ($days / 30) . ' Month(s)';
        } else {
             $term_display = $days . ' Days';
        }
        
        return [
            'id' => (int)$p['id'],
            'title' => htmlspecialchars($p['title']),
            'roi_base_percent' => $base_roi,
            'roi_display_range' => "{$roi_min}% - {$roi_max}%", 
            'duration_days' => $days,
            'term_display' => $term_display,
            'min_amount' => (float)$p['min_amount'],
            'max_amount' => (float)$p['max_amount'],
            'risk' => ucfirst(htmlspecialchars($p['risk'])),
            // TEMPORARY FIX: Hardcoded status as 'active' for frontend rendering, 
            // since the column is missing in the database.
            'status' => 'active', 
            'created_at' => date('Y-m-d', strtotime($p['created_at']))
        ];
    }, $plans);
}

// --- Active Investments Fetcher (Paginated) ---
function fetchActiveInvestments($pdo, $page = 1, $perPage = 10, $search = '') {
    
    $sql = "FROM investments i
            JOIN users u ON i.user_id = u.id
            WHERE i.status = 'active'";
    $params = [];
    
    // Apply search
    if (!empty($search)) {
        $searchWild = "%$search%";
        // Search by user name, user email, or plan name
        $sql .= " AND (u.full_name LIKE :s OR u.email LIKE :s OR i.plan_name LIKE :s)";
        $params[':s'] = $searchWild;
    }

    // 1. Count Total Records
    $countStmt = executeQuery($pdo, "SELECT COUNT(i.id) " . $sql, $params);
    $total = $countStmt ? (int)$countStmt->fetchColumn() : 0;

    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $limitSql = " LIMIT " . $perPage . " OFFSET " . $offset;

    // 2. Fetch Active Investments (Paginated)
    $dataSql = "SELECT 
                    i.id,
                    i.plan_name,
                    i.amount,
                    i.roi_percent,
                    i.duration_days,
                    i.status,
                    i.maturity_date,
                    i.created_at,
                    COALESCE(u.full_name, u.name) AS user_name,
                    u.email AS user_email,
                    u.id AS user_id
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
        'roi_percent' => (float)$r['roi_percent'],
        'duration_days' => (int)$r['duration_days'],
        'status' => htmlspecialchars($r['status']),
        'date_started' => date('Y-m-d', strtotime($r['created_at'])),
        'maturity_date' => $r['maturity_date'] ? date('Y-m-d', strtotime($r['maturity_date'])) : 'N/A'
    ], $rows);
    
    return [
        'investments' => $formatted,
        'current_page' => $page,
        'total_pages' => $totalPages
    ];
}

// --- POST / Management Handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
    $action = strtolower(trim($input['action'] ?? ''));

    if ($action === 'add_plan' || $action === 'edit_plan') {
        $title = trim($input['title'] ?? '');
        $min_amount = (float)($input['min_amount'] ?? 0);
        $max_amount = (float)($input['max_amount'] ?? 0);
        $roi_min = (float)($input['roi_min'] ?? 0); 
        $roi_max = (float)($input['roi_max'] ?? 0); 
        $duration = (int)($input['duration'] ?? 0);
        $risk = trim($input['risk'] ?? 'low');
        // $status = trim($input['status'] ?? 'active'); // Removed due to missing DDL column
        $id = (int)($input['id'] ?? 0); 
        
        // --- Required fields validation ---
        if (empty($title) || $duration <= 0 || $min_amount <= 0 || $roi_min <= 0 || $roi_max <= 0 || $roi_min > $roi_max || $min_amount > $max_amount) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing required data (Title, Duration, valid min/max amounts/ROI).']);
            exit;
        }

        // Calculate the center/base ROI for the investments table
        $base_roi = round(($roi_min + $roi_max) / 2, 2);

        // Required placeholders/defaults for NOT NULL fields not in the modal
        $description = $input['description'] ?? 'Investment plan description.';
        $details = $input['details'] ?? 'Detailed plan features.';
        $income = $input['income'] ?? 'General investment returns.';
        $summary = $input['summary'] ?? 'Summary of the plan.';
        $icon = $input['icon'] ?? 'mdi:chart-line';
        $color = $input['color'] ?? 'Blue';
        $payout_option = $input['payout_option'] ?? 'maturity';
        
        try {
            if ($action === 'add_plan') {
                // FIX: Removed 'status' from INSERT query and params since it is missing in the DDL.
                $sql = "INSERT INTO investment_plans 
                        (title, roi_percent, duration_days, min_amount, max_amount, risk, description, details, income, summary, icon, color, payout_option) 
                        VALUES (:title, :roi_percent, :duration, :min_amount, :max_amount, :risk, :description, :details, :income, :summary, :icon, :color, :payout_option)";
                $params = [
                    ':title' => $title, 
                    ':roi_percent' => $base_roi, 
                    ':duration' => $duration, 
                    ':min_amount' => $min_amount, 
                    ':max_amount' => $max_amount, 
                    ':risk' => strtolower($risk), 
                    // ':status' => strtolower($status), // Removed
                    ':description' => $description,
                    ':details' => $details,
                    ':income' => $income,
                    ':summary' => $summary,
                    ':icon' => $icon,
                    ':color' => $color,
                    ':payout_option' => $payout_option
                ];
                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'New investment plan created successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create plan.']);
                }
            } elseif ($action === 'edit_plan') {
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid plan ID for edit.']);
                    exit;
                }

                // FIX: Removed 'status' from UPDATE query and params since it is missing in the DDL.
                $sql = "UPDATE investment_plans SET 
                            title = :title, 
                            roi_percent = :roi_percent, 
                            duration_days = :duration, 
                            min_amount = :min_amount, 
                            max_amount = :max_amount, 
                            risk = :risk 
                            -- status = :status (Removed)
                        WHERE id = :id";
                $params = [
                    ':title' => $title, 
                    ':roi_percent' => $base_roi, 
                    ':duration' => $duration, 
                    ':min_amount' => $min_amount, 
                    ':max_amount' => $max_amount, 
                    ':risk' => strtolower($risk), 
                    // ':status' => strtolower($status), // Removed
                    ':id' => $id
                ];

                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'Investment plan updated successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update plan.']);
                }
            }
        } catch (Exception $e) {
            error_log("Plan Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing plan request.']);
        }
    } elseif ($action === 'edit_investment') {
        $inv_id = (int)($input['id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        $roi_percent = (float)($input['roi_percent'] ?? 0);
        $status = trim($input['status'] ?? '');
        
        if ($inv_id <= 0 || empty($status)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid investment ID or missing status.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // 1. Fetch current investment details, especially user_id and old status
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

            // 2. Update investment record
            $update_sql = "UPDATE investments SET amount = :amount, roi_percent = :roi_percent, status = :status WHERE id = :id";
            $update_params = [
                ':amount' => number_format($amount, 2, '.', ''), 
                ':roi_percent' => number_format($roi_percent, 2, '.', ''), 
                ':status' => $status, 
                ':id' => $inv_id
            ];
            executeQuery($pdo, $update_sql, $update_params);

            // 3. Handle status change and wallet update (if needed)

            // Case A: Investment manually marked as 'completed'
            if ($status === 'completed' && $old_status !== 'completed') {
                // Calculate final ROI (either the existing roi_earned or the max potential)
                $final_roi_earned = (float)$inv['roi_earned'] ?: round($amount * $roi_percent / 100, 2);
                $total_payout = round($amount + $final_roi_earned, 2);

                // Update investments with final calculated ROI
                executeQuery($pdo, "UPDATE investments SET roi_earned = :roi WHERE id = :id", [':roi' => $final_roi_earned, ':id' => $inv_id]);

                // Credit user wallet (principal + ROI) and update total earnings
                executeQuery($pdo, "UPDATE wallets SET balance = balance + :payout, total_earnings = total_earnings + :roi WHERE user_id = :user_id", 
                             [':payout' => $total_payout, ':roi' => $final_roi_earned, ':user_id' => $user_id]);

                // Create transaction record
                $reference = 'ADMIN-PAYOUT-' . uniqid();
                $details = json_encode(['investment_id' => $inv_id, 'admin_action' => 'manual_completion', 'payout' => $total_payout, 'roi' => $final_roi_earned]);
                executeQuery($pdo, "INSERT INTO transactions (user_id, type, amount, reference, status, details, method, created_at)
                                    VALUES (?, 'investment_payout', ?, ?, 'completed', ?, 'system', NOW())",
                                    [$user_id, $total_payout, $reference, $details]);
                
            } 
            // Case B: Investment manually marked as 'cancelled' (only applies if it was previously 'active')
            elseif ($status === 'cancelled' && $old_status === 'active') {
                // Refund principal amount to user wallet
                executeQuery($pdo, "UPDATE wallets SET balance = balance + :amount, total_investments = total_investments - :amount WHERE user_id = :user_id", 
                             [':amount' => $old_amount, ':user_id' => $user_id]);

                // Create transaction record for the refund
                $reference = 'ADMIN-REFUND-' . uniqid();
                $details = json_encode(['investment_id' => $inv_id, 'admin_action' => 'manual_cancellation', 'refund_amount' => $old_amount]);
                executeQuery($pdo, "INSERT INTO transactions (user_id, type, amount, reference, status, details, method, created_at)
                                    VALUES (?, 'investment_refund', ?, ?, 'completed', ?, 'system', NOW())",
                                    [$user_id, $old_amount, $reference, $details]);
            }
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => "Investment ID {$inv_id} updated successfully. Wallet adjusted for status change from {$old_status} to {$status}."]);

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


// --- GET Requests (Initial Load, Search, Filter, Single Data Fetch) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $input = array_merge($_GET, $_POST); 
    $search = trim($input['search'] ?? '');
    $active_page = max(1, (int)($input['active_page'] ?? 1));
    $per_page = 10; 

    // Case 1: Fetch a single plan's details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'plan_details') {
        $plan_id = (int)($input['id'] ?? 0);
        // FIX: Removed 'status' from SELECT as it's missing from DDL
        $stmt = executeQuery($pdo, "SELECT id, title, roi_percent, duration_days, min_amount, max_amount, risk FROM investment_plans WHERE id = :id", [':id' => $plan_id]);
        $plan = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($plan) {
            $base_roi = (float)$plan['roi_percent'];
            // Re-calculate the display min/max based on the base ROI stored in the DB (for consistency)
            $roi_min = number_format($base_roi * 0.9, 2);
            $roi_max = number_format($base_roi * 1.1, 2);
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$plan['id'],
                    'title' => htmlspecialchars($plan['title']),
                    'min_amount' => (string)number_format((float)$plan['min_amount'], 2, '.', ''),
                    'max_amount' => (string)number_format((float)$plan['max_amount'], 2, '.', ''),
                    'roi_min' => $roi_min,
                    'roi_max' => $roi_max,
                    'duration_days' => (int)$plan['duration_days'],
                    'risk' => htmlspecialchars($plan['risk']),
                    // Hardcoded status for the modal form field value
                    'status' => 'active', 
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Plan not found.']);
        }
        exit;
    }
    
    // Case 2: Fetch a single active investment's details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'investment_details') {
        $inv_id = (int)($input['id'] ?? 0);
        $stmt = executeQuery($pdo, "SELECT i.id, i.plan_name, i.amount, i.roi_percent, i.status, COALESCE(u.full_name, u.name) AS user_name, u.email AS user_email FROM investments i JOIN users u ON i.user_id = u.id WHERE i.id = :id", [':id' => $inv_id]);
        $inv = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($inv) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$inv['id'],
                    'user_display' => htmlspecialchars($inv['user_name']) . ' (' . htmlspecialchars($inv['user_email']) . ')',
                    'plan_name' => htmlspecialchars($inv['plan_name']),
                    'amount' => (string)number_format((float)$inv['amount'], 2, '.', ''),
                    'roi_percent' => (string)number_format((float)$inv['roi_percent'], 2, '.', ''),
                    'status' => htmlspecialchars($inv['status']),
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Investment not found.']);
        }
        exit;
    }

    // Case 3: Main Dashboard Data Fetch (load_dashboard)
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
            'active_total_pages' => $active_investments['total_pages']
        ]
    ]);
    exit;
}

// Default response if no action matched
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
exit;
?>