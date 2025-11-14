<?php
// ========================================
// GLOBAL CONSTANTS — HealthRunCare Platform
// ========================================

// App Information
define('APP_NAME', 'HealthRunCare');
define('APP_URL', 'https://healthruncare.com');
define('APP_EMAIL', 'support@healthruncare.com');
define('CURRENCY', 'USD');
define('TIMEZONE', 'Europe/London'); 

// Paths
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('TEMP_PATH', UPLOAD_PATH . 'temp/');
define('USER_UPLOADS_PATH', UPLOAD_PATH . 'user_uploads/');

// User Roles
define('ROLE_USER', 'user');
define('ROLE_SUPPORT_ADMIN', 'support_admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// Feature Controls
define('OTP_EXPIRY_MINUTES', 10); 
define('SIMULATION_MODE', true);  
define('MAX_WITHDRAWAL_ATTEMPTS', 3);

// Business Timings / System
date_default_timezone_set(TIMEZONE);


?>
