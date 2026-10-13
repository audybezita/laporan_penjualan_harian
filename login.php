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
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Donat NS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/laporan_penjualan_harian/assets/css/style.css">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="mb-3">Login pemilik</h3>
            <?php if (!empty($_GET['registered'])): ?>
              <div class="alert alert-success">Akun berhasil dibuat. Silakan login.</div>
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
              <button class="btn btn-primary w-100">Login</button>
            </form>
            <div class="mt-3 text-center small">
              <a href="/laporan_penjualan_harian/setup.php">Buat akun pemilik pertama</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
