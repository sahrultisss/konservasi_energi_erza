<?php
require 'auth.php';
require_can('checklist.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $s = mysqli_prepare($conn, "DELETE FROM checklist WHERE id_checklist=?");
    mysqli_stmt_bind_param($s, "i", $id);
    mysqli_stmt_execute($s);
    flash('Checklist berhasil dihapus.');
}
header('Location: checklist.php'); exit;
