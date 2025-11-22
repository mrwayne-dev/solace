<?php
// D:\mrwayne\web_dev\healthruncare\api\admin\donations.php
// ============================================================
// PURPOSE: Manage Donations & Charity Campaigns (Admin View)
// Handles: Metrics, Campaign CRUD (Add/Edit), Campaign List, Donation History List
// ============================================================

session_start();
header('Content-Type: application/json');

// Ensure only authenticated admins can access this script
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// Assumes 'database.php' contains getPDO() and executeQuery() or similar helper
require_once '../../config/database.php';

$pdo = getPDO();

/**
 * Helper function to execute a prepared statement and return result set.
 */
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database Error in admin/donations.php: " . $e->getMessage());
        return false;
    }
}

// --- Metric Fetcher ---
function fetchDonationMetrics($pdo) {
    $metrics = [
        'total_campaigns' => 0,
        'active_campaigns' => 0,
        'total_raised' => 0.00,
        'top_campaign_name' => 'None',
        'top_campaign_amount' => 0.00
    ];

    try {
        // Total Campaigns
        $metrics['total_campaigns'] = $pdo->query("SELECT COUNT(id) FROM charities")->fetchColumn() ?? 0;
        
        // Active Campaigns
        $metrics['active_campaigns'] = $pdo->query("SELECT COUNT(id) FROM charities WHERE status = 'active'")->fetchColumn() ?? 0;

        // Total Raised
        $metrics['total_raised'] = $pdo->query("SELECT SUM(raised_amount) FROM charities")->fetchColumn() ?? 0.00;

        // Top Campaign
        $stmt = $pdo->query("SELECT name, raised_amount FROM charities ORDER BY raised_amount DESC LIMIT 1");
        $top = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($top) {
            $metrics['top_campaign_name'] = htmlspecialchars($top['name']);
            $metrics['top_campaign_amount'] = (float)$top['raised_amount'];
        }

    } catch (PDOException $e) {
        error_log("Donation Metric Fetch Error: " . $e->getMessage());
    }

    return $metrics;
}

