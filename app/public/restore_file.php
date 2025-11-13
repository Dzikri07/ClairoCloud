<?php
require_once __DIR__ . '/file_functions.php';
ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sampah.php');
    exit;
}

$id = $_POST['id'] ?? '';
$me = current_user();
$index = load_trash_index();
if (!isset($index[$id])) {
    $_SESSION['auth_alert'] = ['type' => 'error', 'text' => 'Item tidak ditemukan'];
    header('Location: sampah.php');
    exit;
}

// only admin or original deleter can restore
if (!($me && ($me['role'] === 'admin' || $me['username'] === ($index[$id]['deleted_by'] ?? '')))) {
    $_SESSION['auth_alert'] = ['type' => 'error', 'text' => 'Akses ditolak'];
    header('Location: sampah.php');
    exit;
}

$res = restore_from_trash($id);
$_SESSION['auth_alert'] = ['type' => $res['success'] ? 'success' : 'error', 'text' => $res['message']];
header('Location: sampah.php');
exit;