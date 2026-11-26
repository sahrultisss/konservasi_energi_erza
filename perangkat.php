<?php
require 'auth.php';
require_can('perangkat.view');
$data = mysqli_query($conn, "SELECT * FROM perangkat ORDER BY id_perangkat");
$pageTitle = 'Data Perangkat';
include 'includes/header.php';
?>
<div class="header-row"><h2>Data Perangkat</h2><?php if (can('perangkat.add')): ?><a class="btn primary" href="perangkat_tambah.php">+ Tambah Perangkat</a><?php endif; ?></div>
<table>
<tr><th>No</th><th>Nama Perangkat</th><th>Jenis</th><th>Keterangan</th><th>Aksi</th></tr>
<?php $no = 1; while ($r = mysqli_fetch_assoc($data)): ?>
<tr>
    <td><?= $no++ ?></td><td><?= e($r['nama_perangkat']) ?></td><td><?= e($r['jenis']) ?></td><td><?= e($r['keterangan']) ?></td>
    <td class="aksi">
        <?php if (can('perangkat.edit')): ?><a class="btn warning" href="perangkat_edit.php?id=<?= (int)$r['id_perangkat'] ?>">Edit</a><?php endif; ?>
        <?php if (can('perangkat.delete')): ?><form method="post" action="perangkat_hapus.php" onsubmit="return confirm('Hapus perangkat ini? Detail checklist terkait ikut terhapus.')">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id_perangkat'] ?>"><button class="btn danger">Hapus</button>
        </form><?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
<?php if ($no === 1): ?><tr><td colspan="5" class="muted">Belum ada data perangkat.</td></tr><?php endif; ?>
</table>
<?php include 'includes/footer.php'; ?>
