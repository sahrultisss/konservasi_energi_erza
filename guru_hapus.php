<?php
require 'auth.php';
require_can('guru.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nip = trim($_POST['nip'] ?? '');
    $stmt = mysqli_prepare($conn, "DELETE FROM guru WHERE nip=?");
    mysqli_stmt_bind_param($stmt, "s", $nip);
    mysqli_stmt_execute($stmt);
    flash('Guru berhasil dihapus.');
}
header('Location: guru.php'); exit;
