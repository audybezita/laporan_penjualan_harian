<?php
require_once __DIR__ . '/config/database.php';
session_start();

$errors = [];
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userCount > 0) {
        $errors[] = 'Akun sudah dibuat. Silakan login.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($username === '') {
            $errors[] = 'Username tidak boleh kosong.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Password konfirmasi tidak cocok.';
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$username, $hash]);
            header('Location: /laporan_penjualan_harian/login.php?registered=1');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup Akun Pemilik</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/laporan_penjualan_harian/assets/css/style.css">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="mb-3">Setup akun pemilik</h3>
            <?php if ($userCount > 0): ?>
              <div class="alert alert-warning">Akun pemilik sudah ada. Silakan <a href="/laporan_penjualan_harian/login.php">login</a>.</div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
            <?php endif; ?>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <button class="btn btn-primary w-100">Buat akun</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
