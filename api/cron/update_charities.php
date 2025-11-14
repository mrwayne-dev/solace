<?php
// ===============================================
// FILE: /api/cron/update_charities.php
// PURPOSE: HealthRunCare — User Impact & Wallet Sync Cron
// - Syncs wallet donation totals with charity_donations
// - Randomly grows user impact metrics based on donations
// - Logs every step for debugging
// ===============================================

date_default_timezone_set('UTC');

// ---- LOG FILE ----
$logFile = __DIR__ . '/charity_cron.log';
file_put_contents($logFile, "\n==============================\n[" . date('Y-m-d H:i:s') . "] User Impact Cron started\n", FILE_APPEND);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';


// ---- Step 1: Test PHP execution ----
file_put_contents($logFile, "✅ Step 1: PHP loaded successfully.\n", FILE_APPEND);

try {
    $pdo = getPDO();
    file_put_contents($logFile, "✅ Step 2: Database connected successfully.\n", FILE_APPEND);
} catch (Exception $e) {
    $err = "❌ DB Connection failed: " . $e->getMessage();
    file_put_contents($logFile, $err . "\n", FILE_APPEND);
    exit(1);
}

// ===========================================
// 1️⃣ SYNC WALLET TOTALS WITH REAL DONATIONS
// ===========================================
try {
    $syncStmt = $pdo->query("
        UPDATE wallets w
        JOIN (
            SELECT user_id, COALESCE(SUM(amount),0) AS total_donated
            FROM charity_donations
            GROUP BY user_id
        ) c ON w.user_id = c.user_id
        SET w.total_donations = c.total_donated
    ");
    $affected = $syncStmt->rowCount();
    file_put_contents($logFile, "🔄 Step 3: Wallet totals synced for {$affected} users.\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, "⚠️ Wallet sync error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// ===========================================
// 2️⃣ FETCH USERS WITH DONATION ACTIVITY
// ===========================================
try {
    $stmt = $pdo->prepare("
        SELECT 
            user_id, 
            SUM(amount) AS total_donated, 
            COUNT(DISTINCT charity_id) AS charities_supported
        FROM charity_donations
        GROUP BY user_id
        HAVING total_donated > 0
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents($logFile, "✅ Step 4: Found " . count($users) . " users with donation activity.\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, "❌ Step 4 Error: " . $e->getMessage() . "\n", FILE_APPEND);
    exit(1);
}

if (empty($users)) {
    file_put_contents($logFile, "⚠️ No users with donation activity found. Exiting.\n", FILE_APPEND);
    exit(0);
}

// ===========================================
// 3️⃣ RANDOMIZED IMPACT GROWTH LOGIC
// ===========================================
foreach ($users as $u) {
    $uid = (int)$u['user_id'];
    $total = (float)$u['total_donated'];
    $charitiesSupported = (int)$u['charities_supported'];

    // --- Growth factors ---
    $volumeFactor = max(0.5, min(10, $total / 100)); // donation-based scaling
    $breadthFactor = 1 + ($charitiesSupported * 0.2); // more charities = more reach
    $growthRate = $volumeFactor * $breadthFactor;
    $randomness = rand(85, 115) / 100; // ±15%

    // --- Metric increments ---
    $peopleInc = max(1, floor($growthRate * rand(1, 3)));
    $impactInc = min(100, round($growthRate * rand(1, 5) * $randomness, 2));
    $commInc = ($charitiesSupported >= 3) ? rand(1, 2) : rand(0, 1);
    $packInc = ceil($growthRate * rand(1, 4) * $randomness);

    // --- Ensure record exists ---
    $pdo->prepare("
        INSERT IGNORE INTO user_impacts 
        (user_id, total_contributions, people_helped, impact_score, communities_helped, packages_funded)
        VALUES (?, 0, 0, 0, 0, 0)
    ")->execute([$uid]);

    // --- Sync total_contributions with wallet ---
    $pdo->prepare("
        UPDATE user_impacts ui
        JOIN wallets w ON ui.user_id = w.user_id
        SET ui.total_contributions = w.total_donations
        WHERE ui.user_id = ?
    ")->execute([$uid]);

    // --- Apply randomized growth ---
    $pdo->prepare("
        UPDATE user_impacts 
        SET 
            people_helped = people_helped + :ppl,
            impact_score = LEAST(100, impact_score + :impact),
            communities_helped = communities_helped + :comm,
            packages_funded = packages_funded + :pack,
            updated_at = NOW()
        WHERE user_id = :uid
    ")->execute([
        ':ppl' => $peopleInc,
        ':impact' => $impactInc,
        ':comm' => $commInc,
        ':pack' => $packInc,
        ':uid' => $uid
    ]);

    // --- Log per-user update ---
    $logLine = sprintf(
        "[%s] User #%d → +%d people | +%.1f impact | +%d communities | +%d packages | total=%.2f | charities=%d\n",
        date('Y-m-d H:i:s'),
        $uid,
        $peopleInc,
        $impactInc,
        $commInc,
        $packInc,
        $total,
        $charitiesSupported
    );
    file_put_contents($logFile, $logLine, FILE_APPEND);
}

// ===========================================
// ✅ FINISH
// ===========================================
file_put_contents($logFile, "✅ Step 5: Impact growth completed for " . count($users) . " users.\n\n", FILE_APPEND);
echo "✅ Impact growth updated for " . count($users) . " users.\n";
?>
