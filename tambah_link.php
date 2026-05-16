<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$error = '';

// Daftar icon yang tersedia
$icons = [
    'fas fa-link' => 'Default Link',
    'fab fa-instagram' => 'Instagram',
    'fab fa-tiktok' => 'TikTok',
    'fab fa-youtube' => 'YouTube',
    'fab fa-twitter' => 'Twitter/X',
    'fab fa-facebook' => 'Facebook',
    'fab fa-github' => 'GitHub',
    'fab fa-linkedin' => 'LinkedIn',
    'fab fa-whatsapp' => 'WhatsApp',
    'fab fa-telegram' => 'Telegram',
    'fab fa-discord' => 'Discord',
    'fab fa-spotify' => 'Spotify',
    'fas fa-shopping-cart' => 'Toko Online',
    'fas fa-envelope' => 'Email',
    'fas fa-donate' => 'Donasi',
    'fas fa-blog' => 'Blog',
    'fas fa-video' => 'Video',
    'fas fa-music' => 'Music',
    'fas fa-code' => 'Coding',
    'fas fa-camera' => 'Photography'
];

if (isset($_POST['simpan'])) {
    $judul = trim($_POST['judul']);
    $url = trim($_POST['url']);
    $icon = trim($_POST['icon']);
    $short_code = trim($_POST['short_code']);
    $scheduled_start = !empty($_POST['scheduled_start']) ? $_POST['scheduled_start'] : null;
    $scheduled_end = !empty($_POST['scheduled_end']) ? $_POST['scheduled_end'] : null;

    if ($judul === '') {
        $error = "Judul tidak boleh kosong.";
    } elseif ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        $error = "URL tidak valid. Pastikan diawali https://";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT MAX(urutan) as max_urutan FROM links WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        $urutan = ($row['max_urutan'] ?? 0) + 1;

        $ins = mysqli_prepare($conn, "INSERT INTO links (user_id, judul, url, icon, urutan, scheduled_start, scheduled_end) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, "isssiss", $id_user, $judul, $url, $icon, $urutan, $scheduled_start, $scheduled_end);
        
        if (mysqli_stmt_execute($ins)) {
            $link_id = mysqli_insert_id($conn);
            
            // Buat short URL jika ada
            if (!empty($short_code)) {
                $short_code = preg_replace('/[^a-zA-Z0-9_-]/', '', $short_code);
                if (!empty($short_code)) {
                    $check = mysqli_query($conn, "SELECT id FROM short_urls WHERE short_code = '$short_code'");
                    if (mysqli_num_rows($check) == 0) {
                        mysqli_query($conn, "INSERT INTO short_urls (link_id, short_code) VALUES ($link_id, '$short_code')");
                    }
                }
            }
            
            header("Location: dashboard.php?success=Link berhasil ditambahkan");
            exit;
        } else {
            $error = "Gagal menambahkan link.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Link | Neo-Linktree</title>
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
            padding: 20px 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }

        .form-card {
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
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-weight: 800;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 11px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #1a1a1a;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }

        input:focus, select:focus {
            outline: none;
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px #1a1a1a;
        }

        .icon-preview {
            display: inline-block;
            padding: 8px 12px;
            background: #f0f0f0;
            border-radius: 8px;
            margin-top: 5px;
            font-size: 14px;
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
            margin-top: 10px;
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
            background: #ff6b6b;
            color: white;
        }

        small {
            display: block;
            margin-top: 4px;
            font-size: 10px;
            color: #666;
        }

        @media (max-width: 600px) {
            .form-card {
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

<div class="form-container">
    <div class="form-card">
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h2><i class="fas fa-plus"></i> Tambah Link Baru</h2>

        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Judul Link</label>
                <input type="text" name="judul" required placeholder="Contoh: Instagram Saya" value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>URL Tujuan</label>
                <input type="url" name="url" required placeholder="https://instagram.com/username" value="<?= htmlspecialchars($_POST['url'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Icon (untuk tampilan lebih keren)</label>
                <select name="icon" id="iconSelect">
                    <?php foreach ($icons as $icon_value => $icon_name): ?>
                        <option value="<?= $icon_value ?>" <?= ($_POST['icon'] ?? '') == $icon_value ? 'selected' : '' ?>>
                            <?= $icon_name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="icon-preview" id="iconPreview">
                    <i class="fas fa-link"></i> Preview icon
                </div>
            </div>
            
            <div class="form-group">
                <label>Short URL (opsional)</label>
                <input type="text" name="short_code" placeholder="contoh: igku" value="<?= htmlspecialchars($_POST['short_code'] ?? '') ?>">
                <small>Buat link pendek custom: website.com/s/igku</small>
            </div>
            
            <div class="form-group">
                <label>Jadwalkan Tampil (opsional)</label>
                <input type="datetime-local" name="scheduled_start" value="<?= $_POST['scheduled_start'] ?? '' ?>">
                <small>Mulai tampil pada tanggal & waktu tertentu</small>
            </div>
            
            <div class="form-group">
                <label>Selesai Tampil (opsional)</label>
                <input type="datetime-local" name="scheduled_end" value="<?= $_POST['scheduled_end'] ?? '' ?>">
                <small>Berhenti tampil otomatis</small>
            </div>
            
            <button type="submit" name="simpan" class="btn-submit"><i class="fas fa-save"></i> Simpan Link</button>
        </form>
    </div>
</div>

<script>
const iconSelect = document.getElementById('iconSelect');
const iconPreview = document.getElementById('iconPreview');

function updateIconPreview() {
    const selectedIcon = iconSelect.value;
    iconPreview.innerHTML = `<i class="${selectedIcon}"></i> Preview icon`;
}

iconSelect.addEventListener('change', updateIconPreview);
updateIconPreview();
</script>

</body>
</html>