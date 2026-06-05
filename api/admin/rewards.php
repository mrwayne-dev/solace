<?php
// ============================================================
// FILE: /api/admin/rewards.php
// PURPOSE: Admin controller for X-Rewards catalog & order management
// ACTIONS: get_products, add_product, edit_product, toggle_status,
//          get_orders, update_order_status, admin_cancel_order
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
require_once __DIR__ . '/../backend/email.php';

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

function adminRef($prefix) {
    return strtoupper($prefix . '-' . uniqid() . '-' . rand(1000, 9999));
}


// --------------------- ACTION: get_products ---------------------
if ($action === 'get_products') {
    try {
        $stmt = $pdo->query("SELECT * FROM xrewards_products ORDER BY created_at DESC");
        jsonOut('success', 'Products loaded.', ['products' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('admin rewards get_products: ' . $e->getMessage());
        jsonOut('error', 'Failed to load products.');
    }
}


// --------------------- ACTION: add_product ---------------------
if ($action === 'add_product') {
    $product_name = trim((string)($input['product_name'] ?? ''));
    $description  = trim((string)($input['description'] ?? ''));
    $retail_price = (float) ($input['retail_price'] ?? 0);
    $image_path   = trim((string)($input['image_path'] ?? '')) ?: null;
    $stock        = array_key_exists('stock', $input) && $input['stock'] !== '' && $input['stock'] !== null
                    ? (int) $input['stock'] : null;
    $status       = $input['status'] ?? 'active';

    if ($product_name === '')      jsonOut('error', 'Product name is required.');
    if ($retail_price <= 0)        jsonOut('error', 'Retail price must be greater than zero.');
    if (!in_array($status, ['active', 'inactive', 'out_of_stock'], true)) {
        jsonOut('error', 'Invalid status.');
    }

    // Auto-compute reward price at 40% discount
    $reward_price = round($retail_price * 0.60, 2);
    $discount_pct = 40.00;

    try {
        $pdo->prepare("INSERT INTO xrewards_products
            (product_name, description, retail_price, reward_price, discount_pct, image_path, stock, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$product_name, $description, $retail_price, $reward_price, $discount_pct, $image_path, $stock, $status]);

        $product_id = (int) $pdo->lastInsertId();
        jsonOut('success', 'Product added.', [
            'product_id'   => $product_id,
            'reward_price' => $reward_price,
            'discount_pct' => $discount_pct,
        ]);
    } catch (Exception $e) {
        error_log('admin rewards add_product: ' . $e->getMessage());
        jsonOut('error', 'Failed to add product.');
    }
}


// --------------------- ACTION: edit_product ---------------------
if ($action === 'edit_product') {
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) jsonOut('error', 'Invalid product id.');

    // Build dynamic UPDATE from whichever fields were provided
    $allowed = ['product_name', 'description', 'retail_price', 'reward_price',
                'discount_pct', 'image_path', 'stock', 'status'];
    $sets = [];
    $params = [];

    foreach ($allowed as $field) {
        if (!array_key_exists($field, $input)) continue;

        if ($field === 'stock') {
            $val = ($input[$field] === '' || $input[$field] === null) ? null : (int) $input[$field];
        } elseif (in_array($field, ['retail_price', 'reward_price', 'discount_pct'], true)) {
            $val = (float) $input[$field];
        } else {
            $val = $input[$field] === null ? null : (string) $input[$field];
        }
        $sets[] = "$field = ?";
        $params[] = $val;
    }

    // If retail_price changed but reward_price wasn't supplied, recompute
    if (array_key_exists('retail_price', $input) && !array_key_exists('reward_price', $input)) {
        $sets[] = "reward_price = ?";
        $params[] = round((float)$input['retail_price'] * 0.60, 2);
    }

    if (empty($sets)) jsonOut('error', 'No fields supplied to update.');

    $params[] = $id;
    try {
        $stmt = $pdo->prepare("UPDATE xrewards_products SET " . implode(', ', $sets) . " WHERE id = ?");
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) jsonOut('error', 'Product not found or nothing changed.');
        jsonOut('success', 'Product updated.', ['product_id' => $id]);
    } catch (Exception $e) {
        error_log('admin rewards edit_product: ' . $e->getMessage());
        jsonOut('error', 'Failed to update product.');
    }
}


