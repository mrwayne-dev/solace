<?php
// ===============================================
// FILE: /api/backend/charity.php
// PURPOSE: Charity dashboard — summary cards, campaigns, donation processing, history.
// Supports SPA requests (fetch/AJAX). Integrates with wallets/transactions.
// ===============================================

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set true on HTTPS
    'cookie_samesite' => 'Strict',
]);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Restrict for production
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ---------------------------
// Include dependencies
// ---------------------------
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/email.php';


// ---------------------------
// Auth check
// ---------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? ($_SESSION['name'] ?? 'User');
$user_email = $_SESSION['email'] ?? '';

try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// ---------------------------
// Parse input
// ---------------------------
$input = json_decode(file_get_contents('php://input'), true) ?: $_GET;
$action = trim($input['action'] ?? 'get_summary');

// Helper: JSON response + exit
function jsonResponse($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// Helper: Generate reference
function generateReference($prefix) {
    return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
}

// ===========================================================
// ACTION: GET SUMMARY (Cards: Total Donated, Count, Top Charity, Last Date)
// ===========================================================
if ($action === 'get_summary') {
    try {
        // Total donated & count from charity_donations
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(amount), 0) AS total_donated,
                COUNT(*) AS donations_made,
                MAX(created_at) AS last_donation_date
            FROM charity_donations 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_donated' => 0,
            'donations_made' => 0,
            'last_donation_date' => null
        ];

        // Top supported charity (highest sum amount)
        $topStmt = $pdo->prepare("
            SELECT c.name AS top_charity
            FROM charity_donations cd
            JOIN charities c ON cd.charity_id = c.id
            WHERE cd.user_id = ?
            GROUP BY c.id, c.name
            ORDER BY SUM(cd.amount) DESC
            LIMIT 1
        ");
        $topStmt->execute([$user_id]);
        $topCharity = $topStmt->fetchColumn() ?: 'None';

        // Format values clearly
        $totalDonated = (float) $summary['total_donated'];
        $donationsMade = (int) $summary['donations_made'];
        $lastDate = $summary['last_donation_date'] 
            ? date('M d, Y', strtotime($summary['last_donation_date'])) 
            : 'None';

        jsonResponse('success', 'Summary loaded.', [
            // ✅ clean numeric for frontend formatting
            'total_donated' => $totalDonated,
            'donations_made' => $donationsMade,
            'top_charity' => $topCharity,
            'last_donation_date' => $lastDate
        ]);

    } catch (Exception $e) {
        error_log('Error fetching charity summary: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load summary.');
    }
}


// ===========================================================
// ACTION: GET ACTIVE CAMPAIGNS
// ===========================================================
if ($action === 'get_campaigns') {
    $stmt = $pdo->prepare("
        SELECT id, name, organization, COALESCE(description, '') AS description, COALESCE(image, '/assets/images/charity/placeholder.jpg') AS image,
               goal_amount AS goal, raised_amount AS raised, status,
               ROUND((raised_amount / goal_amount * 100), 0) AS progress
        FROM charities 
        WHERE status = 'active' AND goal_amount > raised_amount
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format
    foreach ($campaigns as &$c) {
        $c['raised_formatted'] = number_format((float) $c['raised'], 2);
        $c['goal_formatted'] = number_format((float) $c['goal'], 2);
        $c['progress'] = (int) $c['progress'];
    }

    jsonResponse('success', 'Campaigns loaded.', ['campaigns' => $campaigns]);
}

// ===========================================================
// ACTION: GET SINGLE CAMPAIGN (For details panel on select)
// ===========================================================
if ($action === 'get_single_campaign') {
    $id = (int) ($input['id'] ?? 0);
    if (!$id) jsonResponse('error', 'Invalid charity ID.');

    $stmt = $pdo->prepare("
        SELECT id, name, organization, COALESCE(description, '') AS description, COALESCE(image, '/assets/images/charity/placeholder.jpg') AS image,
               goal_amount AS goal, raised_amount AS raised, status,
               ROUND((raised_amount / goal_amount * 100), 0) AS progress
        FROM charities WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) jsonResponse('error', 'Charity not found or inactive.');

    $campaign['raised_formatted'] = number_format((float) $campaign['raised'], 2);
    $campaign['goal_formatted'] = number_format((float) $campaign['goal'], 2);
    $campaign['progress'] = (int) $campaign['progress'];

    jsonResponse('success', 'Campaign loaded.', ['campaign' => $campaign]);  // 'data' => $campaign for JS consistency
}

// ===========================================================
// ACTION: MAKE DONATION
// ===========================================================
if ($action === 'make_donation') {
    $charity_id = (int) ($input['charity_id'] ?? 0);
    $amount = (float) ($input['amount'] ?? 0);
    $note = trim($input['note'] ?? '');

    if ($amount <= 0 || $charity_id <= 0) {
        jsonResponse('error', 'Invalid donation details.');
    }

    // Validate charity exists and active
    $chkStmt = $pdo->prepare("SELECT name, goal_amount, raised_amount FROM charities WHERE id = ? AND status = 'active'");
    $chkStmt->execute([$charity_id]);
    $charity = $chkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$charity || $charity['raised_amount'] >= $charity['goal_amount']) {
        jsonResponse('error', 'Invalid or inactive charity.');
    }

    // Check wallet balance
    $walletStmt = $pdo->prepare("SELECT balance, total_donations FROM wallets WHERE user_id = ?");
    $walletStmt->execute([$user_id]);
    $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);
    if (!$wallet || $wallet['balance'] < $amount) {
        jsonResponse('error', 'Insufficient balance.');
    }

    $reference = generateReference('HRC-DON');
    $timestamp = date('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        // Deduct from balance & update total_donations
        $pdo->prepare("UPDATE wallets SET balance = balance - ?, total_donations = total_donations + ? WHERE user_id = ?")
            ->execute([$amount, $amount, $user_id]);

        // Add to charity raised_amount
        $pdo->prepare("UPDATE charities SET raised_amount = raised_amount + ? WHERE id = ?")
            ->execute([$amount, $charity_id]);

        // Insert donation (with reference)
        $pdo->prepare("
            INSERT INTO charity_donations (user_id, charity_id, amount, reference, created_at)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$user_id, $charity_id, $amount, $reference, $timestamp]);

        // Insert into transactions for reference/status/details
        $details = json_encode(['charity_id' => $charity_id, 'note' => $note]);
        $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, reference, status, details, created_at)
            VALUES (?, 'donation', ?, ?, 'completed', ?, ?)
        ")->execute([$user_id, $amount, $reference, $details, $timestamp]);

        // ===========================================================
