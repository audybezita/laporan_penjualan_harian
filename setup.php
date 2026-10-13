<?php
require_once __DIR__ . '/config/database.php';
session_start();

$errors = [];

// Jika sudah ada user dan user mencoba setup, redirect ke login
$stmt = $pdo->query('SELECT COUNT(*) as c FROM users');
$row = $stmt->fetch();
$users_exist = ($row && $row['c'] > 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Buat akun pertama bila belum ada user
    if ($users_exist) {
        $errors[] = 'Akun sudah ada. Gunakan halaman login.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($username === '' || $password === '') {
            $errors[] = 'Username dan password wajib diisi.';
        } elseif ($password !== $confirm) {
            $errors[] = 'Password dan konfirmasi tidak cocok.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$username, $hash]);
            header('Location: /laporan_penjualan_harian/login.php?registered=1');
            exit;
        }
    }
}
?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup Akun Pemilik — Donat NS</title>
  <link href="/laporan_penjualan_harian/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="/laporan_penjualan_harian/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="card-title">Buat Akun Pemilik (Setup Awal)</h4>
          <?php if ($users_exist): ?>
            <div class="alert alert-warning">Sudah ada akun. Silakan gunakan halaman <a href="/laporan_penjualan_harian/login.php">Login</a>.</div>
          <?php endif; ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
          <?php endif; ?>
          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input name="username" class="form-control" required value="">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Konfirmasi Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button class="btn btn-primary">Buat Akun</button>
          </form>
          <hr>
          <small class="text-muted">Petunjuk: buat username dan password yang kuat. Simpan kredensial dengan aman. Jika Anda ragu, buka file database/donat_ns.sql untuk struktur tabel.</small>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
