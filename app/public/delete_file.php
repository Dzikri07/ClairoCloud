<?php
require_once __DIR__ . '/file_functions.php';
ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: semuafile.php');
    exit;
}

$fname = $_POST['file'] ?? '';
$fname = rawurldecode($fname);
$uploads = get_upload_dir();
$path = realpath($uploads . DIRECTORY_SEPARATOR . $fname);

// basic safety: ensure file is inside uploads dir
if ($path === false || strpos($path, realpath($uploads)) !== 0 || !is_file($path)) {
    $_SESSION['auth_alert'] = ['type' => 'error', 'text' => 'File tidak ditemukan atau akses ditolak'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'semuafile.php'));
    exit;
}

// require login
$check = require_login();
if (!$check['success']) {
    $_SESSION['auth_alert'] = ['type' => 'error', 'text' => 'Harus login untuk menghapus file'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'semuafile.php'));
    exit;
}

// Check if file also exists in MinIO and delete it
$objectKey = basename($path);
if (function_exists('minio_file_exists') && minio_file_exists($objectKey)) {
    minio_delete_file($objectKey);
}

// move to trash instead of deleting permanently
$res = move_to_trash($path, basename($path), $check['user']['username'] ?? null);
if ($res['success']) {
    $_SESSION['auth_alert'] = ['type' => 'success', 'text' => $res['message']];
} else {
    $_SESSION['auth_alert'] = ['type' => 'error', 'text' => $res['message']];
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'semuafile.php'));
exit;
