<?php
$connection = @fsockopen('mail.spacemail.com', 465, $errno, $errstr, 10);
if (!$connection) {
    echo "❌ Connection failed: $errstr ($errno)";
} else {
    echo "✅ Connected to mail.spacemail.com:465";
    fclose($connection);
}
?>
