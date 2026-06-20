<?php
// ========================================
// ADMIN FORGOT PASSWORD — Solace Mining
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
$email = trim($input['email'] ?? '');

if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

try {
    $pdo = getPDO();

    // --- Verify Admin ---
    $stmt = $pdo->prepare("SELECT id, full_name FROM admins WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(['status' => 'error', 'message' => 'No admin found with that email']);
        exit;
    }

    // --- Rate limit ---
    $stmt = $pdo->prepare("SELECT created_at FROM admin_password_resets WHERE admin_id = :aid ORDER BY id DESC LIMIT 1");
    $stmt->execute(['aid' => $admin['id']]);
    $lastReset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastReset && (time() - strtotime($lastReset['created_at'])) < 120) {
        echo json_encode(['status' => 'error', 'message' => 'Please wait before requesting another OTP.']);
        exit;
    }

    // --- Generate OTP ---
    $otp = random_int(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // --- Ensure admin_password_resets table ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            otp VARCHAR(10) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    // --- Clean up old OTPs ---
    $pdo->prepare("DELETE FROM admin_password_resets WHERE admin_id = :aid")->execute(['aid' => $admin['id']]);

    // --- Insert new OTP ---
    $stmt = $pdo->prepare("INSERT INTO admin_password_resets (admin_id, otp, expires_at) VALUES (:aid, :otp, :expiry)");
    $stmt->execute(['aid' => $admin['id'], 'otp' => $otp, 'expiry' => $expiry]);

    // --- Send OTP Email ---
    sendEmail([
        'to' => $email,
        'template' => 'password_reset',
        'variables' => [
            'user_name' => $admin['full_name'] ?? 'Administrator',
            'otp' => $otp,
        ],
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'OTP sent to your admin email.',
        'data' => ['user_id' => $admin['id']]
    ]);

} catch (Exception $e) {
    error_log("Admin Forgot Password Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Try again later.']);
}
?>
