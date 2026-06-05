<?php
// ========================================
// FORGOT PASSWORD HANDLER — TitanXHoldings
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

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

try {
    // ✅ Establish database connection
    $pdo = getPDO();

    // --- Verify User ---
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'No account found with that email']);
        exit;
    }

    // --- Simple rate limit: 2-minute cooldown ---
    $stmt = $pdo->prepare("SELECT created_at FROM password_resets WHERE user_id = :uid ORDER BY id DESC LIMIT 1");
    $stmt->execute(['uid' => $user['id']]);
    $lastReset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastReset && (time() - strtotime($lastReset['created_at'])) < 120) {
        echo json_encode(['status' => 'error', 'message' => 'Please wait before requesting another code.']);
        exit;
    }

    // --- Generate and store OTP ---
    $otp = random_int(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            otp VARCHAR(10) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    // --- Clean up old OTPs ---
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid")->execute(['uid' => $user['id']]);

    // --- Insert new OTP ---
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, otp, expires_at) VALUES (:uid, :otp, :expiry)");
    $stmt->execute(['uid' => $user['id'], 'otp' => $otp, 'expiry' => $expiry]);

    // --- Send OTP Email ---
    sendEmail([
        'to' => $email,
        'template' => 'password_reset',
        'variables' => [
            'user_name' => $user['full_name'] ?? 'User',
            'otp' => $otp,
        ],
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'OTP sent to your email.',
        'data' => ['user_id' => $user['id']]
    ]);

} catch (Exception $e) {
    error_log("Forgot Password Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Something went wrong, please try again later.']);
}
?>