// --------------------- ACTION: toggle_status ---------------------
if ($action === 'toggle_status') {
    $id = (int) ($input['id'] ?? 0);
    $status = $input['status'] ?? '';
    if ($id <= 0) jsonOut('error', 'Invalid product id.');
    if (!in_array($status, ['active', 'inactive', 'out_of_stock'], true)) {
        jsonOut('error', 'Invalid status.');
    }

    try {
        $stmt = $pdo->prepare("UPDATE xrewards_products SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        if ($stmt->rowCount() === 0) jsonOut('error', 'Product not found.');
        jsonOut('success', 'Status updated.', ['product_id' => $id, 'status' => $status]);
    } catch (Exception $e) {
        error_log('admin rewards toggle_status: ' . $e->getMessage());
        jsonOut('error', 'Failed to update status.');
    }
}


// --------------------- ACTION: get_orders ---------------------
if ($action === 'get_orders') {
    $status_filter = $input['status'] ?? null;

    try {
        $sql = "SELECT xrewards_orders.*,
                       users.full_name AS user_name, users.email AS user_email,
                       xrewards_products.product_name, xrewards_products.image_path
                FROM xrewards_orders
                JOIN users             ON xrewards_orders.user_id    = users.id
                JOIN xrewards_products ON xrewards_orders.product_id = xrewards_products.id";
        $params = [];
        if ($status_filter !== null && $status_filter !== '' && $status_filter !== 'all') {
            $sql .= " WHERE xrewards_orders.status = ?";
            $params[] = $status_filter;
        }
        $sql .= " ORDER BY xrewards_orders.ordered_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonOut('success', 'Orders loaded.', ['orders' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('admin rewards get_orders: ' . $e->getMessage());
        jsonOut('error', 'Failed to load orders.');
    }
}


// --------------------- ACTION: update_order_status ---------------------
if ($action === 'update_order_status') {
    $order_id   = (int) ($input['order_id'] ?? 0);
    $new_status = trim((string)($input['new_status'] ?? ''));

    if ($order_id <= 0) jsonOut('error', 'Invalid order id.');
    if (!in_array($new_status, ['confirmed', 'shipped', 'delivered'], true)) {
        jsonOut('error', 'Invalid status. Allowed: confirmed, shipped, delivered.');
    }

    try {
        $pdo->beginTransaction();

        // Lock the order row to prevent concurrent admin updates
        $ostmt = $pdo->prepare("SELECT xrewards_orders.*,
                       users.full_name AS user_name, users.email AS user_email,
                       xrewards_products.product_name
                FROM xrewards_orders
                JOIN users             ON xrewards_orders.user_id    = users.id
                JOIN xrewards_products ON xrewards_orders.product_id = xrewards_products.id
                WHERE xrewards_orders.id = ? FOR UPDATE");
        $ostmt->execute([$order_id]);
        $order = $ostmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            $pdo->rollBack();
            jsonOut('error', 'Order not found.');
        }
        if ($order['status'] === 'cancelled') {
            $pdo->rollBack();
            jsonOut('error', 'Cannot update a cancelled order.');
        }

        $pdo->prepare("UPDATE xrewards_orders SET status = ? WHERE id = ?")
            ->execute([$new_status, $order_id]);

        $pdo->commit();

        // Send appropriate email for confirmed/shipped (delivered = silent)
        if (function_exists('sendEmail')) {
            $template = null;
            if ($new_status === 'confirmed') $template = 'xrewards_order_confirmed';
            if ($new_status === 'shipped')   $template = 'xrewards_order_shipped';

            if ($template !== null) {
                sendEmail([
                    'to' => $order['user_email'],
                    'template' => $template,
                    'variables' => [
                        'user_name'    => $order['user_name'] ?? 'User',
                        'product_name' => $order['product_name'],
                        'quantity'     => (int) $order['quantity'],
                        'reference'    => $order['reference'],
                    ]
                ]);
            }
        }

        jsonOut('success', 'Order status updated.', [
            'order_id' => $order_id,
            'status'   => $new_status,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('admin rewards update_order_status: ' . $e->getMessage());
        jsonOut('error', 'Failed to update order status.');
    }
}


// --------------------- ACTION: admin_cancel_order ---------------------
if ($action === 'admin_cancel_order') {
    $order_id = (int) ($input['order_id'] ?? 0);
    $reason   = trim((string)($input['reason'] ?? ''));
    if ($order_id <= 0) jsonOut('error', 'Invalid order id.');

    try {
        $pdo->beginTransaction();

        // Lock order
        $ostmt = $pdo->prepare("SELECT xrewards_orders.*,
                       users.full_name AS user_name, users.email AS user_email,
                       xrewards_products.product_name, xrewards_products.stock AS product_stock
                FROM xrewards_orders
                JOIN users             ON xrewards_orders.user_id    = users.id
                JOIN xrewards_products ON xrewards_orders.product_id = xrewards_products.id
                WHERE xrewards_orders.id = ? FOR UPDATE");
        $ostmt->execute([$order_id]);
        $order = $ostmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $pdo->rollBack();
            jsonOut('error', 'Order not found.');
        }
        if ($order['status'] === 'cancelled') {
            $pdo->rollBack();
            jsonOut('error', 'Order is already cancelled.');
        }
        if (in_array($order['status'], ['delivered'], true)) {
            $pdo->rollBack();
            jsonOut('error', 'Delivered orders cannot be cancelled.');
        }

        $refund_amount = (float) $order['total_price'];
        $quantity      = (int)   $order['quantity'];
        $product_id    = (int)   $order['product_id'];
        $user_id       = (int)   $order['user_id'];
        $tracks_stock  = $order['product_stock'] !== null;

        // Lock & credit wallet
        $wstmt = $pdo->prepare("SELECT id FROM wallets WHERE user_id = ? FOR UPDATE");
        $wstmt->execute([$user_id]);
        if (!$wstmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);
        }
        $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?")
            ->execute([$refund_amount, $user_id]);

        // Restock if tracked
        if ($tracks_stock) {
            $pdo->prepare("UPDATE xrewards_products SET stock = stock + ? WHERE id = ?")
                ->execute([$quantity, $product_id]);
        }

        // Mark cancelled
        $note = $reason !== '' ? "Cancelled by admin: $reason" : 'Cancelled by admin.';
        $pdo->prepare("UPDATE xrewards_orders SET status = 'cancelled', notes = ? WHERE id = ?")
            ->execute([$note, $order_id]);

        // Refund transaction
        $reference = adminRef('TXH-REFUND');
        $now = date('Y-m-d H:i:s');
        $details = json_encode([
            'order_id'     => $order_id,
            'product_id'   => $product_id,
            'product_name' => $order['product_name'],
            'quantity'     => $quantity,
            'cancelled_by' => 'admin',
            'admin_id'     => (int) $_SESSION['admin_id'],
            'reason'       => $reason ?: null,
            'kind'         => 'xrewards_admin_refund',
        ]);
        $pdo->prepare("INSERT INTO transactions (user_id, type, method, amount, reference, status, details, created_at)
                       VALUES (?, 'refund', 'wallet', ?, ?, 'completed', ?, ?)")
            ->execute([$user_id, $refund_amount, $reference, $details, $now]);

        $pdo->commit();

        // Email user
        if (function_exists('sendEmail')) {
            sendEmail([
                'to' => $order['user_email'],
                'template' => 'xrewards_order_cancelled',
                'variables' => [
                    'user_name'    => $order['user_name'] ?? 'User',
                    'product_name' => $order['product_name'],
                    'quantity'     => $quantity,
                    'refund'       => number_format($refund_amount, 2),
                    'reference'    => $reference,
                ]
            ]);
        }

        jsonOut('success', 'Order cancelled and refund issued.', [
            'order_id'  => $order_id,
            'refund'    => $refund_amount,
            'reference' => $reference,
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('admin rewards admin_cancel_order: ' . $e->getMessage());
        jsonOut('error', 'Failed to cancel order.');
    }
}


// --------------------- ACTION: get_metrics ---------------------
if ($action === 'get_metrics') {
    try {
        $row = $pdo->query("SELECT
            COUNT(CASE WHEN status='active'      THEN 1 END) AS active_products,
            COUNT(CASE WHEN status='out_of_stock' THEN 1 END) AS oos_products
            FROM xrewards_products")->fetch(PDO::FETCH_ASSOC) ?: [];

        $orders = $pdo->query("SELECT
            COUNT(CASE WHEN status='pending'    THEN 1 END) AS pending_orders,
            COUNT(CASE WHEN status='shipped'    THEN 1 END) AS shipped_orders,
            COUNT(CASE WHEN status='delivered'  THEN 1 END) AS delivered_orders,
            COALESCE(SUM(CASE WHEN status<>'cancelled' THEN total_price END), 0) AS total_revenue
            FROM xrewards_orders")->fetch(PDO::FETCH_ASSOC) ?: [];

        jsonOut('success', 'Metrics loaded.', ['metrics' => [
            'active_products'   => (int)($row['active_products'] ?? 0),
            'oos_products'      => (int)($row['oos_products'] ?? 0),
            'pending_orders'    => (int)($orders['pending_orders'] ?? 0),
            'shipped_orders'    => (int)($orders['shipped_orders'] ?? 0),
            'delivered_orders'  => (int)($orders['delivered_orders'] ?? 0),
            'total_revenue'     => round((float)($orders['total_revenue'] ?? 0), 2),
        ]]);
    } catch (Exception $e) {
        error_log('admin rewards get_metrics: ' . $e->getMessage());
        jsonOut('error', 'Failed to load metrics.');
    }
}


// default
http_response_code(400);
jsonOut('error', 'Invalid action.');
