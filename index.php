<?php
require 'auth.php';
$pageTitle = 'Dashboard';
include 'includes/header.php';

function hitung($conn, $tabel) { return mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM $tabel"))['total']; }

$hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggal = $hari[date('w')] . ', ' . date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y');

$terbaru = mysqli_query($conn, "SELECT c.id_checklist, c.tanggal, c.jam, COALESCE(u.nama, s.nama) AS nama, COALESCE(u.level, 'Siswa') AS peran,
    (SELECT COUNT(*) FROM detail_checklist d WHERE d.id_checklist=c.id_checklist) AS jml
    FROM checklist c LEFT JOIN users u ON u.id_user=c.id_user LEFT JOIN siswa s ON s.nis=c.nis ORDER BY c.tanggal DESC, c.jam DESC LIMIT 5");
?>
<section class="hero">
    <div>
        <div class="hero-date"><?= e($tanggal) ?></div>
        <h2>Selamat datang, <?= e($_SESSION['nama']) ?> 👋 <span class="chip"><?= e($_SESSION['level']) ?></span></h2>
        <p>Catat dan pantau penggunaan perangkat listrik di sekolah, dari sebelum dipakai hingga sesudah dimatikan.</p>
        <div class="hero-actions">
            <?php if (can('checklist.add')): ?><a class="btn warning" href="checklist_tambah.php">+ Isi Checklist</a><?php endif; ?>
            <a class="btn ghost" href="checklist.php">Lihat Checklist</a>
        </div>
    </div>
    <div class="hero-badge">⚡</div>
</section>

<div class="cards">
    <?php if (can('siswa.view')): ?>
    <div class="card stat s-green">
        <div class="stat-top"><div class="icon">🎓</div><div><h3>Data Siswa</h3><div class="number"><?= hitung($conn, 'siswa') ?></div></div></div>
        <div class="stat-actions"><a class="btn primary" href="siswa.php"><?= can('siswa.add') ? 'Kelola' : 'Lihat / Edit' ?></a></div>
    </div>
    <?php endif; ?>
    <?php if (can('guru.view')): ?>
    <div class="card stat s-violet">
        <div class="stat-top"><div class="icon">👩‍🏫</div><div><h3>Data Guru</h3><div class="number"><?= hitung($conn, 'guru') ?></div></div></div>
        <div class="stat-actions"><a class="btn primary" href="guru.php"><?= can('guru.add') ? 'Kelola' : 'Lihat / Edit' ?></a></div>
    </div>
    <?php endif; ?>
    <?php if (can('perangkat.view')): ?>
    <div class="card stat s-amber">
        <div class="stat-top"><div class="icon">💡</div><div><h3>Perangkat</h3><div class="number"><?= hitung($conn, 'perangkat') ?></div></div></div>
        <div class="stat-actions"><a class="btn primary" href="perangkat.php"><?= can('perangkat.add') ? 'Kelola' : 'Lihat / Edit' ?></a></div>
    </div>
    <?php endif; ?>
    <div class="card stat s-sky">
        <div class="stat-top"><div class="icon">✅</div><div><h3>Checklist</h3><div class="number"><?= hitung($conn, 'checklist') ?></div></div></div>
        <div class="stat-actions">
            <a class="btn primary" href="checklist.php">Lihat</a>
            <?php if (can('checklist.add')): ?><a class="btn outline" href="checklist_tambah.php">+ Isi Checklist</a><?php endif; ?>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card panel">
        <div class="panel-head"><h3>Checklist terbaru</h3><a href="checklist.php">Lihat semua →</a></div>
        <table>
            <tr><th>Nama</th><th>Role</th><th>Tanggal</th><th>Jam</th><th>Perangkat</th><th></th></tr>
            <?php $ada = false; while ($r = mysqli_fetch_assoc($terbaru)): $ada = true; ?>
            <tr>
                <td><b><?= e($r['nama'] ?? '-') ?></b></td><td><?= e($r['peran']) ?></td>
                <td><?= e($r['tanggal']) ?></td><td><?= e(substr($r['jam'], 0, 5)) ?></td>
                <td><span class="badge yes"><?= (int)$r['jml'] ?> item</span></td>
                <td class="aksi"><a class="btn outline" href="checklist_detail.php?id=<?= (int)$r['id_checklist'] ?>">Detail</a></td>
            </tr>
            <?php endwhile; ?>
            <?php if (!$ada): ?><tr><td colspan="6" class="muted">Belum ada checklist.</td></tr><?php endif; ?>
        </table>
    </div>
    <div class="card panel">
        <div class="panel-head"><h3>Tips hemat energi</h3></div>
        <ul class="tips">
            <li><span>💡</span>Matikan lampu dan AC saat ruangan kosong.</li>
            <li><span>🔌</span>Cabut charger dan kabel yang tidak terpakai.</li>
            <li><span>🖥️</span>Aktifkan mode hemat daya pada komputer dan proyektor.</li>
            <li><span>☀️</span>Manfaatkan cahaya alami di siang hari.</li>
        </ul>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
