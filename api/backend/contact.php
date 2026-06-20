<?php
// ========================================
// CONTACT FORM HANDLER — Solace Mining
// Receives the public /contact form, validates input, stores any
// attachment, emails support, and auto-acknowledges the sender.
// Responds with JSON for AJAX, or redirects back for plain POST.
// ========================================

ini_set('display_errors', 0);
error_reporting(0);
ob_start();

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/helpers.php';
require_once __DIR__ . '/email.php';

// Is this an AJAX/fetch request? (decides JSON vs redirect)
$isAjax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
);

function respond($ok, $message, $isAjax, $extra = []) {
    ob_clean();
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['status' => $ok ? 'success' : 'error', 'message' => $message], $extra));
    } else {
        $q = $ok ? 'sent=1' : ('error=' . urlencode($message));
        header('Location: ' . rtrim(APP_URL, '/') . '/contact?' . $q);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', $isAjax);
}

// --- Collect + validate input ---
$name    = cleanInput($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$type    = cleanInput($_POST['type'] ?? '');
$service = cleanInput($_POST['service'] ?? '');
$subject = cleanInput($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $type === '' || $subject === '' || $message === '') {
    respond(false, 'Please fill in all required fields.', $isAjax);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.', $isAjax);
}
if (mb_strlen($name) > 100 || mb_strlen($subject) > 150 || mb_strlen($message) > 5000) {
    respond(false, 'One of your fields is too long.', $isAjax);
}

// --- Optional attachment ---
$attachmentNote = 'None';
$attachmentUrl  = '';
if (!empty($_FILES['attachment']) && ($_FILES['attachment']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $f = $_FILES['attachment'];
    $allowed = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) {
        respond(false, 'Attachment must be a PDF, JPG, PNG, DOC or DOCX file.', $isAjax);
    }
    if ($f['size'] > 5 * 1024 * 1024) {
        respond(false, 'Attachment must be 5 MB or smaller.', $isAjax);
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, array_values($allowed), true)) {
        respond(false, 'That file type is not allowed.', $isAjax);
    }
    $dir = __DIR__ . '/../../uploads/contacts';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $safeName = 'contact_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $safeName)) {
        respond(false, 'We could not save your attachment. Please try again.', $isAjax);
    }
    $attachmentUrl  = rtrim(APP_URL, '/') . '/uploads/contacts/' . $safeName;
    $attachmentNote = $attachmentUrl;
}

// --- Notify support + acknowledge sender ---
$reference = 'CNT-' . strtoupper(bin2hex(random_bytes(3)));
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$adminResult = sendEmail([
    'to'       => ADMIN_CONTACT_EMAIL,
    'template' => 'contact_message',
    'subject'  => 'New contact message: ' . $subject,
    'variables' => [
        'name'         => $name,
        'email'        => $email,
        'type'         => $type ?: '—',
        'service'      => $service ?: '—',
        'subject'      => $subject,
        'attachment'   => $attachmentNote,
        'message_body' => nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
        'submitted_at' => date('Y-m-d H:i:s'),
        'ip'           => $ip,
    ],
]);

// Auto-acknowledgement to the sender (best-effort; don't fail the request if it bounces)
sendEmail([
    'to'       => $email,
    'template' => 'contact_received',
    'variables' => [
        'name'      => $name,
        'subject'   => $subject,
        'reference' => $reference,
    ],
]);

$adminOk = is_array($adminResult) ? !empty($adminResult['success']) : (bool)$adminResult;
if (!$adminOk) {
    respond(false, 'Something went wrong sending your message. Please email support@solacemining.org directly.', $isAjax);
}

respond(true, 'Message sent! Please check your email.', $isAjax, ['reference' => $reference]);
