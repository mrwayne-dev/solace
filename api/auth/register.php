<?php
// ========================================
// USER REGISTRATION — TitanXHoldings
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
    $email      = strtolower(trim($input['email'] ?? ''));
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

    // --- Baseline password policy ---
    if (strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
        exit;
    }

    // --- One email = one role: reject if used by a user OR an admin ---
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
        exit;
    }
    $adminCheck = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $adminCheck->execute([$email]);
    if ($adminCheck->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'This email cannot be used for a user account.']);
        exit;
    }

    // --- Hash password ---
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $full_name = "{$first_name} {$last_name}";

    // --- Insert new user (unverified — no session until email is confirmed) ---
    $stmt = $pdo->prepare("
        INSERT INTO users (name, full_name, email, password, email_verified, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$full_name, $full_name, $email, $hashed]);
    $user_id = $pdo->lastInsertId();

    // --- Create wallet entry ---
    $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);

    // --- Generate + store email verification OTP ---
    $otp = random_int(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("INSERT INTO email_verifications (user_id, otp, expires_at) VALUES (?, ?, ?)")
        ->execute([$user_id, $otp, $expiry]);

    // --- Send Verification Email (welcome email is sent after verification) ---
    sendEmail([
        'to' => $email,
        'template' => 'email_verification',
        'variables' => [
            'user_name' => $full_name,
            'otp' => $otp,
        ],
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'We sent a 6-digit verification code to your email.',
        'data' => ['requires_verification' => true, 'user_id' => $user_id]
    ]);

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again.']);
}
?>
