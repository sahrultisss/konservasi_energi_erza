<?php
require 'auth.php';
require_can('siswa.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nis = trim($_POST['nis'] ?? '');
    $stmt = mysqli_prepare($conn, "DELETE FROM siswa WHERE nis=?");
    mysqli_stmt_bind_param($stmt, "s", $nis);
    mysqli_stmt_execute($stmt);
    flash('Siswa berhasil dihapus.');
}
header('Location: siswa.php'); exit;
