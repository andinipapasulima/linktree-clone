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

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (isset($_POST['simpan_profil'])) {
    $nama = trim($_POST['nama']);
    $bio = trim($_POST['bio']);
    $warna_tema = trim($_POST['warna_tema']);
    $custom_css = trim($_POST['custom_css']);
    $foto_nama = $user['foto'];
    $background_image = $user['background_image'];

    if ($nama === '') {
        $error = "Nama lengkap tidak boleh kosong.";
    } else {
        // Upload foto profil
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($file_ext, $ekstensi_diperbolehkan)) {
                $foto_nama = "user_" . $id_user . "_" . time() . "." . $file_ext;
                $destinasi = "uploads/" . $foto_nama;

                if (!move_uploaded_file($file_tmp, $destinasi)) {
                    $error = "Gagal mengunggah foto profil.";
                } else {
                    if ($user['foto'] && $user['foto'] != 'default.png' && file_exists("uploads/" . $user['foto'])) {
                        unlink("uploads/" . $user['foto']);
                    }
                }
            } else {
                $error = "Ekstensi file foto harus JPG, JPEG, PNG, GIF, atau WEBP.";
            }
        }

        // Upload background image
        if (isset($_FILES['background_img']) && $_FILES['background_img']['error'] === UPLOAD_ERR_OK) {
            $bg_tmp = $_FILES['background_img']['tmp_name'];
            $bg_name = $_FILES['background_img']['name'];
            $bg_ext = strtolower(pathinfo($bg_name, PATHINFO_EXTENSION));
            
            if (in_array($bg_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $background_image = "bg_" . $id_user . "_" . time() . "." . $bg_ext;
                $bg_destinasi = "uploads/backgrounds/" . $background_image;
                
                if (!is_dir("uploads/backgrounds")) {
                    mkdir("uploads/backgrounds", 0777, true);
                }
                
                if (!move_uploaded_file($bg_tmp, $bg_destinasi)) {
                    $error = "Gagal mengunggah background.";
                }
            } else {
                $error = "Background harus format gambar.";
            }
        }

        if ($error === '') {
            $upd = mysqli_prepare($conn, "UPDATE users SET nama = ?, bio = ?, foto = ?, warna_tema = ?, background_image = ?, custom_css = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "ssssssi", $nama, $bio, $foto_nama, $warna_tema, $background_image, $custom_css, $id_user);
            mysqli_stmt_execute($upd);

            $_SESSION['nama'] = $nama;
            $success = "✅ Profil berhasil diperbarui!";
            
            $user['nama'] = $nama;
            $user['bio'] = $bio;
            $user['foto'] = $foto_nama;
            $user['warna_tema'] = $warna_tema;
            $user['background_image'] = $background_image;
            $user['custom_css'] = $custom_css;
        }
    }
}

$preview_url = "u.php?user=" . $user['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil | Neo-Linktree</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #e3fafc;
            background-image: radial-gradient(#1a1a1a 1px, transparent 1px);
            background-size: 20px 20px;
            color: #1a1a1a;
            padding: 20px 16px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }

        .profile-card {
            background: #ffffff;
            border: 3px solid #1a1a1a;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 8px 8px 0px #1a1a1a;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 20px;
            padding: 6px 12px;
            background: #94ffd8;
            border: 2px solid #1a1a1a;
            border-radius: 10px;
            text-decoration: none;
            color: #1a1a1a;
            font-weight: 700;
            font-size: 12px;
            box-shadow: 3px 3px 0px #1a1a1a;
            transition: all 0.1s ease;
        }

        .back-link:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #1a1a1a;
        }

        h2 {
            font-size: 22px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 800;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 11px;
        }

        input[type="text"],
        input[type="color"],
        textarea,
        input[type="file"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #1a1a1a;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            background: white;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px #1a1a1a;
        }

        textarea {
            resize: vertical;
            font-family: inherit;
        }

        .preview-box {
            background: #faf8ff;
            border: 2px solid #1a1a1a;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .preview-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid #1a1a1a;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #b460f3;
            color: white;
            border: 2px solid #1a1a1a;
            border-radius: 10px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 4px 4px 0px #1a1a1a;
            transition: all 0.1s ease;
        }

        .btn-submit:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px #1a1a1a;
        }

        .alert {
            padding: 10px 14px;
            border: 2px solid #1a1a1a;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 12px;
            box-shadow: 3px 3px 0px #1a1a1a;
        }
        .alert-error { background: #ff6b6b; color: white; }
        .alert-success { background: #94ffd8; color: #1a1a1a; }

        small {
            display: block;
            margin-top: 4px;
            font-size: 10px;
            color: #666;
        }

        hr {
            margin: 20px 0;
            border: 1px dashed #ddd;
        }

        @media (max-width: 600px) {
            .profile-card {
                padding: 16px;
            }
            h2 {
                font-size: 18px;
            }
        }

        /* Form pages - fix auto zoom */
.form-group input,
.form-group textarea,
.form-group select {
    font-size: 16px !important;
}
    </style>
</head>
<body>

<div class="profile-container">
    <div class="profile-card">
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h2><i class="fas fa-user-edit"></i> Edit Profil</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="preview-box">
            <img src="<?= "uploads/" . ($user['foto'] ?? 'default.png') ?>" class="preview-avatar" id="previewAvatar">
            <p><strong>Preview Halaman:</strong><br>
            <a href="<?= $preview_url ?>" target="_blank" style="color: #b460f3;"><?= $preview_url ?></a></p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required value="<?= htmlspecialchars($user['nama']) ?>">
            </div>
            
            <div class="form-group">
                <label>Bio Singkat</label>
                <textarea name="bio" rows="3" placeholder="Tulis sesuatu tentang dirimu..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Warna Tema</label>
                <input type="color" name="warna_tema" value="<?= htmlspecialchars($user['warna_tema'] ?? '#6366f1') ?>">
                <small>Warna tombol saat di-hover di halaman publik</small>
            </div>
            
            <div class="form-group">
                <label>Custom CSS (Opsional)</label>
                <textarea name="custom_css" rows="5" placeholder="Tulis CSS custom untuk halaman publikmu..."><?= htmlspecialchars($user['custom_css'] ?? '') ?></textarea>
                <small>Contoh: .profile-name { font-size: 30px; }</small>
            </div>
            
            <!-- Di dalam form, setelah input background_img -->
<div class="form-group">
    <label>Background Halaman Publik</label>
    <input type="file" name="background_img" accept="image/*">
    <small>Upload gambar background (opsional)</small>
    <?php if ($user['background_image']): ?>
        <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span style="font-size: 11px;">Current: <?= $user['background_image'] ?></span>
            <button type="button" onclick="hapusBackground()" class="btn-action" style="background: #ff6b6b; color: white; padding: 4px 10px;">
                <i class="fas fa-trash"></i> Hapus Background
            </button>
        </div>
    <?php endif; ?>
</div>
            
            <hr>
            
            <button type="submit" name="simpan_profil" class="btn-submit"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>

<script>
document.getElementById('fotoInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewAvatar').src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});

function hapusBackground() {
    if (confirm('Hapus background halaman publik?')) {
        fetch('api_hapus_background.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('✅ Background berhasil dihapus!');
                location.reload();
            } else {
                alert('❌ Gagal menghapus background');
            }
        });
    }
}
</script>

</body>
</html>