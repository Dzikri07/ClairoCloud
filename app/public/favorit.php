<?php
// favorit.php - Favorit page
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorit - Clario</title>
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
                <p class="fs-5 mb-1">Favorit</p>
                <h6 class="fw-bold mt-3">File favorit</h6>
                <p class="text-muted small">Lihat file yang kamu tandai sebagai favorit.</p>
            </div>
        </div>

        <?php
        // For now favorites feature isn't implemented; show default not-found image
        echo '<div class="text-center mt-4">';
        echo '<img src="assets/image/defaultNotfound.png" alt="Tidak ada file favorit" style="max-width:260px; opacity:0.95;">';
        echo '<p class="text-muted small mt-2">Tidak ada file favorit.</p>';
        echo '</div>';
        ?>
    </div>
</div>
</body>
</html>
