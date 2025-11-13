<?php
require_once __DIR__ . '/file_functions.php';
ensure_session();

$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $res = authenticate_user($username, $password);
        if ($res['success']) {
            $_SESSION['auth_alert'] = ['type' => 'success', 'text' => 'Berhasil masuk sebagai ' . $username];
        } else {
            $_SESSION['auth_alert'] = ['type' => 'error', 'text' => $res['message'] ?? 'Invalid credentials'];
        }
    } elseif ($action === 'logout') {
        logout_user();
        $_SESSION['auth_alert'] = ['type' => 'success', 'text' => 'Berhasil keluar'];
    }
    // --- admin actions ---
    elseif (in_array($action, ['add_user', 'delete_user', 'set_quota'])) {
        $check = require_login('admin');
        if (!$check['success']) {
            $_SESSION['auth_alert'] = ['type' => 'error', 'text' => 'Akses ditolak'];
            header('Location: ' . $redirect);
            exit;
        }

        if ($action === 'add_user') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $email = $_POST['email'] ?? '';
            $quota_gb = isset($_POST['quota_gb']) ? floatval($_POST['quota_gb']) : null;
            $quota_bytes = $quota_gb !== null ? (int)round($quota_gb * 1024 * 1024 * 1024) : null;
            $res = add_user($username, $password, $role, $email, $quota_bytes);
            $_SESSION['auth_alert'] = ['type' => $res['success'] ? 'success' : 'error', 'text' => $res['message']];
        } elseif ($action === 'delete_user') {
            $username = trim($_POST['username'] ?? '');
            // prevent deleting self
            $me = current_user();
            if ($me && $me['username'] === $username) {
                $_SESSION['auth_alert'] = ['type' => 'error', 'text' => 'Tidak dapat menghapus akun sendiri'];
            } else {
                $res = delete_user($username);
                $_SESSION['auth_alert'] = ['type' => $res['success'] ? 'success' : 'error', 'text' => $res['message']];
            }
        } elseif ($action === 'set_quota') {
            $username = trim($_POST['username'] ?? '');
            $quota_gb = isset($_POST['quota_gb']) ? floatval($_POST['quota_gb']) : 5;
            $bytes = (int)round($quota_gb * 1024 * 1024 * 1024);
            $res = set_user_quota($username, $bytes);
            $_SESSION['auth_alert'] = ['type' => $res['success'] ? 'success' : 'error', 'text' => $res['message']];
        }
    }
}

header('Location: ' . $redirect);
exit;