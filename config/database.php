<?php
// ========================================
// DATABASE CONNECTION HANDLER — HealthRunCare
// ========================================

function getPDO() {
    require_once __DIR__ . '/env.php';

    $host = DB_HOST;
    $dbname = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        if (ENV === 'dev') {
            echo "Database connection failed: " . $e->getMessage();
        } else {
            error_log("Database connection failed: " . $e->getMessage());
        }
        exit();
    }
}
?>
