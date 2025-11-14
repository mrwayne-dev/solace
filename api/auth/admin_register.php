<?php
// ========================================
// ADMIN REGISTRATION — HealthRunCare
// ========================================

ini_set('display_errors', 0);
error_reporting(0);
ob_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../backend/email.php';

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Strict',
]);

ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();
    $input = json_decode(file_get_contents('php://input'), true);

    $first_name = trim($input['first_name'] ?? '');
    $last_name  = trim($input['last_name'] ?? '');
    $email      = trim($input['email'] ?? '');
    $password   = trim($input['password'] ?? '');

    if (!$first_name || !$last_name || !$email || !$password) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
        exit;
    }

    // Check existing email
    $check = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $name = "{$first_name} {$last_name}";

    // Insert into admins table
    $stmt = $pdo->prepare("
        INSERT INTO admins (name, full_name, email, password, role, status, created_at)
        VALUES (?, ?, ?, ?, 'manager', 'active', NOW())
    ");
    $stmt->execute([$name, $name, $email, $hashed]);
    $admin_id = $pdo->lastInsertId();

    // Set session
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_name'] = $name;
    $_SESSION['admin_role'] = 'manager';
    $_SESSION['admin_logged_in'] = true;

    // Send welcome email
    sendEmail([
        'to' => $email,
        'template' => 'welcome_admin',
        'variables' => ['admin_name' => $name],
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Admin registration successful!',
        'data' => ['redirect' => '/admin']
    ]);
    exit;

} catch (Exception $e) {
    error_log('Admin registration error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']);
    exit;
}
?>
