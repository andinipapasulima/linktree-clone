<?php
session_start();
include 'koneksi.php';

// Proteksi halaman dashboard (wajib login)
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// OTOMATIS DETEKSI KEY SESSION KAMU:
$user_id = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['id_user'])) {
    $user_id = $_SESSION['id_user'];
} elseif (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
}

if ($user_id === '' && isset($_SESSION['username'])) {
    $sess_username = $_SESSION['username'];
    $get_user_by_name = mysqli_query($conn, "SELECT id FROM users WHERE username = '$sess_username'");
    $res_by_name = mysqli_fetch_assoc($get_user_by_name);
    if ($res_by_name) {
        $user_id = $res_by_name['id'];
    }
}

if ($user_id === '') {
    header("Location: logout.php");
    exit;
}

// Ambil data user yang sedang login
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query_user);

// Ambil statistik kilat
$query_count = mysqli_query($conn, "SELECT COUNT(*) as total_link FROM links WHERE user_id = '$user_id'");
$count_data = mysqli_fetch_assoc($query_count);

// Ambil semua daftar link milik user
$links = mysqli_query($conn, "SELECT * FROM links WHERE user_id = '$user_id' ORDER BY urutan ASC");

$foto_path = "uploads/" . ($user['foto'] ?? '');
if (!file_exists($foto_path) || empty($user['foto'])) {
    $foto_path = "uploads/default.png";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Neo-Linktree</title>
    <style>
        /* ==========================================================================
           DASHBOARD NEO-BRUTALISME PREMIUM SYSTEM (1-COLUMN REVISED)
           ========================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #e3fafc; 
            background-image: radial-gradient(#1a1a1a 1.2px, transparent 1.2px);
            background-size: 20px 20px;
            color: #1a1a1a;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* HEADER NAVIGASI */
        .dash-header {
            background: #ffffff;
            padding: 24px;
            border-radius: 20px;
            border: 4px solid #1a1a1a;
            box-shadow: 8px 8px 0px #1a1a1a;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .user-profile-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dash-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: 3px solid #1a1a1a;
            object-fit: cover;
        }

        .user-profile-info h2 {
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .user-profile-info p {
            font-size: 13px;
            font-weight: 700;
            color: #666;
        }

        /* TOMBOL KONSOL RETRO */
        .btn-retro {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            color: #1a1a1a;
            border: 3px solid #1a1a1a;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.1s ease;
        }
        .btn-blue { background: #94ffd8; box-shadow: 3px 3px 0px #1a1a1a; }
        .btn-danger { background: #ff6b6b; color: white; box-shadow: 3px 3px 0px #1a1a1a; }
        .btn-yellow { background: #ffde4d; box-shadow: 3px 3px 0px #1a1a1a; }

        .btn-retro:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #1a1a1a;
        }
        .btn-retro:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px #1a1a1a;
        }

        /* KARTU UTAMA */
        .dash-card {
            background: #ffffff;
            border: 4px solid #1a1a1a;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 8px 8px 0px #1a1a1a;
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* STATS CANVAS DISPLAY (Lebar penuh di atas list) */
        .stats-display {
            background: #b460f3;
            color: white;
            padding: 18px 24px;
            border-radius: 18px;
            border: 4px solid #1a1a1a;
            box-shadow: 6px 6px 0px #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .stats-text {
            text-align: left;
        }
        .stats-label {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }
        .stats-number {
            font-size: 34px;
            font-weight: 900;
        }

        /* ITEM LINK CARDS LIST (LEBIH LEGA) */
        .links-list-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .link-item-card {
            background: #ffffff;
            border: 3px solid #1a1a1a;
            border-radius: 16px;
            padding: 18px 24px;
            box-shadow: 5px 5px 0px #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease;
        }

        .link-item-card:hover {
            transform: scale(1.005) translate(-1px, -1px);
            box-shadow: 6px 6px 0px #1a1a1a;
        }

        .link-details h4 {
            font-size: 17px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .link-details p {
            font-size: 13px;
            font-weight: 600;
            color: #555;
            word-break: break-all;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            border-radius: 20px;
            border: 2px solid #1a1a1a;
            margin-top: 8px;
        }
        .badge-active { background: #94ffd8; }
        .badge-inactive { background: #ff6b6b; color: white; }

        .link-actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 800;
            border: 2px solid #1a1a1a;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            color: #1a1a1a;
            box-shadow: 2px 2px 0px #1a1a1a;
            transition: all 0.1s ease;
        }
        .btn-edit { background: #ffde4d; }
        .btn-delete { background: #ff6b6b; color: white; }

        .btn-action:hover {
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px #1a1a1a;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            font-weight: 700;
            color: #555;
            border: 3px dashed #1a1a1a;
            border-radius: 16px;
            background: #fff;
        }

        /* ==========================================================================
   EFEK ANIMASI & POLESAN TAMBAHAN (MAKIN MEMUKAU)
   ========================================================================== */

/* 1. Animasi Kartu Link Saat Di-hover */
.link-item-card {
    background: #ffffff;
    border: 3px solid #1a1a1a;
    border-radius: 16px;
    padding: 18px 24px;
    box-shadow: 5px 5px 0px #1a1a1a;
    display: flex;
    justify-content: space-between;
    align-items: center;
    /* Tambahkan transition biar gerakannya mulus */
    transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s ease;
}

.link-item-card:hover {
    transform: translate(-4px, -4px); /* Kartu naik ke atas kiri */
    box-shadow: 9px 9px 0px #1a1a1a; /* Bayangan memanjang */
    background-color: #fffdec; /* Sedikit efek highlight hangat */
}

/* 2. Pembeda Baris Otomatis (Zebra Striping ala Brutalisme) */
/* Kartu urutan genap akan otomatis punya aksen warna latar yang berbeda tipis */
.links-list-container .link-item-card:nth-child(even) {
    background-color: #faf8ff;
}
.links-list-container .link-item-card:nth-child(even):hover {
    background-color: #fffdec;
}

/* 3. Efek Klik Tombol Aksi Melesek ke Dalam */
.btn-action {
    padding: 10px 14px;
    font-size: 14px;
    font-weight: 800;
    border: 2px solid #1a1a1a;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    color: #1a1a1a;
    box-shadow: 2px 2px 0px #1a1a1a;
    transition: transform 0.1s ease, box-shadow 0.1s ease;
}

.btn-action:hover {
    transform: translate(-1px, -1px);
    box-shadow: 3px 3px 0px #1a1a1a;
}

/* Efek pas diklik (Active State) */
.btn-action:active {
    transform: translate(2px, 2px); /* Tombol terdorong ke arah bayangan */
    box-shadow: 0px 0px 0px #1a1a1a; /* Bayangan hilang seolah menyentuh lantai */
}

/* Khusus tombol urutan panah, kita kasih efek warna hover biar interaktif */
.link-ordering .btn-action:hover {
    background: #ff76ce !important; /* Berubah jadi pink nyala pas di-hover */
    color: white;
}
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <header class="dash-header">
        <div class="user-profile-info">
            <img src="<?= $foto_path ?>" alt="Avatar" class="dash-avatar">
            <div>
                <h2>Hello, <?= htmlspecialchars($user['nama'] ?? 'User') ?>!</h2>
                <p>@<?= htmlspecialchars($user['username'] ?? 'username') ?></p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="tambah_link.php" class="btn-retro btn-yellow">➕ TAMBAH TAUTAN BARU</a>
            <a href="u.php?user=<?= $user['username'] ?? '' ?>" target="_blank" class="btn-retro btn-blue">🌐 LIHAT WEB</a>
            <a href="logout.php" class="btn-retro btn-danger">🚪 KELUAR</a>
        </div>
    </header>

    <div class="stats-display">
        <div class="stats-text">
            <div class="stats-label">Total Tautan Aktif Ditampilkan</div>
        </div>
        <div class="stats-number"><?= $count_data['total_link'] ?? 0 ?></div>
    </div>

    <main class="dash-card">
        <h3 class="card-title">📂 Kelola Semua Tautan</h3>
        
        <div class="links-list-container">
            <?php if (!$links || mysqli_num_rows($links) === 0): ?>
                <div class="empty-state">
                    Belum ada tautan yang dibuat. Klik tombol <strong>Tambah Tautan Baru</strong> di atas untuk membuat tautan pertamamu!
                </div>
            <?php else: ?>
                <?php while ($link = mysqli_fetch_assoc($links)): ?>
                    <div class="link-item-card" style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
    
    <div class="link-details" style="flex-grow: 1;">
        <h4><?= htmlspecialchars($link['judul']) ?></h4>
        <p><?= htmlspecialchars($link['url']) ?></p>
        
        <?php if ($link['aktif'] == 1): ?>
            <span class="badge-status badge-active">Aktif</span>
        <?php else: ?>
            <span class="badge-status badge-inactive">Non-Aktif</span>
        <?php endif; ?>
    </div>
    
    <div class="link-controls-wrapper" style="display: flex; align-items: center; gap: 15px;">
        
        <div class="link-actions" style="display: flex; gap: 8px;">
            <a href="edit_link.php?id=<?= $link['id'] ?>" class="btn-action btn-edit">📝</a>
            <a href="hapus_link.php?id=<?= $link['id'] ?>" onclick="return confirm('Yakin ingin menghapus tautan ini?')" class="btn-action btn-delete">🗑️</a>
        </div>

        <div class="link-ordering" style="display: flex; flex-direction: column; gap: 4px;">
            <a href="ubah_urutan.php?id=<?= $link['id'] ?>&aksi=naik" class="btn-action" style="background: #94ffd8; padding: 4px 10px; font-size: 11px; line-height: 1;">🔼</a>
            <a href="ubah_urutan.php?id=<?= $link['id'] ?>&aksi=turun" class="btn-action" style="background: #94ffd8; padding: 4px 10px; font-size: 11px; line-height: 1;">🔽</a>
        </div>

    </div>

</div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

</div>

</body>
</html>