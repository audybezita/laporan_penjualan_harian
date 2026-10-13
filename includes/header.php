<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Donat NS - Dashboard</title>
  <link href="/laporan_penjualan_harian/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="/laporan_penjualan_harian/assets/css/style.css" rel="stylesheet">
  <script src="/laporan_penjualan_harian/assets/js/chart.umd.min.js"></script>
</head>
<body>
<nav class="navbar topnav">
  <div class="container-fluid">
    <a class="navbar-brand" href="/laporan_penjualan_harian/admin/dashboard.php">Donat NS</a>
    <div class="d-flex align-items-center">
      <span class="me-3">Hai, Pemilik</span>
      <a class="btn btn-outline-danger btn-sm" href="/laporan_penjualan_harian/logout.php">Logout</a>
    </div>
  </div>
</nav>
<div class="d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="p-4 content-area">