// --- Campaign Fetcher ---
function fetchCampaigns($pdo, $search = '', $filter = 'all') {
    $sql = "SELECT 
                id, name, description, image, goal_amount, raised_amount, status 
            FROM charities 
            WHERE 1";
    $params = [];

    // Apply status filter
    if ($filter !== 'all') {
        // Handle "archived" filter explicitly, otherwise treat others (active, inactive) as is
        $sql .= " AND status = :status";
        $params[':status'] = $filter;
    }
    
    // Apply search
    if (!empty($search)) {
        $searchWild = "%$search%";
        $sql .= " AND (name LIKE :s OR description LIKE :s)";
        // Re-use search parameter for both fields
        $params[':s'] = $searchWild;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = executeQuery($pdo, $sql, $params);
    $campaigns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Format numeric values as strings and calculate progress
    return array_map(function($c) {
        $raised = (float)$c['raised_amount'];
        $goal = (float)$c['goal_amount'];
        $progress = ($goal > 0) ? round(($raised / $goal) * 100) : 0;
        return [
            'id' => (int)$c['id'],
            'name' => htmlspecialchars($c['name']),
            'description' => htmlspecialchars($c['description']),
            'image' => htmlspecialchars($c['image']),
            'goal_amount' => (string)number_format($goal, 2, '.', ''),
            'raised_amount' => (string)number_format($raised, 2, '.', ''),
            'progress' => $progress, // Integer percentage
            'status' => $c['status']
        ];
    }, $campaigns);
}

// --- Donations Fetcher (Paginated) ---
function fetchDonations($pdo, $page = 1, $perPage = 10, $search = '') {
    
    $sql = "FROM charity_donations cd
            JOIN users u ON cd.user_id = u.id
            JOIN charities c ON cd.charity_id = c.id
            WHERE 1";
    $params = [];
    
    // Apply search
    if (!empty($search)) {
        $searchWild = "%$search%";
        // Search by donor name, donor email, or campaign name
        $sql .= " AND (u.full_name LIKE :s OR u.email LIKE :s OR c.name LIKE :s)";
        $params[':s'] = $searchWild;
    }

    // 1. Count Total Records
    $countStmt = executeQuery($pdo, "SELECT COUNT(cd.id) " . $sql, $params);
    $total = $countStmt ? (int)$countStmt->fetchColumn() : 0;

    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    $limitSql = " LIMIT " . $perPage . " OFFSET " . $offset;

    // 2. Fetch Donations (Paginated)
    $dataSql = "SELECT 
                    cd.id,
                    cd.amount,
                    cd.created_at,
                    c.name AS campaign_name,
                    COALESCE(u.full_name, u.name) AS donor_name,
                    u.email AS donor_email
                " . $sql . " 
                ORDER BY cd.created_at DESC" . $limitSql;

    $stmt = executeQuery($pdo, $dataSql, $params);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    $formatted = array_map(fn($r) => [
        'id' => (int)$r['id'],
        'donor_name' => htmlspecialchars($r['donor_name']),
        'donor_email' => htmlspecialchars($r['donor_email']),
        'campaign_name' => htmlspecialchars($r['campaign_name']),
        'amount' => (string)number_format((float)$r['amount'], 2, '.', ''),
        'date' => date('Y-m-d H:i:s', strtotime($r['created_at'])), 
        'status' => 'Completed' // Donations from user wallet are instantly 'Completed'
    ], $rows);
    
    return [
        'donations' => $formatted,
        'current_page' => $page,
        'total_pages' => $totalPages
    ];
}


// --- POST / Campaign Management Handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = array_merge($_POST, $_FILES); // Use $_POST for non-file fields and $_FILES for files
    $action = strtolower($input['action'] ?? '');
    
    // Default image path
    $image_path = null; 
    
    // Simple image upload handler 
    if (isset($_FILES['campaign_image']) && $_FILES['campaign_image']['error'] === UPLOAD_ERR_OK) {
        // Define target directory and basic sanitation
        $target_dir = "../../assets/images/charity/";
        // Ensure directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES["campaign_image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid('camp_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["campaign_image"]["tmp_name"], $target_file)) {
            $image_path = "/assets/images/charity/" . $file_name;
        } else {
             // Log error if file move fails
             error_log("Failed to move uploaded file: " . $_FILES["campaign_image"]["error"]);
        }
    }


    if ($action === 'add_campaign' || $action === 'edit_campaign') {
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $goal = (float)($input['goal'] ?? 0);
        $raised = (float)($input['raised'] ?? 0); 
        $status = $input['status'] ?? 'active';
        $id = (int)($input['id'] ?? 0); // Only relevant for edit

        if (empty($name) || $goal <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Campaign name and goal amount (must be > 0) are required.']);
            exit;
        }
        
        try {
            if ($action === 'add_campaign') {
                $image_path_final = $image_path ?? '/assets/images/charity/placeholder.jpg';
                $sql = "INSERT INTO charities (name, description, image, goal_amount, raised_amount, status) 
                        VALUES (:name, :desc, :image, :goal, :raised, :status)";
                $params = [
                    ':name' => $name, 
                    ':desc' => $description, 
                    ':image' => $image_path_final, 
                    ':goal' => number_format($goal, 2, '.', ''), 
                    ':raised' => number_format($raised, 2, '.', ''), 
                    ':status' => $status
                ];
                $stmt = executeQuery($pdo, $sql, $params);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'New charity campaign created successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create campaign.']);
                }
            } elseif ($action === 'edit_campaign') {
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid campaign ID for edit.']);
                    exit;
                }

                $updateParams = [
                    ':name' => $name, 
                    ':description' => $description, 
                    ':goal_amount' => number_format($goal, 2, '.', ''), 
                    ':raised_amount' => number_format($raised, 2, '.', ''), 
                    ':status' => $status,
                    ':id' => $id
                ];

                $sql = "UPDATE charities SET 
                            name = :name, 
                            description = :description, 
                            goal_amount = :goal_amount, 
                            raised_amount = :raised_amount, 
                            status = :status";
                
                // Only update image if a new one was uploaded
                if ($image_path !== null) {
                    $sql .= ", image = :image";
                    $updateParams[':image'] = $image_path;
                }

                $sql .= " WHERE id = :id";

                $stmt = executeQuery($pdo, $sql, $updateParams);

                if ($stmt) {
                    echo json_encode(['status' => 'success', 'message' => 'Campaign updated successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update campaign.']);
                }
            }
        } catch (Exception $e) {
            error_log("Campaign Action Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error processing campaign request.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid POST action specified.']);
    }
    exit;
}


// --- GET Requests (Initial Load, Search, Filter, Single Campaign Details) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $input = array_merge($_GET, $_POST); // Combine for GET convenience
    $search = trim($input['search'] ?? '');
    $filter = strtolower(trim($input['filter'] ?? 'all'));
    $donations_page = max(1, (int)($input['donations_page'] ?? 1));
    $per_page = 10; // Fixed items per page for donations table

    // Case 1: Fetch a single campaign's details for editing
    if (isset($input['fetch']) && $input['fetch'] === 'campaign_details') {
        $campaign_id = (int)($input['id'] ?? 0);
        $stmt = executeQuery($pdo, "SELECT id, name, description, image, goal_amount, raised_amount, status FROM charities WHERE id = :id", [':id' => $campaign_id]);
        $campaign = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if ($campaign) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => (int)$campaign['id'],
                    'name' => htmlspecialchars($campaign['name']),
                    'description' => htmlspecialchars($campaign['description']),
                    'image' => htmlspecialchars($campaign['image']),
                    'goal_amount' => (string)number_format((float)$campaign['goal_amount'], 2, '.', ''),
                    'raised_amount' => (string)number_format((float)$campaign['raised_amount'], 2, '.', ''),
                    'status' => $campaign['status']
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Campaign not found.']);
        }
        exit;
    }
    
    // Case 2: Main Dashboard Data Fetch 
    $metrics = fetchDonationMetrics($pdo);
    $campaigns = fetchCampaigns($pdo, $search, $filter);
    $donations = fetchDonations($pdo, $donations_page, $per_page, $search);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'metrics' => $metrics,
            'campaigns' => $campaigns,
            'donations' => $donations['donations'],
            'donations_page' => $donations['current_page'],
            'donations_total_pages' => $donations['total_pages']
        ]
    ]);
    exit;
}

// Default response if no action matched
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
exit;
?>