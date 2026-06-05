<?php
// ===============================================
// FILE: /api/backend/profile.php
// PURPOSE: User profile controller for TitanXHoldings
// Actions: get_profile | update_profile | change_password
// ===============================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$pdo = getPDO();
$user_id = (int) $_SESSION['user_id'];

// --- Parse request (JSON body, POST, or GET) ---
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$body = [];
$raw = @file_get_contents('php://input');
if ($raw) {
    $json = @json_decode($raw, true);
    if (is_array($json)) {
        $body = $json;
        if (!$action && !empty($json['action'])) $action = $json['action'];
    }
}
$action = $action ? trim((string) $action) : null;

function jsonResponse($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

function field($body, $key) {
    return isset($body[$key]) ? trim((string) $body[$key]) : '';
}

try {
    switch ($action) {

        // -------------------------------------------------------
        // GET PROFILE
        // -------------------------------------------------------
        case 'get_profile': {
            $stmt = $pdo->prepare("SELECT name, full_name, email, phone, country, address, profile_picture
                                   FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u) jsonResponse('error', 'User not found.');

            jsonResponse('success', 'Profile loaded.', [
                'full_name'       => $u['full_name'] ?: $u['name'],
                'email'           => $u['email'],
                'phone'           => $u['phone'] ?? '',
                'country'         => $u['country'] ?? '',
                'address'         => $u['address'] ?? '',
                'profile_picture' => $u['profile_picture'] ?: '/assets/images/avatar/default.png',
            ]);
            break;
        }

        // -------------------------------------------------------
        // UPDATE PROFILE (name, email, phone, country, address)
        // -------------------------------------------------------
        case 'update_profile': {
            $full_name = field($body, 'full_name');
            $email     = field($body, 'email');
            $phone     = field($body, 'phone');
            $country   = field($body, 'country');
            $address   = field($body, 'address');

            if ($full_name === '') jsonResponse('error', 'Full name is required.');
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse('error', 'A valid email address is required.');
            }

            // Email uniqueness (exclude self)
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
            $chk->execute([$email, $user_id]);
            if ($chk->fetch()) jsonResponse('error', 'That email is already in use.');

            $upd = $pdo->prepare("UPDATE users
                                  SET full_name = ?, email = ?, phone = ?, country = ?, address = ?
                                  WHERE id = ?");
            $upd->execute([$full_name, $email, $phone ?: null, $country ?: null, $address ?: null, $user_id]);

            // Keep session display name + email fresh
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;

            jsonResponse('success', 'Profile updated successfully.', [
                'full_name' => $full_name,
                'email'     => $email,
                'phone'     => $phone,
                'country'   => $country,
                'address'   => $address,
            ]);
            break;
        }

        // -------------------------------------------------------
        // CHANGE PASSWORD
        // -------------------------------------------------------
        case 'change_password': {
            $current = field($body, 'current_password');
            $new     = field($body, 'new_password');
            $confirm = field($body, 'confirm_password');

            if ($current === '' || $new === '' || $confirm === '') {
                jsonResponse('error', 'All password fields are required.');
            }
            if (strlen($new) < 8) jsonResponse('error', 'New password must be at least 8 characters.');
            if ($new !== $confirm) jsonResponse('error', 'New password and confirmation do not match.');

            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !password_verify($current, $row['password'])) {
                jsonResponse('error', 'Your current password is incorrect.');
            }

            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user_id]);

            jsonResponse('success', 'Password updated successfully.');
            break;
        }

        default:
            jsonResponse('error', 'Unknown or missing action.');
    }
} catch (Throwable $e) {
    error_log('profile.php: ' . $e->getMessage());
    jsonResponse('error', 'Server error. Please try again.');
}
