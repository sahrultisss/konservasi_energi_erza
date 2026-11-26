<?php
require 'auth.php';
require_can('guru.edit');
$nip = trim($_GET['nip'] ?? '');
$q = mysqli_prepare($conn, "SELECT * FROM guru WHERE nip=?");
mysqli_stmt_bind_param($q, "s", $nip); mysqli_stmt_execute($q);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
if (!$r) die("Data guru tidak ditemukan.");

$error = '';
if (isset($_POST['simpan'])) {
    csrf_check();
    $nama = trim($_POST['nama']); $mapel = trim($_POST['mapel']);
    if ($nama === '' || $mapel === '') $error = 'Nama dan mapel wajib diisi.';
    else {
        $s = mysqli_prepare($conn, "UPDATE guru SET nama=?, mapel=? WHERE nip=?");
        mysqli_stmt_bind_param($s, "sss", $nama, $mapel, $nip);
        if (mysqli_stmt_execute($s)) { flash('Data guru berhasil diubah.'); header('Location: guru.php'); exit; }
        $error = 'Gagal mengubah data.';
    }
    $r['nama'] = $nama; $r['mapel'] = $mapel;
}
$pageTitle = 'Edit Guru';
include 'includes/header.php';
?>
<div class="form-card"><h2>Edit Guru</h2>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
    <label>NIP</label><input value="<?= e($r['nip']) ?>" disabled>
    <label>Nama</label><input name="nama" value="<?= e($r['nama']) ?>" required>
    <label>Mata Pelajaran</label><input name="mapel" value="<?= e($r['mapel']) ?>" required>
    <div class="actions"><button class="btn primary" name="simpan">Simpan</button><a class="btn secondary" href="guru.php">Batal</a></div>
</form></div>
<?php include 'includes/footer.php'; ?>
