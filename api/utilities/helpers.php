<?php
// ========================================
// HELPER FUNCTIONS — Solace Mining
// ========================================

/**
 * Return the user's referral code, generating & persisting a unique
 * one on first use. Codes are short, uppercase, URL-safe.
 */
function ensureReferralCode($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT referral_code FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $code = $stmt->fetchColumn();
    if ($code) return $code;

    do {
        $candidate = 'SLM' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referral_code = ?");
        $check->execute([$candidate]);
    } while ((int)$check->fetchColumn() > 0);

    $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?")->execute([$candidate, $user_id]);
    return $candidate;
}

/**
 * Logs an admin action (for audit trails)
 */
function logAdminAction($pdo, $admin_id, $action, $details = '') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, details, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$admin_id, $action, $details]);
    } catch (Exception $e) {
        error_log('Admin log error: ' . $e->getMessage());
    }
}

/**
 * Sanitize user input to prevent XSS or injection
 */
function cleanInput($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Attempt to resolve approximate location from IP address.
 * Works safely in localhost (returns 'Localhost') and online.
 */
function getLocationFromIP($ip) {
    try {
        // Handle localhost or private IPs quickly
        if (in_array($ip, ['127.0.0.1', '::1']) || preg_match('/^192\.168\./', $ip)) {
            return 'Localhost / Internal Network';
        }

        // Lightweight public lookup (2s timeout)
        $url = "https://ipapi.co/{$ip}/json/";
        $context = stream_context_create(['http' => ['timeout' => 2]]);
        $response = @file_get_contents($url, false, $context);
        if (!$response) return 'Unknown Location';

        $data = json_decode($response, true);
        if (!is_array($data)) return 'Unknown Location';

        $city = $data['city'] ?? '';
        $region = $data['region'] ?? '';
        $country = $data['country_name'] ?? '';
        $parts = array_filter([$city, $region, $country]);

        return $parts ? implode(', ', $parts) : 'Unknown Location';

    } catch (Exception $e) {
        error_log('GeoIP lookup failed: ' . $e->getMessage());
        return 'Unknown Location';
    }
}
function logLoginEvent($pdo, $userType, $userId, $ip, $browser, $location) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO login_logs (user_type, user_id, ip, browser, location, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userType, $userId, $ip, $browser, $location]);
    } catch (Exception $e) {
        error_log('Login log error: ' . $e->getMessage());
    }
}

/**
 * Extracts readable browser + OS information from a user-agent string.
 */
function getUserBrowser($userAgent) {
    $browser = 'Unknown Browser';
    $platform = 'Unknown OS';
    $version = '';

    // --- Detect platform ---
    if (preg_match('/linux/i', $userAgent)) {
        $platform = 'Linux';
    } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
        $platform = 'Mac OS';
    } elseif (preg_match('/windows|win32/i', $userAgent)) {
        $platform = 'Windows';
    } elseif (preg_match('/iphone/i', $userAgent)) {
        $platform = 'iPhone';
    } elseif (preg_match('/android/i', $userAgent)) {
        $platform = 'Android';
    }

    // --- Detect browser ---
    if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
        $browser = 'Internet Explorer';
        $ub = "MSIE";
    } elseif (preg_match('/Firefox/i', $userAgent)) {
        $browser = 'Firefox';
        $ub = "Firefox";
    } elseif (preg_match('/OPR|Opera/i', $userAgent)) {
        $browser = 'Opera';
        $ub = "OPR";
    } elseif (preg_match('/Edge/i', $userAgent)) {
        $browser = 'Edge';
        $ub = "Edge";
    } elseif (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edge/i', $userAgent)) {
        $browser = 'Chrome';
        $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
        $browser = 'Safari';
        $ub = "Safari";
    } else {
        $ub = '';
    }

    // --- Extract version ---
    if ($ub && preg_match("/{$ub}\/([0-9\.]+)/i", $userAgent, $matches)) {
        $version = $matches[1];
    }

    return trim("{$browser} {$version} on {$platform}");
}

// ============================================================
// Brute-force mitigation: lightweight per-IP login throttle.
// Records only FAILED attempts; throttles by IP (NOT by account)
// so an attacker can't lock a victim out by spamming their email.
// Fails OPEN on infra error — a rate-limiter must never self-DoS.
// ============================================================
function ensureLoginAttemptsTable($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_ip_time (ip, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function loginThrottleExceeded($pdo, $ip, $maxPerIp = 10, $windowMinutes = 15) {
    try {
        ensureLoginAttemptsTable($pdo);
        $since = date('Y-m-d H:i:s', time() - ($windowMinutes * 60));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?");
        $stmt->execute([$ip, $since]);
        return (int) $stmt->fetchColumn() >= $maxPerIp;
    } catch (Throwable $e) {
        error_log('loginThrottleExceeded error: ' . $e->getMessage());
        return false; // fail open
    }
}

function recordLoginFailure($pdo, $ip, $email = null) {
    try {
        ensureLoginAttemptsTable($pdo);
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip, email, attempted_at) VALUES (?, ?, NOW())");
        $stmt->execute([$ip, ($email !== '' ? $email : null)]);
    } catch (Throwable $e) {
        error_log('recordLoginFailure error: ' . $e->getMessage());
    }
}

/**
 * Append a structured security event to logs/security.log (A09).
 * Values are newline-stripped to prevent log forgery/injection (audit 11.3).
 */
function logSecurityEvent($event, array $context = []) {
    try {
        $logPath = __DIR__ . '/../../logs/security.log';
        if (!is_dir(dirname($logPath))) @mkdir(dirname($logPath), 0775, true);
        $clean = [];
        foreach ($context as $k => $v) {
            $clean[$k] = is_scalar($v) ? str_replace(["\r", "\n"], ' ', (string) $v) : json_encode($v);
        }
        $line = json_encode(['ts' => date('c'), 'event' => $event] + $clean, JSON_UNESCAPED_SLASHES);
        @file_put_contents($logPath, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    } catch (Throwable $e) {
        error_log('logSecurityEvent error: ' . $e->getMessage());
    }
}
?>
