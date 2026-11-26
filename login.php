<?php
session_start();
include "koneksi.php";

if (isset($_SESSION['level'])) { header("Location: index.php"); exit; }

$error = "";
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id_user, username, password, nama, level FROM users WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($row) {
        $stored = $row['password'];
        $ok = false;
        if (preg_match('/^\$2[ayb]\$/', $stored)) {
            $ok = password_verify($password, $stored);
        } elseif (hash_equals($stored, $password)) {   // password lama (plaintext)
            $ok = true;
            $new = password_hash($password, PASSWORD_DEFAULT); // upgrade otomatis ke hash
            $up = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id_user=?");
            mysqli_stmt_bind_param($up, "si", $new, $row['id_user']);
            mysqli_stmt_execute($up);
        }
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['id_user']  = $row['id_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama']     = $row['nama'];
            $_SESSION['level']    = $row['level'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Konservasi Energi</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
<div class="login-wrap">
    <aside class="login-side">
        <div class="brand">⚡</div>
        <h1>Konservasi Energi</h1>
        <p>Catat setiap perangkat listrik di sekolah, dari sebelum dipakai sampai sesudah dimatikan.</p>
        <ul class="steps">
            <li>SEBELUM</li><li>SELAMA</li><li>SESUDAH</li><li>5R</li>
        </ul>
    </aside>
    <main class="login-card">
        <h2>Selamat datang 👋</h2>
        <p class="muted">Masuk dengan akun yang diberikan sekolah.</p>
        <?php if ($error): ?><div class="alert danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
            <label for="username">Username</label>
            <div class="field">
                <span class="ico">👤</span>
                <input type="text" id="username" name="username" placeholder="Masukkan username" autocomplete="username" required autofocus>
            </div>
            <label for="password">Password</label>
            <div class="field">
                <span class="ico">🔒</span>
                <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                <button type="button" class="toggle" id="toggle" aria-label="Tampilkan password">Lihat</button>
            </div>
            <button class="btn primary full" type="submit" name="login">Masuk &rarr;</button>
        </form>
        <p class="login-foot">Lupa password? Hubungi admin sekolah.</p>
    </main>
</div>
<script>
document.getElementById('toggle').addEventListener('click', function () {
    var p = document.getElementById('password');
    var show = p.type === 'password';
    p.type = show ? 'text' : 'password';
    this.textContent = show ? 'Sembunyi' : 'Lihat';
});
</script>
</body>
</html>
