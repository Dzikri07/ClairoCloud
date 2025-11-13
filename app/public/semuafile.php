<?php
// semuafile.php - Semua File page
require_once __DIR__ . '/file_functions.php';
ensure_session();
$me = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua File - Clario</title>
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background-color: #f9f9f9;">
<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main flex-grow-1 p-4">
        <div class="header-section d-flex justify-content-between align-items-center mb-4">
            <div class="welcome-text">
                <h4 class="fw-bold">Semua file kamu</h4>
                <p class="text-muted small">Lihat semua file yang telah kamu unggah.</p>
            </div>
            <div class="header-actions">
                <!-- upload form removed as requested -->
            </div>
        </div>

        <?php
        // show upload status messages
        if (isset($_GET['upload'])) {
            if ($_GET['upload'] === 'success') {
                echo '<div class="alert alert-success small">File diunggah: ' . htmlspecialchars($_GET['file']) . '</div>';
            } else {
                echo '<div class="alert alert-danger small">Upload gagal</div>';
            }
        }

        $items = list_files(); // expected: array of ['name'=>..., 'size'=>..., 'type'=>...]
        if (empty($items)) {
            echo '<div class="text-center mt-4"><p class="text-muted">Belum ada file.</p></div>';
        } else {
            echo '<div class="row g-3 mt-3">';
            foreach ($items as $it) {
                $fname = htmlspecialchars($it['name']);
                $safe = rawurlencode($it['name']);
                $size = htmlspecialchars($it['size'] ?? '');
                // icon choice based on type if available
                $icon = 'fa-file';
                if (!empty($it['type'])) {
                    if (strpos($it['type'], 'image/') === 0) $icon = 'fa-image';
                    elseif (strpos($it['type'], 'audio/') === 0) $icon = 'fa-music';
                    elseif (strpos($it['type'], 'video/') === 0) $icon = 'fa-play';
                    elseif (strpos($it['type'], 'text/') === 0) $icon = 'fa-file-lines';
                }

                echo <<<HTML
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="file-card text-center p-3 shadow-sm position-relative" data-fname="{$fname}">
                        <i class="fa {$icon} fa-2x mb-2 text-info"></i>
                        <p class="mb-1 fw-semibold small text-truncate" title="{$fname}">{$fname}</p>
                        <p class="text-muted small">{$size}</p>

                        <!-- mini menu -->
                        <div class="dropdown position-absolute" style="right:8px;top:8px;">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Lihat opsi">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-1">
                                <li><a class="dropdown-item btn-download" href="download.php?file={$safe}" target="_blank"><i class="fa fa-download me-2"></i>Download</a></li>
                                <li><button class="dropdown-item btn-preview" data-file="{$safe}"><i class="fa fa-eye me-2"></i>Tinjauan</button></li>
                                <li><form method="post" action="delete_file.php" onsubmit="return confirm('Hapus file {$fname}?');">
                                        <input type="hidden" name="file" value="{$safe}">
                                        <button type="submit" class="dropdown-item text-danger"><i class="fa fa-trash me-2"></i>Hapus</button>
                                    </form></li>
                            </ul>
                        </div>
                    </div>
                </div>
HTML;
            }
            echo '</div>';
        }
        ?>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewTitle">Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="previewBody" style="min-height:180px;display:flex;align-items:center;justify-content:center;">
        <!-- content injected via JS -->
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // preview handler
    document.querySelectorAll('.btn-preview').forEach(function(btn){
        btn.addEventListener('click', function(){
            var file = this.getAttribute('data-file');
            if (!file) return;
            var title = decodeURIComponent(file);
            document.getElementById('previewTitle').textContent = title;
            var body = document.getElementById('previewBody');
            body.innerHTML = '<div class="text-center text-muted">Loading...</div>';

            fetch('preview_file.php?file=' + file)
                .then(function(r){ return r.text(); })
                .then(function(html){
                    body.innerHTML = html;
                    var modal = new bootstrap.Modal(document.getElementById('previewModal'));
                    modal.show();
                })
                .catch(function(){
                    body.innerHTML = '<div class="text-danger">Gagal memuat preview</div>';
                });
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
