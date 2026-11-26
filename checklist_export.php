<?php
// Export checklist ke Excel (.xlsx). Tanpa library: dibuat langsung dengan ZipArchive.
// Pemakaian: checklist_export.php          -> semua checklist
//            checklist_export.php?id=5     -> satu checklist
require 'auth.php';
require_can('checklist.export');
if (!class_exists('ZipArchive')) {
    die('Ekstensi ZipArchive belum aktif. Buka XAMPP > Config > PHP (php.ini), hapus tanda ; di depan "extension=zip", lalu restart Apache.');
}

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

function xl($s) {
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$s);
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
function colname($i) { $n = ''; $i++; while ($i > 0) { $m = ($i - 1) % 26; $n = chr(65 + $m) . $n; $i = intdiv($i - 1, 26); } return $n; }
function cstr($c, $r, $v, $s) { return '<c r="' . colname($c) . $r . '" s="' . $s . '" t="inlineStr"><is><t xml:space="preserve">' . xl($v) . '</t></is></c>'; }
function cnum($c, $r, $v, $s) { return '<c r="' . colname($c) . $r . '" s="' . $s . '"><v>' . (int)$v . '</v></c>'; }

$headers = ['No', 'Nama', 'Role', 'Tanggal', 'Jam', 'Perangkat', 'Jenis', 'Tahapan', 'Kondisi', 'Dilakukan?', 'Keterangan'];
$widths  = [6, 26, 12, 13, 8, 22, 16, 12, 18, 13, 34];

$rows  = '<row r="1" ht="26" customHeight="1">' . cstr(0, 1, 'Laporan Checklist Konservasi Energi', 2) . '</row>';
$rows .= '<row r="2">' . cstr(0, 2, 'Dicetak oleh ' . $_SESSION['nama'] . ' pada ' . date('d/m/Y H:i'), 0) . '</row>';
$rows .= '<row r="4" ht="22" customHeight="1">';
foreach ($headers as $i => $h) $rows .= cstr($i, 4, $h, 1);
$rows .= '</row>';

$r = 4; $no = 0; $last = null;
while ($d = mysqli_fetch_assoc($res)) {
    $r++;
    if ($d['id_checklist'] !== $last) { $no++; $last = $d['id_checklist']; }
    $rows .= '<row r="' . $r . '">'
        . cnum(0, $r, $no, 3)
        . cstr(1, $r, $d['nama'] ?? '-', 3)
        . cstr(2, $r, $d['peran'], 3)
        . cstr(3, $r, $d['tanggal'], 3)
        . cstr(4, $r, substr($d['jam'], 0, 5), 3)
        . cstr(5, $r, $d['nama_perangkat'] ?? '-', 3)
        . cstr(6, $r, $d['jenis'] ?? '-', 3)
        . cstr(7, $r, $d['tahapan'] ?? '-', 3)
        . cstr(8, $r, $d['kondisi'] ?? '-', 3)
        . cstr(9, $r, $d['status'] ?? '-', 3)
        . cstr(10, $r, $d['keterangan'] ?? '', 3)
        . '</row>';
}

$cols = '';
foreach ($widths as $i => $w) $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';

$X = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$NS = 'xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"';

$sheet = $X . '<worksheet ' . $NS . '><sheetViews><sheetView workbookViewId="0"><pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft"/></sheetView></sheetViews>'
       . '<sheetFormatPr defaultRowHeight="15"/><cols>' . $cols . '</cols><sheetData>' . $rows . '</sheetData></worksheet>';

$styles = $X . '<styleSheet ' . $NS . '>'
  . '<fonts count="3">'
  .   '<font><sz val="11"/><name val="Calibri"/></font>'
  .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
  .   '<font><b/><sz val="15"/><color rgb="FF0F3B2A"/><name val="Calibri"/></font>'
  . '</fonts>'
  . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
  .   '<fill><patternFill patternType="solid"><fgColor rgb="FF166A47"/><bgColor indexed="64"/></patternFill></fill></fills>'
  . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
  .   '<border><left style="thin"><color rgb="FFBFCBC4"/></left><right style="thin"><color rgb="FFBFCBC4"/></right><top style="thin"><color rgb="FFBFCBC4"/></top><bottom style="thin"><color rgb="FFBFCBC4"/></bottom><diagonal/></border></borders>'
  . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
  . '<cellXfs count="4">'
  .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
  .   '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
  .   '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
  .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
  . '</cellXfs>'
  . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
  . '</styleSheet>';

$workbook = $X . '<workbook ' . $NS . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Checklist" sheetId="1" r:id="rId1"/></sheets></workbook>';
$wbrels = $X . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
  . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
  . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
$rels = $X . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
  . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
$types = $X . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
  . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
  . '<Default Extension="xml" ContentType="application/xml"/>'
  . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
  . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
  . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';

$tmp = tempnam(sys_get_temp_dir(), 'xlsx');
$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) die('Gagal membuat file Excel.');
$zip->addFromString('[Content_Types].xml', $types);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('xl/workbook.xml', $workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels', $wbrels);
$zip->addFromString('xl/styles.xml', $styles);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
$zip->close();

$name = 'checklist_konservasi_energi' . ($id ? '_' . $id : '') . '_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize($tmp));
header('Cache-Control: no-store');
readfile($tmp);
unlink($tmp);
exit;
