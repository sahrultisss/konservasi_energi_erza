<?php
require 'auth.php';
require_can('checklist.view');
$id = (int)($_GET['id'] ?? 0);
$q = mysqli_prepare($conn, "SELECT c.*, COALESCE(u.nama, s.nama) AS nama, COALESCE(u.level, 'Siswa') AS peran FROM checklist c LEFT JOIN users u ON u.id_user=c.id_user LEFT JOIN siswa s ON s.nis=c.nis WHERE c.id_checklist=?");
mysqli_stmt_bind_param($q, "i", $id); mysqli_stmt_execute($q);
$head = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
if (!$head) die("Checklist tidak ditemukan.");

$q2 = mysqli_prepare($conn, "SELECT d.*, p.nama_perangkat, p.jenis FROM detail_checklist d LEFT JOIN perangkat p ON p.id_perangkat=d.id_perangkat WHERE d.id_checklist=?");
mysqli_stmt_bind_param($q2, "i", $id); mysqli_stmt_execute($q2);
$d = mysqli_stmt_get_result($q2);

$pageTitle = 'Detail Checklist';
include 'includes/header.php';
?>
<div class="header-row"><h2>Detail Checklist</h2>
    <div class="toolbar-btn">
        <?php if (can('checklist.export')): ?><a class="btn outline" href="checklist_export.php?id=<?= (int)$id ?>">📊 Export Excel</a>
        <a class="btn outline" href="checklist_cetak.php?id=<?= (int)$id ?>&auto=1" target="_blank">📄 Export PDF</a><?php endif; ?>
        <a class="btn secondary" href="checklist.php">← Kembali</a>
    </div></div>
<div class="card">
    <p><b>Pengisi:</b> <?= e($head['nama'] ?? '-') ?> (<?= e($head['peran']) ?>)</p>
    <p><b>Tanggal:</b> <?= e($head['tanggal']) ?> | <b>Jam:</b> <?= e(substr($head['jam'], 0, 5)) ?></p>
</div>
<table>
<tr><th>No</th><th>Perangkat</th><th>Jenis</th><th>Tahapan</th><th>Kondisi</th><th>Status</th><th>Keterangan</th></tr>
<?php $no = 1; while ($r = mysqli_fetch_assoc($d)): ?>
<tr>
    <td><?= $no++ ?></td><td><?= e($r['nama_perangkat'] ?? '-') ?></td><td><?= e($r['jenis'] ?? '-') ?></td>
    <td><?= e($r['tahapan']) ?></td><td><?= e($r['kondisi']) ?></td>
    <td><span class="badge <?= $r['status'] === 'Ya' ? 'yes' : 'no' ?>"><?= e($r['status']) ?></span></td>
    <td><?= e($r['keterangan']) ?></td>
</tr>
<?php endwhile; ?>
<?php if ($no === 1): ?><tr><td colspan="7" class="muted">Checklist ini tidak memiliki detail perangkat.</td></tr><?php endif; ?>
</table>
<?php include 'includes/footer.php'; ?>
