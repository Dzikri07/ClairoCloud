<?php
// semuafile.php - Semua File page
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
                <p class="fs-5 mb-1">Semua file kamu</p>
                <h6 class="fw-bold mt-3">Daftar file</h6>
                <p class="text-muted small">Lihat semua file yang telah kamu unggah.</p>
            </div>
            <div>
                <form action="upload.php" method="post" enctype="multipart/form-data" class="d-flex align-items-center">
                    <input type="file" name="upload_file" class="form-control form-control-sm" style="max-width:220px;">
                    <button class="btn btn-sm btn-outline-primary ms-2" type="submit">Unggah</button>
                </form>
            </div>
        </div>

        <?php
        require_once __DIR__ . '/file_functions.php';

        // show upload status messages
        if (isset($_GET['upload'])) {
            if ($_GET['upload'] === 'success') {
                echo '<div class="alert alert-success small">File diunggah: ' . htmlspecialchars($_GET['file']) . '</div>';
            } else {
                $msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan.';
                echo '<div class="alert alert-danger small">' . $msg . '</div>';
            }
        }

        $items = list_files();
        if (empty($items)) {
            // show default image
            echo '<div class="text-center mt-4">';
            echo '<img src="assets/image/defaultNotfound.png" alt="Tidak ada file" style="max-width:240px; opacity:0.9;">';
            echo '<p class="text-muted small mt-2">Belum ada file yang diunggah.</p>';
            echo '</div>';
        } else {
            echo '<div class="row g-3 mt-3">';
            foreach ($items as $it) {
                $ext = strtolower(pathinfo($it['name'], PATHINFO_EXTENSION));
                echo '<div class="col-6 col-sm-4 col-md-3 col-lg-2">';
                echo '<div class="file-card text-center p-3 shadow-sm">';
                if (strpos($it['mime'], 'image/') === 0) {
                    echo '<img src="' . $it['url'] . '" alt="' . htmlspecialchars($it['name']) . '" style="max-width:100%; height:80px; object-fit:cover; border-radius:8px;">';
                } else {
                    echo '<i class="fa fa-file fa-2x mb-2 text-info"></i>';
                }
                echo '<p class="mb-1 fw-semibold small">' . htmlspecialchars($it['name']) . '</p>';
                echo '<p class="text-muted small">' . human_filesize($it['size']) . '</p>';
                echo '</div></div>';
            }
            echo '</div>';
        }
        ?>
</div>
</body>
</html>
