<?php
// ===============================================
// FILE: /api/backend/upload_avatar.php
// PURPOSE: Handle profile photo upload (multipart/form-data).
// Field: avatar | Stores to /uploads/profiles/{user_id}_{ts}.ext
// ===============================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

function respond($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    respond('error', 'No file uploaded or upload failed.');
}

$file = $_FILES['avatar'];

// Size limit: 2 MB
if ($file['size'] > 2 * 1024 * 1024) {
    respond('error', 'Image must be 2 MB or smaller.');
}

// Validate true MIME type
$allowed = [
    'image/png'  => 'png',
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp',
];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!isset($allowed[$mime])) {
    respond('error', 'Only PNG, JPG, or WEBP images are allowed.');
}
$ext = $allowed[$mime];

$uploadDir = __DIR__ . '/../../uploads/profiles/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

$filename = $user_id . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $filename;
$webPath  = '/uploads/profiles/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    respond('error', 'Could not save the uploaded image.');
}

try {
    $pdo = getPDO();

    // Fetch previous picture for best-effort cleanup
    $prevStmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $prevStmt->execute([$user_id]);
    $prev = $prevStmt->fetchColumn();

    $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?")->execute([$webPath, $user_id]);
    $_SESSION['profile_picture'] = $webPath;

    // Delete the old custom upload (never the shared default)
    if ($prev && strpos($prev, '/uploads/profiles/') === 0) {
        $oldFile = __DIR__ . '/../../' . ltrim($prev, '/');
        if (is_file($oldFile)) @unlink($oldFile);
    }

    respond('success', 'Profile photo updated.', ['profile_picture' => $webPath]);
} catch (Throwable $e) {
    error_log('upload_avatar.php: ' . $e->getMessage());
    respond('error', 'Server error while saving image.');
}
