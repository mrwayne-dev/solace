<?php
// ========================================
// USER REGISTRATION — Solace Mining
// ========================================

ini_set('display_errors', 0);
error_reporting(0);
ob_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/helpers.php';
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
    $ref_code   = strtoupper(trim($input['ref'] ?? ($input['referral_code'] ?? ($_GET['ref'] ?? ''))));

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

    // --- Resolve referrer from referral code (if supplied & valid) ---
    $referred_by = null;
    if ($ref_code !== '') {
        $refStmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ? LIMIT 1");
        $refStmt->execute([$ref_code]);
        $referred_by = $refStmt->fetchColumn() ?: null;
    }

    // --- Insert new user ---
    $stmt = $pdo->prepare("
        INSERT INTO users (name, full_name, email, password, email_verified, referred_by, created_at)
        VALUES (?, ?, ?, ?, 1, ?, NOW())
    ");
    $stmt->execute([$full_name, $full_name, $email, $hashed, $referred_by]);
    $user_id = $pdo->lastInsertId();

    // --- Assign this user their own referral code ---
    ensureReferralCode($pdo, $user_id);

    // --- Record the referral relationship ---
    if ($referred_by) {
        $pdo->prepare("INSERT IGNORE INTO referrals (referrer_user_id, referred_user_id) VALUES (?, ?)")
            ->execute([$referred_by, $user_id]);
    }

    // --- Create wallet entry ---
    $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)")->execute([$user_id]);

    // --- Open the logged-in session (regenerate ID first to prevent fixation) ---
    session_regenerate_id(true);
    $_SESSION['user_id']         = $user_id;
    $_SESSION['email']           = $email;
    $_SESSION['full_name']       = $full_name;
    $_SESSION['role']            = 'user';
    $_SESSION['profile_picture'] = '/assets/images/avatar/default.png';

    // --- Send Welcome Email ---
    sendEmail([
        'to' => $email,
        'template' => 'welcome_user',
        'variables' => ['user_name' => $full_name],
    ]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Account created. Redirecting…',
        'data'    => ['redirect' => '/dashboard']
    ]);

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again.']);
}
?>
