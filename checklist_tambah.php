<?php
require 'auth.php';
require_can('checklist.add');
$error = '';
$tahapan_ok = ['SEBELUM', 'SELAMA', 'SESUDAH', '5R'];
$status_ok  = ['Ya', 'Tidak'];

$perangkat = [];
$res = mysqli_query($conn, "SELECT id_perangkat,nama_perangkat,jenis FROM perangkat ORDER BY nama_perangkat");
while ($p = mysqli_fetch_assoc($res)) $perangkat[] = $p;

// Ambil baris dari POST (untuk isi ulang saat error)
$rows = [];
foreach (($_POST['id_perangkat'] ?? []) as $i => $pid) {
    $rows[] = [
        'id_perangkat' => $pid,
        'tahapan'      => $_POST['tahapan'][$i] ?? 'SEBELUM',
        'kondisi'      => $_POST['kondisi'][$i] ?? '',
        'status'       => $_POST['status'][$i] ?? 'Ya',
        'keterangan'   => $_POST['keterangan'][$i] ?? '',
    ];
}

if (isset($_POST['simpan'])) {
    csrf_check();
    $tanggal = $_POST['tanggal'] ?? ''; $jam = $_POST['jam'] ?? '';
    $valid = array_filter($rows, fn($r) => (int)$r['id_perangkat'] > 0);
    $id_nol = array_filter($rows, fn($r) => $r['id_perangkat'] !== '' && (int)$r['id_perangkat'] === 0);

    if ($tanggal === '' || $jam === '') {
        $error = 'Tanggal dan jam wajib diisi.';
    } elseif ($id_nol) {
        $error = 'ID perangkat tidak valid. Periksa kembali data perangkat di database, lalu muat ulang halaman.';
    } elseif (!$valid) {
        $error = 'Tambahkan minimal satu perangkat.';
    } else {
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO checklist(id_user,tanggal,jam) VALUES(?,?,?)");
            $id_user = (int)$_SESSION['id_user'];
            mysqli_stmt_bind_param($stmt, "iss", $id_user, $tanggal, $jam);
            if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_error($conn));
            $id = mysqli_insert_id($conn);

            $d = mysqli_prepare($conn, "INSERT INTO detail_checklist(id_checklist,id_perangkat,tahapan,kondisi,status,keterangan) VALUES(?,?,?,?,?,?)");
            foreach ($valid as $r) {
                $pid = (int)$r['id_perangkat'];
                $t   = in_array($r['tahapan'], $tahapan_ok, true) ? $r['tahapan'] : 'SEBELUM';
                $k   = trim($r['kondisi']);
                $st  = in_array($r['status'], $status_ok, true) ? $r['status'] : 'Tidak';
                $ket = trim($r['keterangan']);
                mysqli_stmt_bind_param($d, "iissss", $id, $pid, $t, $k, $st, $ket);
                if (!mysqli_stmt_execute($d)) throw new Exception(mysqli_error($conn));
            }
            mysqli_commit($conn);
            flash('Checklist berhasil ditambahkan.');
            header('Location: checklist.php'); exit;
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            $error = 'Gagal menyimpan checklist: ' . $ex->getMessage();
        }
    }
}
if (!$rows) $rows[] = ['id_perangkat' => '', 'tahapan' => 'SEBELUM', 'kondisi' => '', 'status' => 'Ya', 'keterangan' => ''];

function row_html($r, $perangkat, $tahapan_ok) { ob_start(); ?>
<div class="detail-row">
    <div><span class="lbl">Perangkat</span>
        <select name="id_perangkat[]" required>
            <option value="">-- Pilih perangkat --</option>
            <?php foreach ($perangkat as $p): ?>
            <option value="<?= (int)$p['id_perangkat'] ?>" <?= (string)$r['id_perangkat'] === (string)$p['id_perangkat'] ? 'selected' : '' ?>><?= e($p['nama_perangkat'] . ' (' . $p['jenis'] . ')') ?></option>
            <?php endforeach; ?>
        </select></div>
    <div><span class="lbl">Tahapan</span>
        <select name="tahapan[]"><?php foreach ($tahapan_ok as $t): ?><option <?= $r['tahapan'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
    <div><span class="lbl">Kondisi</span><input name="kondisi[]" value="<?= e($r['kondisi']) ?>" placeholder="Contoh: menyala, mati" required></div>
    <div><span class="lbl">Dilakukan?</span>
        <select name="status[]"><option value="Ya" <?= $r['status'] === 'Ya' ? 'selected' : '' ?>>Ya</option><option value="Tidak" <?= $r['status'] === 'Tidak' ? 'selected' : '' ?>>Tidak</option></select></div>
    <div><span class="lbl">Keterangan</span><input name="keterangan[]" value="<?= e($r['keterangan']) ?>" placeholder="Catatan (opsional)"></div>
    <div><button type="button" class="btn danger btn-hapus-baris">Hapus</button></div>
</div>
<?php return ob_get_clean(); }

$pageTitle = 'Isi Checklist';
include 'includes/header.php';
?>
<h2>Isi Checklist</h2>
<div class="form-card wide">
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
    <div class="grid-3">
        <div><label>Pengisi</label><input type="text" value="<?= e($_SESSION['nama'] . ' (' . $_SESSION['level'] . ')') ?>" disabled></div>
        <div><label>Tanggal</label><input type="date" name="tanggal" value="<?= e($_POST['tanggal'] ?? date('Y-m-d')) ?>" required></div>
        <div><label>Jam</label><input type="time" name="jam" value="<?= e($_POST['jam'] ?? date('H:i')) ?>" required></div>
    </div>

    <h3>Detail perangkat</h3>
    <div class="detail-head"><span>Perangkat</span><span>Tahapan</span><span>Kondisi</span><span>Dilakukan?</span><span>Keterangan</span><span></span></div>
    <div id="baris">
        <?php foreach ($rows as $r) echo row_html($r, $perangkat, $tahapan_ok); ?>
    </div>
    <template id="tpl"><?= row_html(['id_perangkat' => '', 'tahapan' => 'SEBELUM', 'kondisi' => '', 'status' => 'Ya', 'keterangan' => ''], $perangkat, $tahapan_ok) ?></template>

    <?php if (!$perangkat): ?><p class="muted">Belum ada data perangkat. <a href="perangkat_tambah.php"><u>Tambah perangkat</u></a> terlebih dahulu.</p><?php endif; ?>

    <button type="button" id="tambah" class="btn outline">Tambah baris</button>
    <div class="actions"><button class="btn primary" name="simpan">Simpan checklist</button><a class="btn outline" href="checklist.php">Batal</a></div>
</form>
</div>
<script>
const baris = document.getElementById('baris');
document.getElementById('tambah').addEventListener('click', () => {
    baris.appendChild(document.getElementById('tpl').content.cloneNode(true));
});
baris.addEventListener('click', e => {
    if (!e.target.classList.contains('btn-hapus-baris')) return;
    if (baris.querySelectorAll('.detail-row').length > 1) e.target.closest('.detail-row').remove();
    else alert('Minimal satu baris perangkat.');
});
</script>
<?php include 'includes/footer.php'; ?>
