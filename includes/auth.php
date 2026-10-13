<?php
require_once __DIR__ . '/../config/database.php';
session_start();

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /laporan_penjualan_harian/login.php');
        exit;
    }
}

function formatRupiah($amount) {
    $amount = (float) $amount;
    return 'Rp' . number_format($amount, 0, ',', '.');
}

function formatTanggalIndonesia($date) {
    if (!$date) {
        return '-';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }
    $bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tanggal = date('d', $timestamp);
    $bulanIdx = (int) date('n', $timestamp) - 1;
    $tahun = date('Y', $timestamp);
    return $tanggal . ' ' . $bulan[$bulanIdx] . ' ' . $tahun;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

function currentUser() {
    global $pdo;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
