<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (strlen($username) < 3) {
        $error = "Username minimal 3 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username hanya boleh huruf, angka, dan underscore.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Username sudah dipakai.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $nama, $username, $hash);
            mysqli_stmt_execute($stmt);
            $success = "Akun berhasil dibuat! Silakan login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <h2>Buat Akun</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="login.php">Masuk</a></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required placeholder="Nama kamu">
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="contoh: andini">
            <small>Ini yang muncul di URL halaman publikmu</small>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Min. 6 karakter">
        </div>
        <button type="submit" name="register">Daftar</button>
    </form>
    <p class="auth-footer">Sudah punya akun? <a href="login.php">Masuk</a></p>
</div>
</body>
</html>