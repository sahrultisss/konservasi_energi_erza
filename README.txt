KONSERVASI ENERGI - PANDUAN INSTALASI

1. Ekstrak folder konservasi_energi_rifky ke folder htdocs XAMPP.
   Contoh: C:\\xampp\\htdocs\\konservasi_energi_rifky
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka phpMyAdmin (http://localhost/phpmyadmin).
4. Import file database/konservasi_energi.sql.
   Catatan: file SQL ini membuat struktur tabel dan memasukkan data contoh.
   Jika database lama berisi data penting, lakukan backup terlebih dahulu.
5. Buka http://localhost/konservasi_energi_rifky/

AKUN DEMO
- Admin: admin / admin123
- Guru: guru / guru123
- Siswa: siswa / siswa123

HAK AKSES
- Admin: mengelola data siswa, guru, perangkat, dan checklist.
- Guru: dashboard dan checklist (isi, lihat, detail, ekspor).
- Siswa: dashboard dan checklist (isi dan lihat), tanpa fitur ekspor.

CATATAN
- Database harus bernama konservasi_energi sesuai pengaturan di koneksi.php.
- Jika username/password MySQL XAMPP berbeda, sesuaikan koneksi.php.
- Pastikan ekstensi mysqli aktif.
- Fitur ekspor Excel membutuhkan ekstensi PHP ZipArchive.
- Fitur PDF menggunakan fungsi cetak browser (pilih Save as PDF).
