<?php
$pageTitle = $pageTitle ?? 'Konservasi Energi';
$cur = basename($_SERVER['PHP_SELF']);
function nav_active($files) { global $cur; return in_array($cur, (array)$files) ? ' class="active"' : ''; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> - Konservasi Energi</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="nav">
    <div class="title"><span class="logo">⚡</span>Konservasi Energi</div>
    <div>
        <a href="index.php"<?= nav_active('index.php') ?>>Dashboard</a>
        <?php if (can('siswa.view')): ?><a href="siswa.php"<?= nav_active(['siswa.php','siswa_tambah.php','siswa_edit.php']) ?>>Siswa</a><?php endif; ?>
        <?php if (can('guru.view')): ?><a href="guru.php"<?= nav_active(['guru.php','guru_tambah.php','guru_edit.php']) ?>>Guru</a><?php endif; ?>
        <?php if (can('perangkat.view')): ?><a href="perangkat.php"<?= nav_active(['perangkat.php','perangkat_tambah.php','perangkat_edit.php']) ?>>Perangkat</a><?php endif; ?>
        <a href="checklist.php"<?= nav_active(['checklist.php','checklist_tambah.php','checklist_detail.php']) ?>>Checklist</a>
        <span class="user"><?= e($_SESSION['nama']) ?> (<?= e($_SESSION['level']) ?>)</span>
        <a href="logout.php">Logout</a>
    </div>
</div>
<div class="container">
<?php if ($m = get_flash()): ?><div class="alert success"><?= e($m) ?></div><?php endif; ?>
