<?php
require 'auth.php';
require_can('guru.add');
$error = '';
$nip = $nama = $mapel = '';
if (isset($_POST['simpan'])) {
    csrf_check();
    $nip = trim($_POST['nip']); $nama = trim($_POST['nama']); $mapel = trim($_POST['mapel']);
    if (!preg_match('/^\d{1,20}$/', $nip)) $error = 'NIP harus berupa angka (maksimal 20 digit).';
    elseif ($nama === '' || $mapel === '') $error = 'Nama dan mapel wajib diisi.';
    else {
        $stmt = mysqli_prepare($conn, "INSERT INTO guru(nip,nama,mapel) VALUES(?,?,?)");
        mysqli_stmt_bind_param($stmt, "sss", $nip, $nama, $mapel);
        if (mysqli_stmt_execute($stmt)) { flash('Guru berhasil ditambahkan.'); header('Location: guru.php'); exit; }
        $error = 'Gagal menyimpan. Pastikan NIP belum digunakan.';
    }
}
$pageTitle = 'Tambah Guru';
include 'includes/header.php';
?>
<div class="form-card"><h2>Tambah Guru</h2>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
    <label>NIP</label><input type="text" name="nip" inputmode="numeric" value="<?= e($nip) ?>" required>
    <label>Nama</label><input type="text" name="nama" value="<?= e($nama) ?>" required>
    <label>Mata Pelajaran</label><input type="text" name="mapel" value="<?= e($mapel) ?>" required>
    <div class="actions"><button class="btn primary" name="simpan">Simpan</button><a class="btn secondary" href="guru.php">Batal</a></div>
</form></div>
<?php include 'includes/footer.php'; ?>
