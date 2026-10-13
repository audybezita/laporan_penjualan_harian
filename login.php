<?php
require_once __DIR__ . '/config/database.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $errors[] = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Username atau password salah.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header('Location: /laporan_penjualan_harian/admin/dashboard.php');
            exit;
        }
    }
}
?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Donat NS</title>
  <link href="/laporan_penjualan_harian/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="/laporan_penjualan_harian/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="card-title">Login Pemilik</h4>
          <?php if (!empty($_GET['registered'])): ?>
            <div class="alert alert-success">Akun berhasil dibuat. Silakan login.</div>
          <?php endif; ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
          <?php endif; ?>
          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-pink">Login</button>
          </form>
          <hr>
          <a href="/laporan_penjualan_harian/setup.php" class="small">Buat akun pemilik pertama (jika belum ada)</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
