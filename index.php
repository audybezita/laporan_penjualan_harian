<?php
require_once __DIR__ . '/../config/database.php';

// Sederhana: jika sudah login, arahkan ke dashboard, jika belum -> login
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /laporan_penjualan_harian/admin/dashboard.php');
    exit;
} else {
    header('Location: /laporan_penjualan_harian/login.php');
    exit;
}
