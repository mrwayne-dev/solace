<?php
// ========================================
// ADMIN DEPOSIT ADDRESSES — CRUD
// Manage the crypto wallet addresses users deposit to.
// GET  -> list all addresses
// POST -> {action: add|update|delete|toggle, ...}
// ========================================
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$pdo = getPDO();

function out($status, $message, $extra = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query("SELECT id, label, network, address, is_active,
                                DATE_FORMAT(created_at,'%Y-%m-%d %H:%i') AS created_at
                         FROM deposit_addresses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['is_active'] = (int)$r['is_active']; }
    out('success', 'Addresses retrieved.', ['data' => $rows]);
}

$data   = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $data['action'] ?? '';

switch ($action) {
    case 'add':
        $label   = trim($data['label'] ?? '');
        $network = trim($data['network'] ?? '');
        $address = trim($data['address'] ?? '');
        if ($label === '' || $network === '' || $address === '') {
            out('error', 'Label, network and address are all required.');
        }
        $stmt = $pdo->prepare("INSERT INTO deposit_addresses (label, network, address, is_active) VALUES (?,?,?,1)");
        $stmt->execute([$label, $network, $address]);
        out('success', 'Deposit address added.', ['id' => (int)$pdo->lastInsertId()]);

    case 'update':
        $id = (int)($data['id'] ?? 0);
        if (!$id) out('error', 'Missing address id.');
        $label   = trim($data['label'] ?? '');
        $network = trim($data['network'] ?? '');
        $address = trim($data['address'] ?? '');
        if ($label === '' || $network === '' || $address === '') {
            out('error', 'Label, network and address are all required.');
        }
        $stmt = $pdo->prepare("UPDATE deposit_addresses SET label=?, network=?, address=? WHERE id=?");
        $stmt->execute([$label, $network, $address, $id]);
        out('success', 'Deposit address updated.');

    case 'toggle':
        $id = (int)($data['id'] ?? 0);
        if (!$id) out('error', 'Missing address id.');
        $stmt = $pdo->prepare("UPDATE deposit_addresses SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([$id]);
        out('success', 'Address status toggled.');

    case 'delete':
        $id = (int)($data['id'] ?? 0);
        if (!$id) out('error', 'Missing address id.');
        $stmt = $pdo->prepare("DELETE FROM deposit_addresses WHERE id = ?");
        $stmt->execute([$id]);
        out('success', 'Deposit address deleted.');

    default:
        out('error', 'Invalid action.');
}
