<?php
require_once __DIR__ . '/file_functions.php';
ensure_session();
$check = require_login('admin');
if (!$check['success']) {
    header('Location: index.php');
    exit;
}
$me = current_user();
$users = get_user_store();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola User - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color:#f9f9f9;">
<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Kelola User</h4>
                <p class="text-muted small mb-0">Anda masuk sebagai: <strong><?= htmlspecialchars($me['username']) ?></strong></p>
            </div>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>

        <div class="card mb-3 p-3">
            <h6>Tambah User Baru</h6>
            <form method="post" action="auth_actions.php" class="row g-2">
                <input type="hidden" name="action" value="add_user">
                <div class="col-md-3">
                    <input name="username" class="form-control form-control-sm" placeholder="username" required>
                </div>
                <div class="col-md-3">
                    <input name="password" class="form-control form-control-sm" placeholder="password" required>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select form-select-sm">
                        <option value="user">user</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input name="quota_gb" type="number" step="0.1" class="form-control form-control-sm" placeholder="Quota (GB)" value="5">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-success w-100">Tambah</button>
                </div>
            </form>
        </div>

        <div class="card p-3">
            <h6>Daftar User</h6>
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr><th>Username</th><th>Role</th><th>Email</th><th>Quota (GB)</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u => $m):
                    $quota_gb = isset($m['quota']) ? round($m['quota'] / (1024*1024*1024), 2) : '-';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($u) ?></td>
                        <td><?= htmlspecialchars($m['role'] ?? '') ?></td>
                        <td><?= htmlspecialchars($m['email'] ?? '') ?></td>
                        <td style="width:160px;">
                            <form method="post" action="auth_actions.php" class="d-flex gap-1 align-items-center mb-0">
                                <input type="hidden" name="action" value="set_quota">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($u) ?>">
                                <input name="quota_gb" type="number" step="0.1" class="form-control form-control-sm" style="width:90px;" value="<?= $quota_gb ?>">
                                <button class="btn btn-sm btn-primary ms-2">Simpan</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($u !== $me['username']): ?>
                            <form method="post" action="auth_actions.php" onsubmit="return confirm('Hapus user <?= htmlspecialchars($u) ?>?');" style="display:inline">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($u) ?>">
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                            <?php else: ?>
                                <span class="text-muted small">--</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<script>
<?php
// SweetAlert toast from session if present
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (isset($_SESSION['auth_alert'])) {
    $a = $_SESSION['auth_alert'];
    unset($_SESSION['auth_alert']);
    $type = ($a['type'] ?? '') === 'success' ? 'success' : 'error';
    $text = addslashes($a['text'] ?? '');
    echo "Swal.fire({toast:true,position:'top-end',icon:'$type',title:'$text',showConfirmButton:false,timer:2200});";
}
?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>