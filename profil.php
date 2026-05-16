<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$error = '';
$success = '';

// Tarik data user terbaru dari database
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (isset($_POST['simpan_profil'])) {
    $nama = trim($_POST['nama']);
    $bio = trim($_POST['bio']);
    $warna_tema = trim($_POST['warna_tema']);
    $foto_nama = $user['foto']; // Gunakan foto lama sebagai default

    if ($nama === '') {
        $error = "Nama lengkap tidak boleh kosong.";
    } else {
        // Proses Upload Foto jika ada file yang diunggah
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png'];

            if (in_array($file_ext, $ekstensi_diperbolehkan)) {
                // Berikan nama unik baru agar file tidak saling menimpa
                $foto_nama = "user_" . $id_user . "_" . time() . "." . $file_ext;
                $destinasi = "uploads/" . $foto_nama;

                // Pindahkan file ke folder uploads/
                if (!move_uploaded_file($file_tmp, $destinasi)) {
                    $error = "Gagal mengunggah foto profil.";
                }
            } else {
                $error = "Ekstensi file foto harus JPG, JPEG, atau PNG.";
            }
        }

        if ($error === '') {
            // Update data di database
            $upd = mysqli_prepare($conn, "UPDATE users SET nama = ?, bio = ?, foto = ?, warna_tema = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "ssssi", $nama, $bio, $foto_nama, $warna_tema, $id_user);
            mysqli_stmt_execute($upd);

            // Update session nama agar di dashboard juga langsung berubah
            $_SESSION['nama'] = $nama;

            $success = "Profil berhasil diperbarui!";
            
            // Refresh data user
            $user['nama'] = $nama;
            $user['bio'] = $bio;
            $user['foto'] = $foto_nama;
            $user['warna_tema'] = $warna_tema;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-card" style="max-width: 500px;">
    <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    <h2 style="margin-bottom:1.5rem">Edit Profil</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Wajib pakai enctype="multipart/form-data" untuk form yang ada input file/gambar -->
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required value="<?= htmlspecialchars($user['nama']) ?>">
        </div>
        <div class="form-group">
            <label>Bio Singkat</label>
            <textarea name="bio" rows="3" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:14px; font-family:sans-serif; resize:none;"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Warna Tema Halaman Publik</label>
            <input type="color" name="warna_tema" value="<?= htmlspecialchars($user['warna_tema'] ?? '#6366f1') ?>" style="height:40px; padding:2px; cursor:pointer;">
        </div>
        <div class="form-group">
            <label>Foto Profil</label>
            <input type="file" name="foto" accept="image/png, image/jpeg, image/jpg">
            <small>Kosongkan jika tidak ingin mengubah foto profil.</small>
        </div>
        <button type="submit" name="simpan_profil">Simpan Profil</button>
    </form>
</div>
</body>
</html>