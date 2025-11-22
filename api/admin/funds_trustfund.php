<?php
// FILE: /api/admin/funds_trustfund.php
// ============================================================
// PURPOSE: Manage TrustFund Schemes and Active TrustFund Accounts (Admin View)
// Handles: Metrics, Plan CRUD, Active TrustFund List (Paginated)
// ============================================================

session_start();
header('Content-Type: application/json');

// Ensure only authenticated admins can access this script
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// NOTE: Assuming '../../config/database.php' contains the getPDO() function
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
        error_log("Database Error in admin/funds_trustfund.php: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

// --- Metric Fetcher ---
function fetchTrustFundMetrics($pdo) {
    $metrics = [
        'total_trustfund' => 0.00,
        'trustfund_users' => 0,
        'total_contributions' => 0.00,
        'next_payout' => '—',
    ];

    try {
        // Total TrustFund Balance (Active investments)
        $stmt = executeQuery($pdo, "
            SELECT 
                COALESCE(SUM(amount), 0) AS total_active_balance,
                COUNT(DISTINCT user_id) AS active_users,
                MIN(CASE WHEN status = 'active' AND maturity_date >= CURDATE() THEN maturity_date ELSE NULL END) AS next_payout 
            FROM trustfund
            WHERE status = 'active'
        ");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($row) {
            $metrics['total_trustfund'] = (float)$row['total_active_balance'];
            $metrics['trustfund_users'] = (int)$row['active_users'];
            $metrics['next_payout'] = $row['next_payout'] ? date('M d, Y', strtotime($row['next_payout'])) : '—';
        }
        
        // Total Contributions (Total amount ever put into trust funds)
        $metrics['total_contributions'] = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM trustfund")->fetchColumn() ?? 0.00;

    } catch (PDOException $e) {
        error_log("TrustFund Metric Fetch Error: " . $e->getMessage());
    }

    return $metrics;
}

// --- TrustFund Plans Fetcher ---
function fetchTrustFundSchemes($pdo) {
    $sql = "SELECT 
                id, name, min_amount, max_amount, duration_days, roi_percent
            FROM trustfund_plans 
            ORDER BY id ASC";

    $stmt = executeQuery($pdo, $sql);
    $plans = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    return array_map(function($p) {
        $days = (int)$p['duration_days'];
        $duration_display = $days > 0 ? floor($days / 30) . ' Months' : 'N/A';
        
        return [
            'id' => (int)$p['id'],
            'name' => htmlspecialchars($p['name']),
            'duration_days' => $days,
            'duration_display' => $duration_display,
            'min_amount' => (float)$p['min_amount'],
            // Frontend assumes Target Amount is max_amount
            'max_amount' => (float)$p['max_amount'], 
            'roi_percent' => (float)$p['roi_percent'], 
            'status' => 'active', // TEMPORARY: Hardcoded active status for display
        ];
    }, $plans);
}

// --- Active TrustFund Accounts Fetcher (Paginated) ---
function fetchActiveTrustFunds($pdo, $page = 1, $perPage = 10, $search = '') {
    
    // NOTE: The 'trustfund' table does not have a 'target_amount' field,
    // so we'll use the user's initial 'amount' as the contribution value here.
    $sql = "FROM trustfund t
            JOIN users u ON t.user_id = u.id
            WHERE 1"; 
    $params = [];
    
    // Apply search
    if (!empty($search)) {
        $searchWild = "%$search%";
        // Search by user name, user email, or plan name
        $sql .= " AND (u.full_name LIKE :s OR u.email LIKE :s OR t.plan_name LIKE :s)";
        $params[':s'] = $searchWild;
    }

    // 1. Count Total Records
    $countStmt = executeQuery($pdo, "SELECT COUNT(t.id) " . $sql, $params);
    $total = $countStmt ? (int)$countStmt->fetchColumn() : 0;

    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $limitSql = " LIMIT " . $perPage . " OFFSET " . $offset;

    // 2. Fetch TrustFund Entries (Paginated)
    $dataSql = "SELECT 
                    t.id,
                    t.plan_name,
                    t.amount,
                    t.status,
                    t.maturity_date,
                    COALESCE(u.full_name, u.name) AS user_name,
                    u.email AS user_email
                " . $sql . " 
                ORDER BY t.created_at DESC" . $limitSql;

    $stmt = executeQuery($pdo, $dataSql, $params);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    $formatted = array_map(fn($r) => [
        'id' => (int)$r['id'],
        'user_name' => htmlspecialchars($r['user_name']),
        'user_email' => htmlspecialchars($r['user_email']),
        'plan_name' => htmlspecialchars($r['plan_name'] ?: 'N/A'),
        'amount' => (float)number_format((float)$r['amount'], 2, '.', ''), // Current Contribution
        'target' => 0.00, // Placeholder: Trustfund table does not track target.
        'status' => htmlspecialchars($r['status']),
        'maturity_date' => $r['maturity_date'] ? date('Y-m-d', strtotime($r['maturity_date'])) : 'N/A'
    ], $rows);
    
    return [
        'trustfunds' => $formatted,
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
        $duration_months = (int)($input['duration'] ?? 0);
        $min_amount = (float)($input['min_amount'] ?? 0);
        $max_amount = (float)($input['max_amount'] ?? 0);
        // $status = trim($input['status'] ?? 'active'); // Removed due to missing DDL column
        $id = (int)($input['id'] ?? 0); 
        
        // --- Required fields validation ---
        if (empty($name) || $duration_months <= 0 || $min_amount <= 0 || $max_amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing required data (Name, Duration, Min/Max amount).']);
            exit;
        }

        // Convert months to days (approximate)
        $duration_days = $duration_months * 30;

        // Generate placeholders for required NOT NULL fields in trustfund_plans DDL
        // NOTE: The form only provides name, duration, min_amount, max_amount. Defaulting others.
        $purpose = 'TrustFund Scheme purpose.';
        $roi_percent = 25.00; // Default ROI
        $risk = 'low';
        $payout_option = 'maturity';
        $summary = 'A TrustFund scheme.';
        $icon = 'mdi:shield-check-outline';
        $color = 'green';
        
        try {
            if ($action === 'add_plan') {
                $sql = "INSERT INTO trustfund_plans 
                        (name, purpose, min_amount, max_amount, duration_days, roi_percent, risk, payout_option, summary, icon, color) 
                        VALUES (:name, :purpose, :min_amount, :max_amount, :duration_days, :roi_percent, :risk, :payout_option, :summary, :icon, :color)";
                $params = [
                    ':name' => $name, 
                    ':purpose' => $purpose, 
                    ':min_amount' => number_format($min_amount, 2, '.', ''), 
                    ':max_amount' => number_format($max_amount, 2, '.', ''), 
                    ':duration_days' => $duration_days,
                    ':roi_percent' => number_format($roi_percent, 2, '.', ''),
                    ':risk' => $risk,
                    ':payout_option' => $payout_option,
                    ':summary' => $summary,
                    ':icon' => $icon,
                    ':color' => $color,
                ];
                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'New TrustFund scheme created successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create TrustFund scheme.']);
                }
            } elseif ($action === 'edit_plan') {
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid plan ID for edit.']);
                    exit;
                }

                $sql = "UPDATE trustfund_plans SET 
                            name = :name, 
                            min_amount = :min_amount, 
                            max_amount = :max_amount, 
                            duration_days = :duration_days 
                        WHERE id = :id";
                $params = [
                    ':name' => $name, 
                    ':min_amount' => number_format($min_amount, 2, '.', ''), 
                    ':max_amount' => number_format($max_amount, 2, '.', ''), 
                    ':duration_days' => $duration_days,
                    ':id' => $id
                ];

                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'TrustFund scheme updated successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update scheme.']);
                }
            }
        } catch (Exception $e) {
            error_log("TrustFund Plan Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing TrustFund scheme request.']);
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
        $stmt = executeQuery($pdo, "SELECT id, name, min_amount, max_amount, duration_days FROM trustfund_plans WHERE id = :id", [':id' => $plan_id]);
        $plan = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($plan) {
            $duration_months = floor((int)$plan['duration_days'] / 30);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$plan['id'],
                    'name' => htmlspecialchars($plan['name']),
                    // Max amount is the 'Target Amount' for the frontend form
                    'max_amount' => (string)number_format((float)$plan['max_amount'], 2, '.', ''), 
                    // Min amount is the 'Min Monthly Contribution' for the frontend form (using the DB field as min contribution)
                    'min_amount' => (string)number_format((float)$plan['min_amount'], 2, '.', ''), 
                    'duration_months' => $duration_months,
                    'status' => 'active', 
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'TrustFund Scheme not found.']);
        }
        exit;
    }
    
    // Case 2: Main Dashboard Data Fetch (load_dashboard)
    $metrics = fetchTrustFundMetrics($pdo);
    $plans = fetchTrustFundSchemes($pdo);
    $active_trustfund = fetchActiveTrustFunds($pdo, $active_page, $per_page, $search);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'metrics' => $metrics,
            'plans' => $plans,
            'active_trustfund' => $active_trustfund['trustfunds'],
            'active_page' => $active_trustfund['current_page'],
            'active_total_pages' => $active_trustfund['total_pages']
        ]
    ]);
    exit;
}

// Default response if no action matched
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
exit;
?>