<?php
require 'auth.php';
require_can('checklist.view');
$data = mysqli_query($conn, "SELECT c.id_checklist, c.tanggal, c.jam,
    COALESCE(u.nama, s.nama) AS nama, COALESCE(u.level, 'Siswa') AS peran,
    (SELECT COUNT(*) FROM detail_checklist d WHERE d.id_checklist=c.id_checklist) AS jml_detail
    FROM checklist c
    LEFT JOIN users u ON u.id_user = c.id_user
    LEFT JOIN siswa s ON s.nis = c.nis
    ORDER BY c.tanggal DESC, c.jam DESC, c.id_checklist DESC");
$pageTitle = 'Data Checklist';
include 'includes/header.php';
?>
<div class="header-row"><h2>Data Checklist</h2>
    <div class="toolbar-btn">
        <?php if (can('checklist.export')): ?><a class="btn outline" href="checklist_export.php">📊 Export Excel</a>
        <a class="btn outline" href="checklist_cetak.php?auto=1" target="_blank">📄 Export PDF</a><?php endif; ?>
        <?php if (can('checklist.add')): ?><a class="btn primary" href="checklist_tambah.php">+ Tambah Checklist</a><?php endif; ?>
    </div></div>
<table>
<tr><th>No</th><th>Nama</th><th>Role</th><th>Tanggal</th><th>Jam</th><th>Perangkat</th><th>Aksi</th></tr>
<?php $no = 1; while ($r = mysqli_fetch_assoc($data)): ?>
<tr>
    <td><?= $no++ ?></td><td><?= e($r['nama'] ?? '-') ?></td><td><?= e($r['peran']) ?></td>
    <td><?= e($r['tanggal']) ?></td><td><?= e(substr($r['jam'], 0, 5)) ?></td><td><?= (int)$r['jml_detail'] ?></td>
    <td class="aksi">
        <a class="btn primary" href="checklist_detail.php?id=<?= (int)$r['id_checklist'] ?>">Detail</a>
        <?php if (can('checklist.delete')): ?><form method="post" action="checklist_hapus.php" onsubmit="return confirm('Hapus checklist ini?')">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id_checklist'] ?>"><button class="btn danger">Hapus</button>
        </form><?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
<?php if ($no === 1): ?><tr><td colspan="7" class="muted">Belum ada checklist.</td></tr><?php endif; ?>
</table>
<?php include 'includes/footer.php'; ?>
