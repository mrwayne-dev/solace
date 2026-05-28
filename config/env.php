<?php
// ========================================
// ENVIRONMENT CONFIGURATION — HealthRunCare
// ========================================

// Auto-detect environment (from .htaccess)
$env = $_SERVER['TXH_ENV'] ?? 'dev';
define('ENV', $env);
define('APP_ENV', (ENV === 'dev' ? 'local' : 'production'));


 
// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'titanx_db');
define('DB_USER', 'uvammbciwx_michael');
define('DB_PASS', 'Michael@01');

// Email / SMTP Settings
define('SMTP_HOST', 'mail.spacemail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'support@healthruncare.com');
define('SMTP_PASS', 'Zegsxchange@01');
define('SMTP_FROM', 'support@healthruncare.com');
define('SMTP_FROM_NAME', 'HealthRunCare');
define('SMTP_SECURE', 'ssl');

// NOWPayments Configuration
define('NOWPAY_API_KEY', 'Q4XC3NR-26CMHSJ-HVGTSCT-BKSMYHK');
define('NOWPAY_PUBLIC_KEY', '2054f28e-65b8-4685-917f-e2445291aaa8');
define('NOWPAY_IPN_SECRET', 'w9y8dEzmXh92gYZ1JjqDCIZ6kA/Awbxe');
define('NOWPAY_CA_BUNDLE', __DIR__ . '/certs/cacert.pem');

// Admin Contact Email
define('ADMIN_CONTACT_EMAIL', 'support@healthruncare.com');

// Error Display (Based on ENV)
if (ENV === 'dev') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>
