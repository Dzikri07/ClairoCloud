<?php
// sampah.php - Sampah (trash) page
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
</head>
<body style="background-color: #f9f9f9;">
<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main flex-grow-1 p-4">
        <div class="header-section d-flex justify-content-between align-items-center mb-4">
            <div class="welcome-text">
                <p class="fs-5 mb-1">Sampah</p>
                <h6 class="fw-bold mt-3">File yang dihapus</h6>
                <p class="text-muted small">File yang dihapus akan muncul di sini.</p>
            </div>
        </div>

        <?php
        // Trash listing not implemented yet; show default not-found image
        echo '<div class="text-center mt-4">';
        echo '<img src="assets/image/defaultNotfound.png" alt="Tidak ada sampah" style="max-width:260px; opacity:0.95;">';
        echo '<p class="text-muted small mt-2">Tidak ada sampah.</p>';
        echo '</div>';
        ?>
    </div>
</div>
</body>
</html>
