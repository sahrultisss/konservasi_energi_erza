<?php
require 'auth.php';
require_can('siswa.add');
$error = '';
$nis = $nama = $kelas = '';
if (isset($_POST['simpan'])) {
    csrf_check();
    $nis = trim($_POST['nis']); $nama = trim($_POST['nama']); $kelas = trim($_POST['kelas']);
    if (!preg_match('/^\d{1,20}$/', $nis)) $error = 'NIS harus berupa angka (maksimal 20 digit).';
    elseif ($nama === '' || $kelas === '') $error = 'Nama dan kelas wajib diisi.';
    else {
        $stmt = mysqli_prepare($conn, "INSERT INTO siswa(nis,nama,kelas) VALUES(?,?,?)");
        mysqli_stmt_bind_param($stmt, "sss", $nis, $nama, $kelas);
        if (mysqli_stmt_execute($stmt)) { flash('Siswa berhasil ditambahkan.'); header('Location: siswa.php'); exit; }
        $error = 'Gagal menyimpan. Pastikan NIS belum digunakan.';
    }
}
$pageTitle = 'Tambah Siswa';
include 'includes/header.php';
?>
<div class="form-card"><h2>Tambah Siswa</h2>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
    <label>NIS</label><input type="text" name="nis" inputmode="numeric" value="<?= e($nis) ?>" required>
    <label>Nama</label><input type="text" name="nama" value="<?= e($nama) ?>" required>
    <label>Kelas</label><input type="text" name="kelas" value="<?= e($kelas) ?>" required>
    <div class="actions"><button class="btn primary" name="simpan">Simpan</button><a class="btn secondary" href="siswa.php">Batal</a></div>
</form></div>
<?php include 'includes/footer.php'; ?>
