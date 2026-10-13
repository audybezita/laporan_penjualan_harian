# Donat NS — Sistem Informasi Laporan Penjualan dan Keuangan

Proyek ini dibuat untuk kebutuhan pencatatan harian usaha Donat NS dengan PHP Native, MySQL, Bootstrap 5, dan Chart.js.

Cara pakai:
1. Jalankan XAMPP Apache + MySQL
2. Buka phpMyAdmin lalu impor file `database/donat_ns.sql`
3. Pastikan file `config/database.php` sudah dieksekusi sesuai koneksi Anda
4. Buka `http://localhost/laporan_penjualan_harian/setup.php` untuk setup akun pemilik pertama
5. Login dan mulai mengelola data produksi, distribusi, laporan penjualan, setoran, pengeluaran, dan keuangan

Catatan:
- Tidak ada data dummy yang otomatis dibuat.
- Semua jumlah dan nominal diinput secara manual oleh pemilik.
- Database sudah menyiapkan tabel utama untuk produksi, distribusi, penjualan, setoran, pengeluaran, dan saldo awal.
