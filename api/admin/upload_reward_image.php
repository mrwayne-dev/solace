<?php
// ===============================================
// FILE: /api/admin/upload_reward_image.php
// PURPOSE: Admin uploads an X-Rewards product image (multipart/form-data).
// Field: image | Stores to /uploads/products/{ts}_{rand}.ext, returns web path.
// ===============================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

function respond($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    respond('error', 'No file uploaded or upload failed.');
}

$file = $_FILES['image'];

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

$uploadDir = __DIR__ . '/../../uploads/products/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

$filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = $uploadDir . $filename;
$webPath  = '/uploads/products/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    respond('error', 'Could not save the uploaded image.');
}

respond('success', 'Image uploaded.', ['image_path' => $webPath]);
