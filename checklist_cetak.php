<?php
// Halaman cetak -> Simpan sebagai PDF lewat browser.
// Pemakaian: checklist_cetak.php (semua) | checklist_cetak.php?id=5 (satu checklist) | tambahkan &auto=1 agar dialog cetak langsung muncul
require 'auth.php';
require_can('checklist.export');
$id = (int)($_GET['id'] ?? 0);
$sql = "SELECT c.id_checklist, COALESCE(u.nama, s.nama) AS nama, COALESCE(u.level, 'Siswa') AS peran, c.tanggal, c.jam,
               p.nama_perangkat, p.jenis, d.tahapan, d.kondisi, d.status, d.keterangan
        FROM checklist c
        LEFT JOIN users u ON u.id_user = c.id_user
        LEFT JOIN siswa s ON s.nis = c.nis
        LEFT JOIN detail_checklist d ON d.id_checklist = c.id_checklist
        LEFT JOIN perangkat p ON p.id_perangkat = d.id_perangkat";
if ($id) $sql .= " WHERE c.id_checklist = ?";
$sql .= " ORDER BY c.tanggal DESC, c.jam DESC, c.id_checklist DESC, d.id_detail";
if ($id) {
    $st = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
} else {
    $res = mysqli_query($conn, $sql);
}
$groups = [];
while ($d = mysqli_fetch_assoc($res)) {
    $k = $d['id_checklist'];
    if (!isset($groups[$k])) $groups[$k] = ['head' => $d, 'items' => []];
    if ($d['nama_perangkat'] !== null || $d['tahapan'] !== null) $groups[$k]['items'][] = $d;
}
$auto = isset($_GET['auto']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Checklist Konservasi Energi</title>
<style>
*{box-sizing:border-box}
:root{--ink:#17191c;--muted:#747982;--line:#e2e5e8;--accent:#b7e34a}
body{margin:0;font-family:Inter,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;font-size:12px;color:var(--ink);background:#f5f6f7;line-height:1.55}
.toolbar{position:sticky;top:0;background:#151719;color:#fff;padding:12px 24px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.toolbar button,.toolbar a{border:1px solid rgba(255,255,255,.16);border-radius:9px;padding:9px 14px;font:650 12px system-ui,sans-serif;cursor:pointer;text-decoration:none}
.toolbar button{background:var(--accent);color:#26320b}.toolbar a{background:rgba(255,255,255,.08);color:#fff}.toolbar span{font-size:12px;color:#c2c7ca}
.page{max-width:210mm;margin:22px auto;background:#fff;padding:16mm 14mm;box-shadow:0 2px 8px rgba(17,19,21,.04),0 12px 30px rgba(17,19,21,.05)}
h1{margin:0;font-size:21px;color:var(--ink);letter-spacing:-.03em}.meta{color:var(--muted);margin:5px 0 17px;padding-bottom:11px;border-bottom:2px solid var(--ink)}
.blok{margin:0 0 17px;break-inside:avoid;page-break-inside:avoid}.blok h2{margin:0 0 6px;font-size:14px;letter-spacing:-.02em}.blok h2 small{font-weight:400;color:var(--muted);font-size:11px}
table{width:100%;border-collapse:collapse}th,td{border:1px solid var(--line);padding:6px 8px;text-align:left;vertical-align:top}th{background:#f1f2f3;color:#34383d;font-size:10px;letter-spacing:.04em;text-transform:uppercase}
.ya{color:#23734f;font-weight:700}.tidak{color:#b23b46;font-weight:700}.kosong{color:var(--muted);font-style:italic}
@page{size:A4;margin:10mm}
@media print{body{background:#fff}.toolbar{display:none}.page{margin:0;max-width:none;padding:0;box-shadow:none}th{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head>
<body>
<div class="toolbar">
    <button onclick="window.print()">🖨 Cetak / Simpan sebagai PDF</button>
    <a href="checklist.php">← Kembali</a>
    <span>Di jendela cetak, pilih tujuan (Destination) <b>Save as PDF</b>.</span>
</div>
<div class="page">
    <h1>⚡ Laporan Checklist Konservasi Energi</h1>
    <div class="meta">Dicetak oleh <?= e($_SESSION['nama']) ?> (<?= e($_SESSION['level']) ?>) pada <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; Jumlah checklist: <?= count($groups) ?></div>

    <?php if (!$groups): ?><p class="kosong">Belum ada data checklist.</p><?php endif; ?>
    <?php foreach ($groups as $g): $h = $g['head']; ?>
    <div class="blok">
        <h2><?= e($h['nama'] ?? '-') ?> <small>— <?= e($h['peran']) ?> · <?= e($h['tanggal']) ?> <?= e(substr($h['jam'], 0, 5)) ?></small></h2>
        <table>
            <tr><th style="width:28px">No</th><th>Perangkat</th><th>Jenis</th><th>Tahapan</th><th>Kondisi</th><th>Dilakukan?</th><th>Keterangan</th></tr>
            <?php $n = 1; foreach ($g['items'] as $it): ?>
            <tr>
                <td><?= $n++ ?></td><td><?= e($it['nama_perangkat'] ?? '-') ?></td><td><?= e($it['jenis'] ?? '-') ?></td>
                <td><?= e($it['tahapan']) ?></td><td><?= e($it['kondisi']) ?></td>
                <td class="<?= $it['status'] === 'Ya' ? 'ya' : 'tidak' ?>"><?= e($it['status']) ?></td><td><?= e($it['keterangan']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$g['items']): ?><tr><td colspan="7" class="kosong">Tidak ada detail perangkat.</td></tr><?php endif; ?>
        </table>
    </div>
    <?php endforeach; ?>
</div>
<?php if ($auto): ?><script>window.addEventListener('load', function(){ window.print(); });</script><?php endif; ?>
</body>
</html>
