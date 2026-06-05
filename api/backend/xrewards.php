<?php
// ===============================================
// FILE: /api/backend/xrewards.php
// PURPOSE: X-Rewards controller for TitanXHoldings
// ACTIONS: get_products, get_orders, place_order, cancel_order
// ===============================================

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => false, // set true in production with HTTPS
    'cookie_samesite' => 'Strict',
]);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // tighten in production
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// includes
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/email.php'; // uses sendEmail()

// auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}
$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? ($_SESSION['name'] ?? 'User');
$user_email = $_SESSION['email'] ?? '';

// get pdo
try {
    $pdo = getPDO();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// parse input
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
$action = trim($input['action'] ?? 'get_products');

// helper responses (guarded — see xweekly.php for rationale)
if (!function_exists('jsonResponse')) {
    function jsonResponse($status, $message, $data = []) {
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }
}

if (!function_exists('generateReference')) {
    function generateReference($prefix = 'TXH-ORD') {
        return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
    }
}


// --------------------- ACTION: get_products ---------------------
if ($action === 'get_products') {
    try {
        $stmt = $pdo->query("SELECT id, product_name, description, retail_price, reward_price, discount_pct, image_path, stock, status, created_at
            FROM xrewards_products WHERE status = 'active' ORDER BY product_name ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', 'Products loaded.', ['products' => $rows]);
    } catch (Exception $e) {
        error_log('xrewards get_products error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load products.');
    }
}


// --------------------- ACTION: get_orders ---------------------
if ($action === 'get_orders') {
    try {
        $stmt = $pdo->prepare("SELECT xrewards_orders.*,
                xrewards_products.product_name, xrewards_products.image_path
            FROM xrewards_orders
            JOIN xrewards_products ON xrewards_orders.product_id = xrewards_products.id
            WHERE xrewards_orders.user_id = ?
            ORDER BY xrewards_orders.ordered_at DESC");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', 'Orders loaded.', ['orders' => $rows]);
    } catch (Exception $e) {
        error_log('xrewards get_orders error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to load orders.');
    }
}


// --------------------- ACTION: place_order ---------------------
if ($action === 'place_order') {
    $product_id = (int) ($input['product_id'] ?? 0);
    $quantity   = (int) ($input['quantity'] ?? 1);
    $shipping_details = trim((string)($input['shipping_details'] ?? ''));

    if ($product_id <= 0) jsonResponse('error', 'Invalid product selected.');
    if ($quantity <= 0)   jsonResponse('error', 'Quantity must be at least 1.');
    if ($shipping_details === '') jsonResponse('error', 'Shipping details are required.');

    try {
        $pdo->beginTransaction();

        // lock product row to check/decrement stock atomically
        $pstmt = $pdo->prepare("SELECT id, product_name, reward_price, stock, status
            FROM xrewards_products WHERE id = ? FOR UPDATE");
        $pstmt->execute([$product_id]);
        $product = $pstmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['status'] !== 'active') {
            $pdo->rollBack();
            jsonResponse('error', 'Product not available.');
        }

        // stock check (NULL stock means unlimited)
        $tracks_stock = $product['stock'] !== null;
        if ($tracks_stock && (int)$product['stock'] < $quantity) {
            $pdo->rollBack();
            jsonResponse('error', 'Insufficient stock for the requested quantity.');
        }

        $unit_price  = (float) $product['reward_price'];
        $total_price = round($unit_price * $quantity, 2);

        // lock wallet
        $wstmt = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        $wallet = $wstmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
            $wstmt->execute([$user_id]);
            $wallet = $wstmt->fetch(PDO::FETCH_ASSOC);
        }

        if ((float)$wallet['balance'] < $total_price) {
            $pdo->rollBack();
            jsonResponse('error', 'Insufficient wallet balance.');
        }

        $reference = generateReference('TXH-ORD');
        $now = date('Y-m-d H:i:s');

        // Debit wallet
        $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?")
            ->execute([$total_price, $user_id]);

        // Decrement stock if tracked
        if ($tracks_stock) {
            $pdo->prepare("UPDATE xrewards_products SET stock = stock - ? WHERE id = ?")
                ->execute([$quantity, $product_id]);
        }

        // Create order
        $pdo->prepare("INSERT INTO xrewards_orders
            (user_id, product_id, quantity, unit_price, total_price, shipping_details, status, reference, ordered_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)")
            ->execute([$user_id, $product_id, $quantity, $unit_price, $total_price, $shipping_details, $reference, $now, $now]);

        $order_id = (int) $pdo->lastInsertId();

        // Transaction record (purchase)
        $details = json_encode([
            'order_id'     => $order_id,
            'product_id'   => $product_id,
            'product_name' => $product['product_name'],
            'quantity'     => $quantity,
            'unit_price'   => $unit_price,
            'kind'         => 'xrewards_purchase',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                       VALUES (?, 'purchase', 'wallet', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $total_price, $reference, $details, $now]);

        $pdo->commit();

        // Emails
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'xrewards_order_placed',
                'variables' => [
                    'user_name'    => $user_name,
                    'product_name' => $product['product_name'],
                    'quantity'     => $quantity,
                    'unit_price'   => number_format($unit_price, 2),
                    'total_price'  => number_format($total_price, 2),
                    'reference'    => $reference,
                ]
            ]);

            if (defined('ADMIN_CONTACT_EMAIL')) {
                sendEmail([
                    'to' => ADMIN_CONTACT_EMAIL,
                    'template' => 'admin_xrewards_order',
                    'variables' => [
                        'user_name'        => $user_name,
                        'user_email'       => $user_email,
                        'product_name'     => $product['product_name'],
                        'quantity'         => $quantity,
                        'total_price'      => number_format($total_price, 2),
                        'shipping_details' => $shipping_details,
                        'reference'        => $reference,
                    ]
                ]);
            }
        }

        jsonResponse('success', 'Order placed successfully.', [
            'order_id'    => $order_id,
            'reference'   => $reference,
            'unit_price'  => $unit_price,
            'total_price' => $total_price,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('xrewards place_order error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to place order. Please try again.');
    }
}


// --------------------- ACTION: cancel_order ---------------------
if ($action === 'cancel_order') {
    $order_id = (int) ($input['order_id'] ?? 0);
    if ($order_id <= 0) jsonResponse('error', 'Invalid order id.');

    try {
        $pdo->beginTransaction();

        // lock the order row
        $ostmt = $pdo->prepare("SELECT id, user_id, product_id, quantity, total_price, status
            FROM xrewards_orders WHERE id = ? FOR UPDATE");
        $ostmt->execute([$order_id]);
        $order = $ostmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $pdo->rollBack();
            jsonResponse('error', 'Order not found.');
        }
        if ((int)$order['user_id'] !== $user_id) {
            $pdo->rollBack();
            jsonResponse('error', 'Permission denied.');
        }
        if ($order['status'] !== 'pending') {
            $pdo->rollBack();
            jsonResponse('error', 'Only pending orders can be cancelled.');
        }

        $refund_amount = (float) $order['total_price'];
        $quantity      = (int) $order['quantity'];
        $product_id    = (int) $order['product_id'];

        // lock product to safely restock
        $pstmt = $pdo->prepare("SELECT id, product_name, stock FROM xrewards_products WHERE id = ? FOR UPDATE");
        $pstmt->execute([$product_id]);
        $product = $pstmt->fetch(PDO::FETCH_ASSOC);
        $tracks_stock = $product && $product['stock'] !== null;

        // lock wallet & credit refund
        $wstmt = $pdo->prepare("SELECT id FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        if (!$wstmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
        }

        $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?")
            ->execute([$refund_amount, $user_id]);

        // restock if tracked
        if ($tracks_stock) {
            $pdo->prepare("UPDATE xrewards_products SET stock = stock + ? WHERE id = ?")
                ->execute([$quantity, $product_id]);
        }

        // mark order cancelled
        $pdo->prepare("UPDATE xrewards_orders SET status = 'cancelled' WHERE id = ?")
            ->execute([$order_id]);

        // refund transaction record
        $reference = generateReference('TXH-REFUND');
        $now = date('Y-m-d H:i:s');
        $details = json_encode([
            'order_id'     => $order_id,
            'product_id'   => $product_id,
            'product_name' => $product['product_name'] ?? null,
            'quantity'     => $quantity,
            'kind'         => 'xrewards_refund',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                       VALUES (?, 'refund', 'wallet', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $refund_amount, $reference, $details, $now]);

        $pdo->commit();

        // Email
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $user_email,
                'template' => 'xrewards_order_cancelled',
                'variables' => [
                    'user_name'    => $user_name,
                    'product_name' => $product['product_name'] ?? '—',
                    'quantity'     => $quantity,
                    'refund'       => number_format($refund_amount, 2),
                    'reference'    => $reference,
                ]
            ]);
        }

        jsonResponse('success', 'Order cancelled and refund credited to your wallet.', [
            'order_id'  => $order_id,
            'refund'    => $refund_amount,
            'reference' => $reference,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('xrewards cancel_order error: ' . $e->getMessage());
        jsonResponse('error', 'Failed to cancel order. Please try again.');
    }
}


// default
http_response_code(400);
jsonResponse('error', 'Invalid action.');
