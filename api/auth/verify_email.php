<?php
// ========================================
// EMAIL VERIFICATION — TitanXHoldings
// Verifies the OTP issued at registration (or resends it),
// then opens the logged-in session on success.
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true) ?: [];
$user_id  = intval($input['user_id'] ?? 0);
$otp      = trim($input['otp'] ?? '');
$resend   = !empty($input['resend']);

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid verification request.']);
    exit;
}

try {
    $pdo = getPDO();

    // --- Fetch the account ---
    $stmt = $pdo->prepare("SELECT id, name, full_name, email, role, profile_picture, email_verified FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Account not found.']);
        exit;
    }

    $displayName = $user['full_name'] ?: ($user['name'] ?? 'User');

    // Already verified — nothing to do.
    if ((int)$user['email_verified'] === 1) {
        echo json_encode(['status' => 'error', 'message' => 'This account is already verified. Please sign in.']);
        exit;
    }

    // ============================================================
    // RESEND: regenerate + re-send a fresh OTP (90s cooldown)
    // ============================================================
    if ($resend) {
        $last = $pdo->prepare("SELECT created_at FROM email_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $last->execute([$user_id]);
        $row = $last->fetch(PDO::FETCH_ASSOC);

        if ($row && (time() - strtotime($row['created_at'])) < 90) {
            $wait = 90 - (time() - strtotime($row['created_at']));
            echo json_encode(['status' => 'error', 'message' => "Please wait {$wait}s before requesting another code."]);
            exit;
        }

        $newOtp = random_int(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("INSERT INTO email_verifications (user_id, otp, expires_at) VALUES (?, ?, ?)")
            ->execute([$user_id, $newOtp, $expiry]);

        sendEmail([
            'to' => $user['email'],
            'template' => 'email_verification',
            'variables' => ['user_name' => $displayName, 'otp' => $newOtp],
        ]);

        echo json_encode(['status' => 'success', 'message' => 'A new code has been sent to your email.']);
        exit;
    }

    // ============================================================
    // VERIFY: validate the OTP
    // ============================================================
    if ($otp === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please enter the 6-digit code.']);
        exit;
    }

    $vstmt = $pdo->prepare("SELECT * FROM email_verifications WHERE user_id = ? AND otp = ? LIMIT 1");
    $vstmt->execute([$user_id, $otp]);
    $verify = $vstmt->fetch(PDO::FETCH_ASSOC);

    if (!$verify) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid verification code.']);
        exit;
    }

    if (strtotime($verify['expires_at']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'This code has expired. Please request a new one.']);
        exit;
    }

    // --- Mark verified + clean up ---
    $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?")->execute([$user_id]);

    // --- Open the logged-in session (same fields as login.php) ---
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $displayName;
    $_SESSION['role'] = $user['role'] ?? 'user';
    $_SESSION['profile_picture'] = $user['profile_picture'] ?: '/assets/images/avatar/default.png';

    // --- Send Welcome Email (now that the email is confirmed) ---
    sendEmail([
        'to' => $user['email'],
        'template' => 'welcome_user',
        'variables' => ['user_name' => $displayName],
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Email verified! Redirecting...',
        'data' => ['redirect' => '/dashboard']
    ]);
    exit;

} catch (Exception $e) {
    error_log('Email verification error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']);
    exit;
}
?>