// UPDATE USER IMPACT METRICS
// ===========================================================
$impactStmt = $pdo->prepare("SELECT * FROM user_impacts WHERE user_id = ?");
$impactStmt->execute([$user_id]);
$impact = $impactStmt->fetch(PDO::FETCH_ASSOC);

// Define increments (you can adjust multipliers later)
$peopleIncrement = floor($amount / 10); // e.g. $10 helps 1 person
$impactIncrement = min(100, ($amount / 100)); // e.g. $100 adds +1 impact point
$communityIncrement = ($amount >= 50) ? 1 : 0; // each $50+ helps 1 community
$packageIncrement = ceil($amount / 25); // every $25 funds one package

if ($impact) {
    $pdo->prepare("
        UPDATE user_impacts 
        SET 
            total_contributions = total_contributions + :amt,
            people_helped = people_helped + :ppl,
            impact_score = LEAST(100, impact_score + :impact),
            communities_helped = communities_helped + :comm,
            packages_funded = packages_funded + :pack
        WHERE user_id = :uid
    ")->execute([
        ':amt' => $amount,
        ':ppl' => $peopleIncrement,
        ':impact' => $impactIncrement,
        ':comm' => $communityIncrement,
        ':pack' => $packageIncrement,
        ':uid' => $user_id
    ]);
} else {
    $pdo->prepare("
        INSERT INTO user_impacts (user_id, total_contributions, people_helped, impact_score, communities_helped, packages_funded)
        VALUES (:uid, :amt, :ppl, :impact, :comm, :pack)
    ")->execute([
        ':uid' => $user_id,
        ':amt' => $amount,
        ':ppl' => $peopleIncrement,
        ':impact' => $impactIncrement,
        ':comm' => $communityIncrement,
        ':pack' => $packageIncrement
    ]);
}


        $pdo->commit();

        // Emails (conditional if sendEmail exists)
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'donation_confirmed',
                'variables' => [
                    'user_name' => $user_name,
                    'amount' => number_format($amount, 2),
                    'charity_name' => $charity['name'],
                    'reference' => $reference,
                ]
            ]);

            sendEmail([
                'to' => ADMIN_CONTACT_EMAIL,
                'template' => 'admin_donation_notification',
                'variables' => [
                    'user_name' => $user_name,
                    'user_email' => $user_email,
                    'amount' => number_format($amount, 2),
                    'charity_name' => $charity['name'],
                    'reference' => $reference,
                ]
            ]);
        }

        jsonResponse('success', 'Donation successful!', ['reference' => $reference]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Donation error: ' . $e->getMessage());
        jsonResponse('error', 'Donation failed. Please try again.');
    }
}

// ===========================================================
// ACTION: GET DONATION HISTORY (Table: Reference from transactions, join on reference)
// ===========================================================
if (in_array($action, ['get_history', 'history'])) {
    $page = max(1, (int) ($input['page'] ?? 1));
    $limit = min(50, max(5, (int) ($input['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    $search = trim($input['search'] ?? '');

    $sql = "
        SELECT t.reference, t.created_at, t.status, cd.amount, c.name AS charity_name
        FROM charity_donations cd
        JOIN transactions t ON t.reference = cd.reference
        JOIN charities c ON cd.charity_id = c.id
        WHERE cd.user_id = ?
    ";
    $params = [$user_id];

    if (!empty($search)) {
        $sql .= " AND (t.reference LIKE ? OR c.name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Count total
    $countSql = "SELECT COUNT(*) FROM ($sql) AS count_sub";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();
    $total_pages = ceil($total / $limit);

    // Fetch paginated
        // Fetch paginated (MySQL doesn't allow binding for LIMIT/OFFSET)
    $limit = (int)$limit;
    $offset = (int)$offset;
    // Append limit/offset directly into SQL string (safe due to integer casting)
    $sql .= " ORDER BY cd.created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format
    $history = [];
    foreach ($rows as $row) {
        $history[] = [
            'reference' => '#' . strtoupper(substr($row['reference'], strrpos($row['reference'], '-') + 1)),
            'date' => date('M d, Y', strtotime($row['created_at'])),
            'charity' => $row['charity_name'],
            'amount' => (float) $row['amount'],
            'status' => ucfirst($row['status']),
        ];
    }

    jsonResponse('success', 'History loaded.', [
        'history' => $history,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total,
        ],
    ]);
}

// Invalid action
http_response_code(400);
jsonResponse('error', 'Invalid action.');
?>