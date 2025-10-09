<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $envUser = getenv('APP_USER') ?: 'admin';
    $envPass = getenv('APP_PASS') ?: 'secret123';
    if ($user === $envUser && $pass === $envPass) {
        $_SESSION['user'] = $user;
        header('Location: /');
        exit;
    } else {
        $error = "Login gagal";
    }
}
?>
<!doctype html><html><head>
<meta charset="utf-8"><title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-5">
<div class="container" style="max-width:420px">
  <h3>Login</h3>
  <?php if(!empty($error)): ?>
    <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>
  <form method="post">
    <div class="mb-3"><label class="form-label">User</label>
      <input name="user" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Password</label>
      <input name="pass" type="password" class="form-control" required></div>
    <button class="btn btn-primary">Login</button>
  </form>
</div>
</body></html>
