<?php
require 'auth.php';
require_can('perangkat.add');
$error = '';
$n = $j = $k = '';
if (isset($_POST['simpan'])) {
    csrf_check();
    $n = trim($_POST['nama_perangkat']); $j = trim($_POST['jenis']); $k = trim($_POST['keterangan']);
    if ($n === '' || $j === '' || $k === '') $error = 'Semua kolom wajib diisi.';
    else {
        $s = mysqli_prepare($conn, "INSERT INTO perangkat(nama_perangkat,jenis,keterangan) VALUES(?,?,?)");
        mysqli_stmt_bind_param($s, "sss", $n, $j, $k);
        if (mysqli_stmt_execute($s)) { flash('Perangkat berhasil ditambahkan.'); header('Location: perangkat.php'); exit; }
        $error = 'Gagal menyimpan.';
    }
}
$pageTitle = 'Tambah Perangkat';
include 'includes/header.php';
?>
<div class="form-card"><h2>Tambah Perangkat</h2>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
    <label>Nama Perangkat</label><input name="nama_perangkat" value="<?= e($n) ?>" required>
    <label>Jenis</label><input name="jenis" value="<?= e($j) ?>" required>
    <label>Keterangan (kondisi standar, mis. "Mati")</label><textarea name="keterangan" required><?= e($k) ?></textarea>
    <div class="actions"><button class="btn primary" name="simpan">Simpan</button><a class="btn secondary" href="perangkat.php">Batal</a></div>
</form></div>
<?php include 'includes/footer.php'; ?>
