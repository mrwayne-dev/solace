<?php
// ============================================================
// ENVIRONMENT CONFIGURATION — Solace Mining
//
// This file no longer stores any secrets. It's a thin loader
// that reads .env at the project root and exposes those values
// as PHP constants (the rest of the codebase still uses
// DB_USER, DB_PASS, SMTP_*, NOWPAY_*, etc.).
//
// All credentials live in .env, which must be gitignored.
// ============================================================

// --- Locate and parse .env ----------------------------------------------
$envPath = __DIR__ . '/../.env';

if (!is_file($envPath) || !is_readable($envPath)) {
    http_response_code(500);
    error_log('config/env.php: missing or unreadable .env at ' . $envPath);
    exit('Server misconfigured: environment file not found.');
}

$envValues = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $trim = trim($line);
    if ($trim === '' || $trim[0] === '#' || strpos($trim, '=') === false) continue;
    [$key, $value] = array_map('trim', explode('=', $trim, 2));
    if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
        $value = trim($value, "\"'");
    }
    $envValues[$key] = $value;
}

$envv = static function (string $key, $fallback = null) use ($envValues) {
    return $envValues[$key] ?? $fallback;
};

$require = static function (string $key) use ($envValues) {
    if (!array_key_exists($key, $envValues) || $envValues[$key] === '') {
        http_response_code(500);
        error_log("config/env.php: required .env key missing: {$key}");
        exit("Server misconfigured: {$key} not set in environment.");
    }
    return $envValues[$key];
};

// --- Environment flag ---------------------------------------------------
$env = $_SERVER['SLM_ENV'] ?? ($envValues['APP_ENV'] ?? 'production');
define('ENV', $env);
define('APP_ENV', (in_array($env, ['dev', 'development', 'local'], true) ? 'local' : 'production'));

// --- Base URL (single source of truth: .env, prod fallback) -------------
// Drives NOWPayments callback/redirect URLs and email links. Defined here
// (not in constants.php) so the .env value always wins regardless of the
// order in which callers include constants.php vs env.php.
if (!defined('APP_URL')) define('APP_URL', $envv('APP_URL', 'https://solacemining.org'));

// --- Database (all required) --------------------------------------------
define('DB_HOST', $require('DB_HOST'));
define('DB_NAME', $require('DB_NAME'));
define('DB_USER', $require('DB_USER'));
define('DB_PASS', $envv('DB_PASS', ''));  // password may legitimately be empty in some local setups

// --- SMTP (host/from required, others optional) -------------------------
define('SMTP_HOST',      $require('SMTP_HOST'));
define('SMTP_PORT',      (int) $envv('SMTP_PORT', 465));
define('SMTP_USER',      $envv('SMTP_USER', ''));
define('SMTP_PASS',      $envv('SMTP_PASS', ''));
define('SMTP_FROM',      $require('SMTP_FROM'));
define('SMTP_FROM_NAME', $envv('SMTP_FROM_NAME', 'Solace Mining'));
define('SMTP_SECURE',    $envv('SMTP_SECURE', 'ssl'));

// --- NOWPayments (optional — only required for deposits) ----------------
define('NOWPAY_API_KEY',    $envv('NOWPAYMENTS_API_KEY', ''));
define('NOWPAY_PUBLIC_KEY', $envv('NOWPAYMENTS_PUBLIC_KEY', ''));
define('NOWPAY_IPN_SECRET', $envv('NOWPAYMENTS_IPN_SECRET', ''));
define('NOWPAY_CA_BUNDLE',  __DIR__ . '/certs/cacert.pem');

// --- Admin --------------------------------------------------------------
define('ADMIN_CONTACT_EMAIL', $envv('SMTP_TO', $envv('SMTP_FROM', 'support@solacemining.org')));
define('ADMIN_INVITE_CODE',   $envv('ADMIN_INVITE_CODE', ''));

// --- Error Display ------------------------------------------------------
if (APP_ENV === 'local') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// --- Cleanup loader locals so callers don't see them --------------------
unset($envPath, $envValues, $envv, $require, $env);
