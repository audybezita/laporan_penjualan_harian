# Donat NS — Sistem Informasi Laporan Penjualan dan Keuangan

Repository ini berisi scaffold aplikasi web "Donat NS" berbasis PHP Native, MySQL, Bootstrap 5, dan Chart.js. Aplikasi dibuat untuk dijalankan di lingkungan lokal (XAMPP) dan belum berisi data sample.

Panduan singkat instalasi:

1. Pastikan Anda menggunakan XAMPP (Apache + MySQL) dan Visual Studio Code.
2. Letakkan folder proyek di C:\\xampp\\htdocs\\laporan_penjualan_harian (atau sesuai nama folder repo).
3. Jalankan Apache dan MySQL di XAMPP Control Panel.
4. Buka phpMyAdmin di http://localhost/phpmyadmin dan impor file `database/donat_ns.sql` untuk membuat database dan tabel.
5. Salin `config/database.php.example` menjadi `config/database.php` lalu sesuaikan DB_HOST, DB_NAME, DB_USER, DB_PASS.
6. Akses http://localhost/laporan_penjualan_harian/ — Anda akan diarahkan ke halaman login.
7. Jika belum ada akun pemilik, buka `setup.php` untuk membuat akun pemilik pertama (aman).

Catatan keamanan dan penggunaan:
- Password pengguna di-hash menggunakan password_hash().
- Semua query menggunakan PDO + prepared statements.
- Jangan menaruh file `config/database.php` di tempat publik dengan kredensial.

Dokumentasi lebih lengkap ada di file README ini.
