<?php
// send_bulk.php
// Run: php send_bulk.php

define('TEST_MODE', false);  // 👈 Switch to true for testing
define('TEST_EMAIL', 'aleruchi0987@gmail.com');
define('SUPPORT_EMAIL', 'support@lymora.tech');
define('SEND_SUPPORT_COPY', true);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/api/backend/email.php';
require_once __DIR__ . '/api/utilities/email_temps.php';

if (php_sapi_name() !== 'cli') {
    die("❌ Run this script from command line only.\n");
}

// Connect DB
try {
    $pdo = getPDO();
} catch (Exception $e) {
    die("❌ DB connection failed: " . $e->getMessage() . "\n");
}

// Decide recipients
if (TEST_MODE) {
    echo "🧪 TEST MODE → Only sending to: " . TEST_EMAIL . "\n";
    $recipients = [[ 'email' => TEST_EMAIL, 'first_name' => 'Test' ]]; 
} else {
    echo "⚠️ LIVE MODE → Fetching all active users...\n";
    $stmt = $pdo->query("SELECT email, first_name FROM users WHERE status = 'active'");
    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($recipients)) {
        die("❌ No active users found.\n");
    }

    echo "📬 Found " . count($recipients) . " users.\n";
    echo "Type YES to confirm sending: ";
    $line = trim(fgets(STDIN));
    if ($line !== "YES") {
        echo "🛑 Aborted.\n";
        exit;
    }
}

// --- EMAIL CONTENT ---
$subject = "Lymora Learn Has Closed";

$sent = 0; $failed = 0;
echo "\n🚀 Sending emails...\n";

foreach ($recipients as $row) {
    $toEmail    = $row['email'];
    $firstName  = ucfirst(strtolower($row['first_name'] ?? 'there'));

    // Personalized body
    $contentHtml = "

<p>At 00:00:00, <strong>Lymora Learn officially closed</strong>.</p>

<p>No extensions. No exceptions.</p>

<p>This was our flagship project, and if you were inside, you already know why it mattered.<br>
If you weren’t, the window is gone.</p>

<p>But this isn’t the end of Lymora.<br>
It’s the start of what comes next.</p>

<p><em>The Lymora Team<em></p>
";

    $templateData = [
        'html' => buildEmailTemplate($subject, "Hi {$firstName},", $contentHtml),
        'text' => strip_tags(str_replace(['<p>', '</p>'], "\n", $contentHtml))
    ];

    echo "✉️  Sending to: {$toEmail} ... ";

    $ok = sendEmail($toEmail, $subject, 'custom', $templateData);

    if ($ok) {
        if (SEND_SUPPORT_COPY) {
            sendEmail(SUPPORT_EMAIL, "COPY: " . $subject, 'custom', $templateData);
        }
        echo "✅ Sent\n"; $sent++;
    } else {
        echo "❌ Failed\n"; $failed++;
    }

    usleep(200000); // 0.2s delay
}

// --- SUMMARY ---
echo "\n📊 SUMMARY\n";
echo "✅ Sent: $sent\n";
echo "❌ Failed: $failed\n";
echo "📬 Total: " . ($sent + $failed) . "\n";
