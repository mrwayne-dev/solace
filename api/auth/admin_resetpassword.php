<?php
// ========================================
// ADMIN RESET PASSWORD — HealthRunCare
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
$admin_id = intval($input['user_id'] ?? 0);
$otp = trim($input['otp'] ?? '');
$new_password = trim($input['new_password'] ?? '');
$verify_only = !empty($input['verify_only']);

if (!$admin_id || !$otp) {
    echo json_encode(['status' => 'error', 'message' => 'Missing OTP or ID']);
    exit;
}

try {
    $pdo = getPDO();

    // --- Validate OTP ---
    $stmt = $pdo->prepare("SELECT * FROM admin_password_resets WHERE admin_id = :aid AND otp = :otp LIMIT 1");
    $stmt->execute(['aid' => $admin_id, 'otp' => $otp]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
        exit;
    }

    if (strtotime($reset['expires_at']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'OTP expired']);
        exit;
    }

    // --- Verification only ---
    if ($verify_only) {
        echo json_encode(['status' => 'success', 'message' => 'OTP verified']);
        exit;
    }

    // --- Validate new password ---
    if (strlen($new_password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
        exit;
    }

    // --- Update admin password ---
    $stmt = $pdo->prepare("UPDATE admins SET password = :password WHERE id = :aid");
    $stmt->execute([
        'password' => password_hash($new_password, PASSWORD_DEFAULT),
        'aid' => $admin_id
    ]);

    // --- Clean up OTP ---
    $pdo->prepare("DELETE FROM admin_password_resets WHERE admin_id = :aid")->execute(['aid' => $admin_id]);

    // --- Retrieve email for confirmation ---
    $stmt = $pdo->prepare("SELECT email, full_name FROM admins WHERE id = :aid");
    $stmt->execute(['aid' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Send confirmation email ---
    if (!empty($admin['email'])) {
        sendEmail([
            'to' => $admin['email'],
            'template' => 'password_reset_success',
            'variables' => ['user_name' => $admin['full_name'] ?? 'Administrator'],
        ]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Password reset successfully!',
        'data' => ['redirect' => '/admin/login']
    ]);

} catch (Exception $e) {
    error_log("Admin Reset Password Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Something went wrong, please try again later.']);
}
?>
