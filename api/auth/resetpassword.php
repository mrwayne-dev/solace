<?php
// ========================================
// RESET PASSWORD HANDLER — Solace Mining
// ========================================

ini_set('display_errors', 0);
error_reporting(0);
ob_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../backend/email.php';

ob_clean();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = intval($input['user_id'] ?? 0);
$otp = trim($input['otp'] ?? '');
$new_password = trim($input['new_password'] ?? '');
$verify_only = !empty($input['verify_only']);

if (empty($user_id) || empty($otp)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID and OTP are required']);
    exit;
}

try {
    // ✅ Establish database connection
    $pdo = getPDO();

    // --- Validate OTP ---
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE user_id = :uid AND otp = :otp LIMIT 1");
    $stmt->execute(['uid' => $user_id, 'otp' => $otp]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
        exit;
    }

    if (strtotime($reset['expires_at']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'OTP expired']);
        exit;
    }

    // --- OTP Verification Only ---
    if ($verify_only) {
        echo json_encode(['status' => 'success', 'message' => 'OTP verified']);
        exit;
    }

    // --- Validate password ---
    if (strlen($new_password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
        exit;
    }

    // --- Update password ---
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :uid");
    $stmt->execute([
        'password' => password_hash($new_password, PASSWORD_DEFAULT),
        'uid' => $user_id
    ]);

    // --- Retrieve user email for notification ---
    $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Clean up OTP ---
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid")->execute(['uid' => $user_id]);

    // --- Send Confirmation Email ---
    if (!empty($user['email'])) {
        sendEmail([
            'to' => $user['email'],
            'template' => 'password_reset_success',
            'variables' => [
                'user_name' => $user['full_name'] ?? 'User',
            ],
        ]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Password reset successful',
        'data' => ['redirect' => '/login']
    ]);

} catch (Exception $e) {
    error_log("Reset Password Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Something went wrong, please try again later.']);
}
?>
