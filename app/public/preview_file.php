<?php
require_once __DIR__ . '/file_functions.php';
ensure_session();

$fname = $_GET['file'] ?? '';
$fname = rawurldecode($fname);
$uploads = get_upload_dir();
$path = realpath($uploads . DIRECTORY_SEPARATOR . $fname);

// Try to get file from MinIO first if available
$objectKey = basename($fname);
$useMinIO = false;
$minioUrl = null;

if (function_exists('minio_file_exists') && minio_file_exists($objectKey)) {
    $useMinIO = true;
    $metadata = minio_get_file_metadata($objectKey);
    if ($metadata) {
        $mime = $metadata['mime'];
        $size = human_filesize($metadata['size'], 2);
        $basename = htmlspecialchars($objectKey);
        
        // Get presigned URL for temporary access (valid for 1 hour)
        $minioUrl = minio_get_presigned_url($objectKey, 3600);
    }
}

// Fallback to local file
if (!$useMinIO) {
    if ($path === false || strpos($path, realpath($uploads)) !== 0 || !is_file($path)) {
        echo '<div class="text-danger">File tidak ditemukan.</div>';
        exit;
    }
    
    $mime = mime_content_type($path) ?: 'application/octet-stream';
    $basename = htmlspecialchars(basename($path));
    $size = human_filesize(filesize($path), 2);
}

if (strpos($mime, 'image/') === 0) {
    // show image
    if ($useMinIO && $minioUrl) {
        $url = htmlspecialchars($minioUrl);
    } else {
        $url = 'uploads/' . rawurlencode(basename($path));
    }
    echo "<img src=\"{$url}\" class=\"img-fluid\" alt=\"{$basename}\" style=\"max-height:480px;\" />";
    echo "<div class=\"text-muted small mt-2\">{$basename} — {$size}";
    if ($useMinIO) {
        echo " <span class=\"badge bg-info\">MinIO</span>";
    }
    echo "</div>";
} else {
    // show file info and download link
    echo "<div class=\"text-center\">";
    echo "<i class=\"fa fa-file fa-3x text-secondary mb-2\"></i>";
    echo "<div class=\"fw-semibold\">{$basename}</div>";
    echo "<div class=\"text-muted small\">{$mime} — {$size}";
    if ($useMinIO) {
        echo " <span class=\"badge bg-info\">MinIO</span>";
    }
    echo "</div>";
    echo "<div class=\"mt-3\"><a href=\"download.php?file=" . rawurlencode(basename($fname)) . "\" class=\"btn btn-sm btn-primary\">Download</a></div>";
    echo "</div>";
}
