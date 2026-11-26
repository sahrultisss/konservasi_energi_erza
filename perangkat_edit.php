<?php
require 'auth.php';
require_can('perangkat.edit');
$id = (int)($_GET['id'] ?? 0);
$q = mysqli_prepare($conn, "SELECT * FROM perangkat WHERE id_perangkat=?");
mysqli_stmt_bind_param($q, "i", $id); mysqli_stmt_execute($q);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
if (!$r) die("Perangkat tidak ditemukan.");

$error = '';
if (isset($_POST['simpan'])) {
    csrf_check();
    $n = trim($_POST['nama_perangkat']); $j = trim($_POST['jenis']); $k = trim($_POST['keterangan']);
    if ($n === '' || $j === '' || $k === '') $error = 'Semua kolom wajib diisi.';
    else {
        $s = mysqli_prepare($conn, "UPDATE perangkat SET nama_perangkat=?, jenis=?, keterangan=? WHERE id_perangkat=?");
        mysqli_stmt_bind_param($s, "sssi", $n, $j, $k, $id);
        if (mysqli_stmt_execute($s)) { flash('Perangkat berhasil diubah.'); header('Location: perangkat.php'); exit; }
        $error = 'Gagal mengubah data.';
    }
    $r['nama_perangkat'] = $n; $r['jenis'] = $j; $r['keterangan'] = $k;
}
$pageTitle = 'Edit Perangkat';
include 'includes/header.php';
?>
<div class="form-card"><h2>Edit Perangkat</h2>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
    <label>Nama Perangkat</label><input name="nama_perangkat" value="<?= e($r['nama_perangkat']) ?>" required>
    <label>Jenis</label><input name="jenis" value="<?= e($r['jenis']) ?>" required>
    <label>Keterangan</label><textarea name="keterangan" required><?= e($r['keterangan']) ?></textarea>
    <div class="actions"><button class="btn primary" name="simpan">Simpan</button><a class="btn secondary" href="perangkat.php">Batal</a></div>
</form></div>
<?php include 'includes/footer.php'; ?>
