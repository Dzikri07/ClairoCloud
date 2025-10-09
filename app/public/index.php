<?php
// index.php - Clario Cloud Storage Frontend
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
                <span class="iconify ms-3 fs-5" data-icon="mdi:settings" title="Pengaturan"></span>
                <i class="fa fa-user fs-5 ms-3" title="Akun"></i>
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

<!-- JS -->
<script>
const gridBtn = document.getElementById('grid-view');
const listBtn = document.getElementById('list-view');

gridBtn.addEventListener('click', () => {
  gridBtn.classList.add('active');
  listBtn.classList.remove('active');
});

listBtn.addEventListener('click', () => {
  listBtn.classList.add('active');
  gridBtn.classList.remove('active');
});
</script>
</body>
</html>
