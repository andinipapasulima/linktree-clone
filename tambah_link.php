<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$error   = '';

if (isset($_POST['simpan'])) {
    $judul = trim($_POST['judul']);
    $url   = trim($_POST['url']);

    if ($judul === '') {
        $error = "Judul tidak boleh kosong.";
    } elseif ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        $error = "URL tidak valid. Pastikan diawali https://";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT MAX(urutan) as max_urutan FROM links WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        $res     = mysqli_stmt_get_result($stmt);
        $row     = mysqli_fetch_assoc($res);
        $urutan  = ($row['max_urutan'] ?? 0) + 1;

        $ins = mysqli_prepare($conn, "INSERT INTO links (user_id, judul, url, urutan) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, "issi", $id_user, $judul, $url, $urutan);
        mysqli_stmt_execute($ins);
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Link</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <a href="dashboard.php" class="back-link">← Kembali</a>
    <h2 style="margin-bottom:1.5rem">Tambah Link</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" required placeholder="contoh: Instagram saya"
                   value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>URL</label>
            <input type="url" name="url" required placeholder="https://instagram.com/username"
                   value="<?= htmlspecialchars($_POST['url'] ?? '') ?>">
        </div>
        <button type="submit" name="simpan">Simpan Link</button>
    </form>
</div>
</body>
</html>