<?php
$apiKey = '6RZ3S39-7NK4238-M99BAHW-JQMDQHG'; // <-- your test API key

$payload = [
  'price_amount' => 10,
  'price_currency' => 'usd',
  'order_id' => 'TEST-LOCAL',
  'order_description' => 'Local test payment (SSL bypass)',
];

// Initialize cURL
$ch = curl_init('https://api.nowpayments.io/v1/invoice');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    'x-api-key: ' . $apiKey,
    'Content-Type: application/json'
  ],
  CURLOPT_POSTFIELDS => json_encode($payload),
  CURLOPT_TIMEOUT => 30,
  CURLOPT_CONNECTTIMEOUT => 10,

  // 👇 TEMPORARY SSL BYPASS (for localhost only)
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Output result
header('Content-Type: text/plain');
echo "HTTP Code: $httpCode\n";
if ($error) echo "cURL Error: $error\n";
echo "Response:\n$response\n";
?>
