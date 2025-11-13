<?php
// sampah.php - Sampah (trash) page
require_once __DIR__ . '/file_functions.php';
ensure_session();
$me = current_user();
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

// purge old items (30 hari)
purge_old_trash(30);
$index = load_trash_index();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sampah - Clario</title>
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color:#f9f9f9;">
<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Sampah</h4>
                <p class="text-muted small mb-0">File yang dihapus akan disimpan sementara selama 30 hari.</p>
            </div>
            <div>
                <a href="semuafile.php" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </div>

        <?php if (empty($index)): ?>
            <div class="card p-4 text-center text-muted">
                <i class="fa fa-trash fa-2x mb-2"></i>
                <div class="fw-semibold">Sampah kosong</div>
                <div class="small">Tidak ada file yang dapat dipulihkan.</div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($index as $id => $m):
                    $deleted_at = date('Y-m-d H:i', $m['deleted_at']);
                    $size = human_filesize($m['size'] ?? 0, 2);
                    $canManage = ($me && ($me['role'] === 'admin' || $me['username'] === ($m['deleted_by'] ?? '')));
                ?>
                <div class="col-12 col-md-6">
                    <div class="card p-3">
                        <div class="d-flex gap-3">
                            <div style="width:64px;height:64px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#f5f7f7;">
                                <i class="fa fa-file fa-2x text-secondary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($m['original_name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($m['mime']) ?> • <?= $size ?></div>
                                        <div class="small text-muted">Dihapus: <?= $deleted_at ?> oleh <?= htmlspecialchars($m['deleted_by'] ?? '-') ?></div>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($canManage): ?>
                                            <form method="post" action="restore_file.php" style="display:inline">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                                                <button class="btn btn-sm btn-outline-primary mb-2">Restore</button>
                                            </form>
                                            <form method="post" action="trash_permanent_delete.php" style="display:inline" onsubmit="return confirm('Hapus permanen <?= addslashes($m['original_name']) ?> ?');">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                                                <button class="btn btn-sm btn-danger">Hapus Permanen</button>
                                            </form>
                                        <?php else: ?>
                                            <div class="small text-muted">Tidak dapat dikelola</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
<?php
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
