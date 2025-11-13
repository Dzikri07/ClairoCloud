<?php
// sidebar.php - extracted sidebar markup with active link detection
$current = basename($_SERVER['SCRIPT_NAME']);

// pastikan helper tersedia ketika sidebar di-include dari halaman lain
if (!function_exists('ensure_session')) {
    require_once __DIR__ . '/file_functions.php';
}
ensure_session();
$me = current_user();
?>
<div class="sidebar p-3 d-flex flex-column">
    <div class="d-flex justify-content-center align-items-center mb-4 logo-container">
        <img src="assets/image/clairo.png" alt="logo" class="me-2" style="width: 70px;">
        <h4 class="fw-bold logo-text mb-0" style="font-family: 'Krona One', sans-serif;">
            <span class="text-teal">C</span>lario
        </h4>
    </div>

    <!-- Sidebar upload: hidden form + file input. Clicking the button opens file picker and submits to upload.php -->
    <form id="sidebar-upload-form" action="upload.php" method="post" enctype="multipart/form-data" style="display:none;">
        <input type="file" name="upload_file" id="sidebar-upload-input">
    </form>
    <button id="sidebar-upload-btn" class="upload-btn mb-4" type="button">
        <i class="fa fa-plus me-1"></i> Upload
    </button>

    <ul class="nav flex-column mb-4">
        <li class="nav-item"><a href="index.php" class="nav-link <?php echo ($current === 'index.php') ? 'active' : ''; ?>"><i class="fa fa-home me-2"></i> Beranda</a></li>
        <li class="nav-item"><a href="semuafile.php" class="nav-link <?php echo ($current === 'semuafile.php') ? 'active' : ''; ?>"><i class="fa fa-layer-group me-2"></i> Semua File</a></li>
        <li class="nav-item"><a href="favorit.php" class="nav-link <?php echo ($current === 'favorit.php') ? 'active' : ''; ?>"><i class="fa fa-star me-2"></i> Favorit</a></li>
        <li class="nav-item"><a href="sampah.php" class="nav-link <?php echo ($current === 'sampah.php') ? 'active' : ''; ?>"><i class="fa fa-trash me-2"></i> Sampah</a></li>

        <!-- tampilkan menu khusus admin -->
        <?php if ($me && isset($me['role']) && $me['role'] === 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link text-danger" href="admin_users.php"><i class="fa fa-users-cog me-2"></i>Kelola User</a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="storage mt-auto">
        <p class="fw-bold small mb-1">Penyimpanan</p>
        <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-info" style="width: 70%;"></div>
        </div>
        <p class="small text-muted mt-1">3,5 GB dari 5 GB Terpakai</p>
    </div>
</div>

<script>
// Sidebar upload button behaviour: trigger hidden file input and submit automatically
document.addEventListener('DOMContentLoaded', function () {
    var uploadBtn = document.getElementById('sidebar-upload-btn');
    var fileInput = document.getElementById('sidebar-upload-input');
    var form = document.getElementById('sidebar-upload-form');

    if (uploadBtn && fileInput && form) {
        uploadBtn.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                form.submit();
            }
        });
    }
});
</script>
