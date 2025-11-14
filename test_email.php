<?php
require_once __DIR__ . '/api/backend/email.php';

$result = sendEmail([
    'to' => 'aleruchi0987@gmail.com',
    'subject' => 'Test Email from HealthRunCare',
    'body' => '<h2>Hello!</h2><p>This is a test email from HealthRunCare via port 587.</p>'
]);

echo $result ? "✅ Email sent successfully!" : "❌ Failed to send email. Check PHP error log.";
?>
