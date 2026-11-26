<?php
require 'auth.php';
require_can('guru.view');
$data = mysqli_query($conn, "SELECT * FROM guru ORDER BY nip");
$pageTitle = 'Data Guru';
include 'includes/header.php';
?>
<div class="header-row"><h2>Data Guru</h2><?php if (can('guru.add')): ?><a class="btn primary" href="guru_tambah.php">+ Tambah Guru</a><?php endif; ?></div>
<table>
<tr><th>No</th><th>NIP</th><th>Nama</th><th>Mata Pelajaran</th><th>Aksi</th></tr>
<?php $no = 1; while ($r = mysqli_fetch_assoc($data)): ?>
<tr>
    <td><?= $no++ ?></td><td><?= e($r['nip']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['mapel']) ?></td>
    <td class="aksi">
        <?php if (can('guru.edit')): ?><a class="btn warning" href="guru_edit.php?nip=<?= urlencode($r['nip']) ?>">Edit</a><?php endif; ?>
        <?php if (can('guru.delete')): ?><form method="post" action="guru_hapus.php" onsubmit="return confirm('Hapus data guru ini?')">
            <?= csrf_field() ?><input type="hidden" name="nip" value="<?= e($r['nip']) ?>"><button class="btn danger">Hapus</button>
        </form><?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
<?php if ($no === 1): ?><tr><td colspan="5" class="muted">Belum ada data guru.</td></tr><?php endif; ?>
</table>
<?php include 'includes/footer.php'; ?>
