<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM links WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$link   = mysqli_fetch_assoc($result);

if (!$link) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if (isset($_POST['update'])) {
    $judul = trim($_POST['judul']);
    $url   = trim($_POST['url']);
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if ($judul === '') {
        $error = "Judul tidak boleh kosong.";
    } elseif ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        $error = "URL tidak valid. Pastikan diawali https://";
    } else {
        $upd = mysqli_prepare($conn, "UPDATE links SET judul=?, url=?, aktif=? WHERE id=? AND user_id=?");
        mysqli_stmt_bind_param($upd, "ssiii", $judul, $url, $aktif, $id, $id_user);
        mysqli_stmt_execute($upd);
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
    <title>Edit Link</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <a href="dashboard.php" class="back-link">← Kembali</a>
    <h2 style="margin-bottom:1.5rem">Edit Link</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" required
                   value="<?= htmlspecialchars($link['judul']) ?>">
        </div>
        <div class="form-group">
            <label>URL</label>
            <input type="url" name="url" required
                   value="<?= htmlspecialchars($link['url']) ?>">
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" name="aktif" id="aktif" <?= $link['aktif'] ? 'checked' : '' ?> style="width:auto">
            <label for="aktif" style="margin:0;font-weight:normal">Tampilkan link ini di halaman publik</label>
        </div>
        <button type="submit" name="update">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>