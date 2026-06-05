<?php
// ============================================================
// FILE: /api/admin/funds_xshares.php
// PURPOSE: Admin controller for X-Shares assets & holdings overview
// ACTIONS: add_asset, edit_asset, toggle_asset, get_holdings
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

$VALID_SCHEDULES = ['weekly', 'monthly', 'quarterly', 'maturity'];


// --------------------- ACTION: add_asset ---------------------
if ($action === 'add_asset') {
    $asset_name      = trim((string)($input['asset_name'] ?? ''));
    $ticker          = trim((string)($input['ticker'] ?? ''));
    $company         = trim((string)($input['company'] ?? ''));
    $current_price   = array_key_exists('current_price', $input) && $input['current_price'] !== '' && $input['current_price'] !== null
                       ? (float) $input['current_price'] : null;
    $roi_percent     = (float) ($input['roi_percent'] ?? 0);
    $payout_schedule = $input['payout_schedule'] ?? 'monthly';
    $duration_days   = array_key_exists('duration_days', $input) && $input['duration_days'] !== '' && $input['duration_days'] !== null
                       ? (int) $input['duration_days'] : null;
    $min_amount      = (float) ($input['min_amount'] ?? 100);
    $description     = trim((string)($input['description'] ?? ''));
    $status          = $input['status'] ?? 'active';

    if ($asset_name === '') jsonOut('error', 'Asset name is required.');
    if ($ticker === '')     jsonOut('error', 'Ticker is required.');
    if ($company === '')    jsonOut('error', 'Company is required.');
    if ($roi_percent <= 0)  jsonOut('error', 'ROI must be greater than zero.');
    if ($min_amount <= 0)   jsonOut('error', 'Minimum investment must be greater than zero.');
    if (!in_array($payout_schedule, $VALID_SCHEDULES, true)) jsonOut('error', 'Invalid payout schedule.');
    if (!in_array($status, ['active', 'inactive'], true))     jsonOut('error', 'Invalid status.');

    try {
        $pdo->prepare("INSERT INTO xshares_assets
            (asset_name, ticker, company, current_price, roi_percent, payout_schedule,
             duration_days, min_amount, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())")
            ->execute([
                $asset_name, $ticker, $company, $current_price, $roi_percent, $payout_schedule,
                $duration_days, $min_amount, $description, $status,
            ]);

        jsonOut('success', 'X-Shares asset added.', ['asset_id' => (int) $pdo->lastInsertId()]);
    } catch (Exception $e) {
        error_log('admin funds_xshares add_asset: ' . $e->getMessage());
        jsonOut('error', 'Failed to add asset.');
    }
}


// --------------------- ACTION: edit_asset ---------------------
if ($action === 'edit_asset') {
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) jsonOut('error', 'Invalid asset id.');

    $allowed = ['asset_name', 'ticker', 'company', 'current_price', 'roi_percent',
                'payout_schedule', 'duration_days', 'min_amount', 'description', 'status'];
    $sets = [];
    $params = [];

    foreach ($allowed as $field) {
        if (!array_key_exists($field, $input)) continue;

        if (in_array($field, ['current_price', 'duration_days'], true)) {
            $raw = $input[$field];
            $val = ($raw === '' || $raw === null) ? null
                 : ($field === 'duration_days' ? (int) $raw : (float) $raw);
        } elseif (in_array($field, ['roi_percent', 'min_amount'], true)) {
            $val = (float) $input[$field];
        } else {
            $val = $input[$field] === null ? null : (string) $input[$field];
        }

        if ($field === 'payout_schedule' && !in_array($val, $VALID_SCHEDULES, true)) {
            jsonOut('error', 'Invalid payout schedule.');
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
        $stmt = $pdo->prepare("UPDATE xshares_assets SET " . implode(', ', $sets) . " WHERE id = ?");
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) jsonOut('error', 'Asset not found or nothing changed.');
        jsonOut('success', 'Asset updated.', ['asset_id' => $id]);
    } catch (Exception $e) {
        error_log('admin funds_xshares edit_asset: ' . $e->getMessage());
        jsonOut('error', 'Failed to update asset.');
    }
}


