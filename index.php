<?php
require_once __DIR__ . '/../config/database.php';

session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /laporan_penjualan_harian/admin/dashboard.php');
    exit;
}
header('Location: /laporan_penjualan_harian/login.php');
exit;
