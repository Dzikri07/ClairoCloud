<?php
require_once __DIR__ . '/file_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$result = handle_upload('upload_file');
	if ($result['success']) {
		header('Location: semuafile.php?upload=success&file=' . rawurlencode($result['file']));
		exit;
	} else {
		header('Location: semuafile.php?upload=error&msg=' . rawurlencode($result['message']));
		exit;
	}
}

// if accessed directly via GET, show a small form
?>
<!DOCTYPE html>
<html><body>
<form action="upload.php" method="post" enctype="multipart/form-data">
	<input type="file" name="upload_file">
	<button type="submit">Upload</button>
</form>
</body></html>