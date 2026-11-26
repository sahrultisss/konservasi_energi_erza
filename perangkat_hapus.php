<?php
require 'auth.php';
require_can('perangkat.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $s = mysqli_prepare($conn, "DELETE FROM perangkat WHERE id_perangkat=?");
    mysqli_stmt_bind_param($s, "i", $id);
    mysqli_stmt_execute($s);
    flash('Perangkat berhasil dihapus.');
}
header('Location: perangkat.php'); exit;
