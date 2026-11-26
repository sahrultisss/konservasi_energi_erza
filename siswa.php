<?php
require 'auth.php';
require_can('siswa.view');
$data = mysqli_query($conn, "SELECT * FROM siswa ORDER BY nis");
$pageTitle = 'Data Siswa';
include 'includes/header.php';
?>
<div class="header-row"><h2>Data Siswa</h2><?php if (can('siswa.add')): ?><a class="btn primary" href="siswa_tambah.php">+ Tambah Siswa</a><?php endif; ?></div>
<table>
<tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Aksi</th></tr>
<?php $no = 1; while ($r = mysqli_fetch_assoc($data)): ?>
<tr>
    <td><?= $no++ ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td>
    <td class="aksi">
        <?php if (can('siswa.edit')): ?><a class="btn warning" href="siswa_edit.php?nis=<?= urlencode($r['nis']) ?>">Edit</a><?php endif; ?>
        <?php if (can('siswa.delete')): ?><form method="post" action="siswa_hapus.php" onsubmit="return confirm('Hapus siswa ini? Checklist miliknya ikut terhapus.')">
            <?= csrf_field() ?><input type="hidden" name="nis" value="<?= e($r['nis']) ?>"><button class="btn danger">Hapus</button>
        </form><?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
<?php if ($no === 1): ?><tr><td colspan="5" class="muted">Belum ada data siswa.</td></tr><?php endif; ?>
</table>
<?php include 'includes/footer.php'; ?>
