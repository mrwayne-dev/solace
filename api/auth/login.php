<?php
// ========================================
// USER LOGIN — HealthRunCare
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
    $pdo = getPDO();

    // --- Get input data ---
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (!$email || !$password) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
        exit;
    }

    // --- Retrieve user ---
    $stmt = $pdo->prepare("SELECT id, name, full_name, email, password, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        exit;
    }

    // --- Check if user is active ---
    if (isset($user['status']) && strtolower($user['status']) !== 'active') {
        echo json_encode(['status' => 'error', 'message' => 'Your account has been disabled. Contact support.']);
        exit;
    }

    // --- Verify password ---
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        exit;
    }

    // --- Setup session ---
    $displayName = $user['full_name'] ?: ($user['name'] ?? 'User');
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $displayName;

    // --- Prepare login details ---
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $browser = getUserBrowser($_SERVER['HTTP_USER_AGENT'] ?? '');
    $location = getLocationFromIP($ip);
    $loginTime = date('Y-m-d H:i:s');

    // --- Update user last_login field (if exists) ---
    if ($pdo->query("SHOW COLUMNS FROM users LIKE 'last_login'")->rowCount() > 0) {
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
    }

    // --- Send Login Alert Email to the User ---
    sendEmail([
        'to' => $user['email'],
        'template' => 'login_alert',
        'variables' => [
            'user_name' => $displayName,
            'login_time' => $loginTime,
            'ip' => $ip,
            'browser' => $browser,
            'location' => $location,
        ],
    ]);

    // --- Notify Admin of User Login ---
    sendEmail([
        'to' => ADMIN_CONTACT_EMAIL,
        'template' => 'admin_user_login_notification',
        'variables' => [
            'user_name' => $displayName,
            'user_email' => $user['email'],
            'login_time' => $loginTime,
            'ip' => $ip,
            'browser' => $browser,
            'location' => $location,
        ],
    ]);

    logLoginEvent($pdo, 'user', $user['id'], $ip, $browser, $location);

    // --- Respond to frontend ---
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful!',
        'data' => ['redirect' => '/dashboard']
    ]);

    exit;

} catch (Exception $e) {
    error_log('User login error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']);
    exit;
}
?>
