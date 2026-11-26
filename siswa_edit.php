<?php
require 'auth.php';
require_can('siswa.edit');
$nis = trim($_GET['nis'] ?? '');
$q = mysqli_prepare($conn, "SELECT * FROM siswa WHERE nis=?");
mysqli_stmt_bind_param($q, "s", $nis); mysqli_stmt_execute($q);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
if (!$r) die("Data siswa tidak ditemukan.");

$error = '';
if (isset($_POST['simpan'])) {
    csrf_check();
    $nama = trim($_POST['nama']); $kelas = trim($_POST['kelas']);
    if ($nama === '' || $kelas === '') $error = 'Nama dan kelas wajib diisi.';
    else {
        $s = mysqli_prepare($conn, "UPDATE siswa SET nama=?, kelas=? WHERE nis=?");
        mysqli_stmt_bind_param($s, "sss", $nama, $kelas, $nis);
        if (mysqli_stmt_execute($s)) { flash('Data siswa berhasil diubah.'); header('Location: siswa.php'); exit; }
        $error = 'Gagal mengubah data.';
    }
    $r['nama'] = $nama; $r['kelas'] = $kelas;
}
$pageTitle = 'Edit Siswa';
include 'includes/header.php';
?>
<div class="form-card"><h2>Edit Siswa</h2>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
    <label>NIS</label><input value="<?= e($r['nis']) ?>" disabled>
    <label>Nama</label><input name="nama" value="<?= e($r['nama']) ?>" required>
    <label>Kelas</label><input name="kelas" value="<?= e($r['kelas']) ?>" required>
    <div class="actions"><button class="btn primary" name="simpan">Simpan</button><a class="btn secondary" href="siswa.php">Batal</a></div>
</form></div>
<?php include 'includes/footer.php'; ?>
