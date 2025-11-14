<?php
// ========================================
// HELPER FUNCTIONS — HealthRunCare
// ========================================

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
?>
