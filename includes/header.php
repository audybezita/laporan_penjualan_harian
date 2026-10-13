<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Donat NS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/laporan_penjualan_harian/assets/css/style.css">
</head>
<body>
<nav class="navbar topnav px-3">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-pink" href="/laporan_penjualan_harian/admin/dashboard.php">Donat NS</a>
    <div class="d-flex align-items-center gap-3">
      <span class="small text-muted">Pemilik</span>
      <span class="fw-semibold"><?php echo htmlspecialchars(currentUser()['username'] ?? 'Pemilik'); ?></span>
      <a href="/laporan_penjualan_harian/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>
<div class="d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="content-area p-4">
