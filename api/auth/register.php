<?php
// ========================================
// USER REGISTRATION — HealthRunCare
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
    // ✅ Establish PDO connection
    $pdo = getPDO();

    $input = json_decode(file_get_contents('php://input'), true);
    $first_name = trim($input['first_name'] ?? '');
    $last_name  = trim($input['last_name'] ?? '');
    $email      = trim($input['email'] ?? '');
    $password   = trim($input['password'] ?? '');

    // --- Validate input ---
    if (!$first_name || !$last_name || !$email || !$password) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
        exit;
    }

    // --- Check existing email ---
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
        exit;
    }

    // --- Hash password ---
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // --- Insert new user (✅ fixed for schema) ---
    $stmt = $pdo->prepare("
        INSERT INTO users (name, full_name, email, password, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute(["{$first_name} {$last_name}", "{$first_name} {$last_name}", $email, $hashed]);
    $user_id = $pdo->lastInsertId();

    // --- Create wallet entry (✅ fixed table name) ---
    $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);

    // --- Set session ---
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['full_name'] = "{$first_name} {$last_name}";

    // --- Send Welcome Email ---
    sendEmail([
        'to' => $email,
        'template' => 'welcome_user',
        'variables' => [
            'user_name' => "{$first_name} {$last_name}",
        ],
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful!',
        'data' => ['redirect' => '/pages/user/dashboard.php']
    ]);

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again.']);
}
?>
