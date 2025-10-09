<?php
// index.php - Clario Cloud Storage Frontend
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clario Cloud Storage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3 d-flex flex-column">
            <div class="text-center mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/6195/6195699.png" alt="logo" class="logo mb-2">
                <h4 class="fw-bold text-primary">Clario</h4>
            </div>
            <button class="btn btn-light w-100 mb-3 shadow-sm upload-btn">
                <i class="fa fa-plus me-2"></i> Upload
            </button>
            <ul class="nav flex-column mb-4">
                <li class="nav-item"><a href="#" class="nav-link active"><i class="fa fa-home me-2"></i> Beranda</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fa fa-layer-group me-2"></i> Semua File</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fa fa-star me-2"></i> Favorit</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="fa fa-trash me-2"></i> Sampah</a></li>
            </ul>
            <div class="storage mt-auto">
                <p class="fw-bold small mb-1">Penyimpanan</p>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-info" style="width: 70%;"></div>
                </div>
                <p class="small text-muted mt-1">3,5 GB dari 5 GB Terpakai</p>
            </div>
        </div>

        <!-- Main -->
        <div class="main flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Beranda</h4>
                <div class="d-flex align-items-center">
                    <input type="text" class="form-control rounded-pill" placeholder="Telusuri file..." style="width: 300px;">
                    <i class="fa fa-gear ms-3 fs-5"></i>
                    <i class="fa fa-user ms-3 fs-5"></i>
                </div>
            </div>

            <p class="fs-5">Selamat datang di <span class="text-info">Clario</span>!</p>
            <h6 class="fw-bold mt-4">Baru-baru ini diunggah</h6>
            <p class="text-muted small">Lihat file yang baru-baru ini diunggah.</p>

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
</body>
</html>
