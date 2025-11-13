<?php
// index.php - Clario Cloud Storage Frontend
require_once __DIR__ . '/file_functions.php';
ensure_session();
$me = current_user();

// ambil alert SweetAlert dari session (di-set oleh auth_actions.php)
$auth_alert = $_SESSION['auth_alert'] ?? null;
if (isset($_SESSION['auth_alert'])) {
    unset($_SESSION['auth_alert']);
}
$auth_msg = $_SESSION['auth_message'] ?? null;
unset($_SESSION['auth_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clario Cloud Storage</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body style="background-color: #f9f9f9;">
    <div class="d-flex">
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main -->
    <div class="main flex-grow-1 p-4">
        <!-- Header Search -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Beranda</h4>
            <div class="d-flex align-items-center header-controls">
                <div class="search-bar d-flex align-items-center">
                    <input type="text" class="form-control rounded-pill" placeholder="Telusuri file..." style="background-color:#d4dedf; width:280px;">
                </div>
                <span class="iconify ms-3 fs-5" data-icon="mdi:settings" title="Pengaturan" role="button"></span>

                <!-- user icon / open modal -->
                <div class="ms-3" style="position:relative;">
                    <button class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#userModal" title="User">
                        <span class="iconify fs-4 me-2" data-icon="mdi:account-circle"></span>
                        <?php if ($me): ?>
                            <div class="text-end me-1 d-none d-sm-block">
                                <div class="fw-semibold small mb-0"><?= htmlspecialchars($me['username']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($me['role']) ?></div>
                            </div>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <div class="header-section d-flex justify-content-between align-items-center mb-4">
            <div class="welcome-text">
                <p class="fs-5 mb-1">Selamat datang di <span class="text-info fw-semibold">Clario</span>!</p>
                <h6 class="fw-bold mt-3">Baru-baru ini diunggah</h6>
                <p class="text-muted small">Lihat file yang baru-baru ini diunggah.</p>
            </div>

            <!-- View Switcher -->
            <div class="view-toggle">
                <button class="toggle-btn active" id="grid-view" title="Tampilan Kotak">
                    <span class="iconify" data-icon="mdi:view-grid-outline" data-width="18"></span>
                </button>
                <button class="toggle-btn" id="list-view" title="Tampilan Daftar">
                    <span class="iconify" data-icon="mdi:view-list-outline" data-width="18"></span>
                </button>
            </div>
        </div>

        <!-- File Cards -->
        <div class="row g-3 mt-3">
            <?php
            $files = [
                ["name" => "Foto_dibali.jpg", "icon" => "fa-image", "size" => "5 MB"],
                ["name" => "dadali.mp3", "icon" => "fa-music", "size" => "3,1 MB"],
                ["name" => "laporan_pkl.docx", "icon" => "fa-file-word", "size" => "488 KB"],
                ["name" => "gustracing.mp4", "icon" => "fa-play", "size" => "50 MB"]
            ];
            foreach ($files as $file) {
                echo '
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="file-card text-center p-3 shadow-sm">
                        <i class="fa ' . $file['icon'] . ' fa-2x mb-2 text-info"></i>
                        <p class="mb-1 fw-semibold small">' . $file['name'] . '</p>
                        <p class="text-muted small">' . $file['size'] . '</p>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;">
      <div class="modal-header border-0" style="padding-top:18px;padding-right:18px;padding-left:18px;">
        <div class="w-100 text-center small text-muted" style="position:relative;top:4px;">
          <?php
            // show email if available, otherwise username
            $topLine = $me['email'] ?? $me['username'] ?? '';
            echo htmlspecialchars($topLine);
          ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position:absolute;right:12px;top:8px;"></button>
      </div>

      <div class="modal-body text-center">
        <?php if ($me): ?>
            <?php
              // compute display name (part before @ if looks like email)
              $display = $me['username'] ?? '';
              if (strpos($display, '@') !== false) {
                  $namePart = strstr($display, '@', true);
              } else {
                  $namePart = $display;
              }

              // compute storage usage (sum files in uploads)
              $uploadDir = get_upload_dir();
              $used = 0;
              foreach (scandir($uploadDir) as $f) {
                  if ($f === '.' || $f === '..') continue;
                  $p = $uploadDir . '/' . $f;
                  if (is_file($p)) $used += filesize($p);
              }
              $total = 5 * 1024 * 1024 * 1024; // 5 GB
              $percent = $total > 0 ? round(($used / $total) * 100) : 0;
              $usedDisplay = human_filesize($used, 2);
            ?>

            <div class="mb-3">
                <div style="width:86px;height:86px;margin:0 auto;border-radius:50%;background:#e9f0f0;display:flex;align-items:center;justify-content:center;">
                    <span class="iconify" data-icon="mdi:account" data-width="44" style="color:#0b4a4a;"></span>
                </div>
            </div>

            <h5 class="mb-1">Halo, <?= htmlspecialchars($namePart ?: $display) ?>.</h5>

            <div class="card mx-auto my-3" style="max-width:320px;background:#eef9f9;border:0;border-radius:12px;padding:14px;">
                <div class="small text-muted mb-2"><?= $percent ?>% dari 5 GB telah digunakan</div>
                <div class="progress" style="height:8px;border-radius:6px;">
                  <div class="progress-bar bg-info" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between small mt-2 text-muted">
                    <div><?= htmlspecialchars($usedDisplay) ?></div>
                    <div>5 GB</div>
                </div>
                <div class="mt-2">
                    <a href="#" class="d-block small text-info">Dapatkan penyimpanan</a>
                    <a href="#" class="d-block small text-info">Kosongkan penyimpanan</a>
                </div>
            </div>

            <form method="post" action="auth_actions.php" class="d-flex justify-content-center gap-2 mt-2">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-outline-secondary" style="min-width:110px;">
                    <span class="iconify" data-icon="mdi:logout" data-width="16" style="vertical-align:middle;"></span>
                    &nbsp;Log out
                </button>
            </form>

        <?php else: ?>

            <form method="post" action="auth_actions.php" class="px-2">
                <input type="hidden" name="action" value="login">
                <div class="mb-2 text-start">
                    <label class="form-label small">Username</label>
                    <input name="username" type="text" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label small">Password</label>
                    <input name="password" type="password" class="form-control form-control-sm" required>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Masuk</button>
                </div>
            </form>

        <?php endif; ?>

        <?php if ($auth_msg): ?>
            <div class="mt-3 small text-center text-secondary"><?= htmlspecialchars($auth_msg) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($auth_alert): 
        $type = $auth_alert['type'] === 'success' ? 'success' : 'error';
        $text = addslashes($auth_alert['text']);
    ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= $type ?>',
        title: '<?= $text ?>',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });
    <?php endif; ?>

    // keep existing UI script (grid/list toggle)
    const gridBtn = document.getElementById('grid-view');
    const listBtn = document.getElementById('list-view');
    if (gridBtn && listBtn) {
        gridBtn.addEventListener('click', () => {
          gridBtn.classList.add('active');
          listBtn.classList.remove('active');
        });
        listBtn.addEventListener('click', () => {
          listBtn.classList.add('active');
          gridBtn.classList.remove('active');
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