// --------------------- ACTION: toggle_asset ---------------------
if ($action === 'toggle_asset') {
    $id     = (int) ($input['id'] ?? 0);
    $status = $input['status'] ?? '';
    if ($id <= 0) jsonOut('error', 'Invalid asset id.');
    if (!in_array($status, ['active', 'inactive'], true)) jsonOut('error', 'Invalid status.');

    try {
        $stmt = $pdo->prepare("UPDATE xshares_assets SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        if ($stmt->rowCount() === 0) jsonOut('error', 'Asset not found.');
        jsonOut('success', 'Asset status updated.', ['asset_id' => $id, 'status' => $status]);
    } catch (Exception $e) {
        error_log('admin funds_xshares toggle_asset: ' . $e->getMessage());
        jsonOut('error', 'Failed to update asset status.');
    }
}


// --------------------- ACTION: get_holdings ---------------------
if ($action === 'get_holdings') {
    $status_filter = $input['status'] ?? null;

    try {
        $sql = "SELECT xshares_holdings.*,
                       users.full_name AS user_name, users.email AS user_email,
                       xshares_assets.asset_name, xshares_assets.ticker, xshares_assets.company,
                       xshares_assets.current_price, xshares_assets.payout_schedule
                FROM xshares_holdings
                JOIN users          ON xshares_holdings.user_id  = users.id
                JOIN xshares_assets ON xshares_holdings.asset_id = xshares_assets.id";
        $params = [];
        if ($status_filter !== null && $status_filter !== '' && $status_filter !== 'all') {
            $sql .= " WHERE xshares_holdings.status = ?";
            $params[] = $status_filter;
        }
        $sql .= " ORDER BY xshares_holdings.started_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonOut('success', 'Holdings loaded.', ['holdings' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('admin funds_xshares get_holdings: ' . $e->getMessage());
        jsonOut('error', 'Failed to load holdings.');
    }
}


// --------------------- ACTION: get_assets (admin) ---------------------
if ($action === 'get_assets') {
    try {
        $stmt = $pdo->query("SELECT * FROM xshares_assets ORDER BY status DESC, asset_name ASC");
        jsonOut('success', 'Assets loaded.', ['assets' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('admin funds_xshares get_assets: ' . $e->getMessage());
        jsonOut('error', 'Failed to load assets.');
    }
}


// --------------------- ACTION: get_metrics ---------------------
if ($action === 'get_metrics') {
    try {
        $row = $pdo->query("SELECT
            COALESCE(SUM(CASE WHEN status='active' THEN amount END), 0) AS total_invested,
            COALESCE(SUM(roi_earned), 0)                                AS total_paid,
            COUNT(DISTINCT user_id)                                     AS total_members,
            COUNT(CASE WHEN status='active'   THEN 1 END)               AS active_holdings,
            COUNT(CASE WHEN status='matured'  THEN 1 END)               AS matured_holdings
            FROM xshares_holdings")->fetch(PDO::FETCH_ASSOC) ?: [];

        $nextMaturity = $pdo->query("SELECT MIN(maturity_date) FROM xshares_holdings WHERE status='active' AND maturity_date >= CURDATE()")->fetchColumn();

        jsonOut('success', 'Metrics loaded.', ['metrics' => [
            'total_invested'   => round((float)($row['total_invested'] ?? 0), 2),
            'total_paid'       => round((float)($row['total_paid'] ?? 0), 2),
            'active_holdings'  => (int)($row['active_holdings'] ?? 0),
            'matured_holdings' => (int)($row['matured_holdings'] ?? 0),
            'total_members'    => (int)($row['total_members'] ?? 0),
            'next_maturity'    => $nextMaturity ? date('M d, Y', strtotime($nextMaturity)) : '—',
        ]]);
    } catch (Exception $e) {
        error_log('admin funds_xshares get_metrics: ' . $e->getMessage());
        jsonOut('error', 'Failed to load metrics.');
    }
}


// default
http_response_code(400);
jsonOut('error', 'Invalid action.');
