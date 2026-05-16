<?php
session_start();
include 'koneksi.php';

// Proteksi halaman dashboard
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

// Ambil data user
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query_user);

// Ambil statistik
$query_count = mysqli_query($conn, "SELECT COUNT(*) as total_link FROM links WHERE user_id = '$user_id'");
$count_data = mysqli_fetch_assoc($query_count);

$query_klik = mysqli_query($conn, "SELECT SUM(jumlah_klik) as total_klik FROM links WHERE user_id = '$user_id'");
$klik_data = mysqli_fetch_assoc($query_klik);

$query_aktif = mysqli_query($conn, "SELECT COUNT(*) as aktif_link FROM links WHERE user_id = '$user_id' AND aktif = 1");
$aktif_data = mysqli_fetch_assoc($query_aktif);

// Ambil visitor count
$query_visitor = mysqli_query($conn, "SELECT COUNT(DISTINCT visitor_ip) as total_visitor FROM visitors WHERE user_id = '$user_id'");
$visitor_data = mysqli_fetch_assoc($query_visitor);

// Ambil semua link
$links = mysqli_query($conn, "SELECT * FROM links WHERE user_id = '$user_id' ORDER BY urutan ASC");

$foto_path = "uploads/" . ($user['foto'] ?? '');
if (!file_exists($foto_path) || empty($user['foto'])) {
    $foto_path = "uploads/default.png";
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Dashboard Admin | Neo-Linktree</title>
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
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* HEADER */
        .dash-header {
            background: #ffffff;
            padding: 20px;
            border-radius: 20px;
            border: 3px solid #1a1a1a;
            box-shadow: 6px 6px 0px #1a1a1a;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .user-profile-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dash-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #1a1a1a;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .dash-avatar:hover {
            transform: scale(1.05);
        }

        .user-profile-info h2 {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .user-profile-info p {
            font-size: 12px;
            font-weight: 600;
            color: #666;
        }

        /* TOMBOL */
        .btn-retro {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            color: #1a1a1a;
            border: 2.5px solid #1a1a1a;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.1s ease;
        }
        
        .btn-blue { background: #94ffd8; box-shadow: 3px 3px 0px #1a1a1a; }
        .btn-danger { background: #ff6b6b; color: white; box-shadow: 3px 3px 0px #1a1a1a; }
        .btn-yellow { background: #ffde4d; box-shadow: 3px 3px 0px #1a1a1a; }
        .btn-purple { background: #b460f3; color: white; box-shadow: 3px 3px 0px #1a1a1a; }
        .btn-green { background: #10b981; color: white; box-shadow: 3px 3px 0px #1a1a1a; }

        .btn-retro:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #1a1a1a;
        }
        .btn-retro:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px #1a1a1a;
        }

        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stats-display {
            background: #b460f3;
            color: white;
            padding: 16px 12px;
            border-radius: 16px;
            border: 3px solid #1a1a1a;
            box-shadow: 5px 5px 0px #1a1a1a;
            text-align: center;
        }
        
        .stats-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            margin-bottom: 6px;
        }
        
        .stats-number {
            font-size: 24px;
            font-weight: 900;
        }

        /* MAIN CARD */
        .dash-card {
            background: #ffffff;
            border: 3px solid #1a1a1a;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 6px 6px 0px #1a1a1a;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* SEARCH BAR */
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            border: 2.5px solid #1a1a1a;
            border-radius: 40px;
            box-shadow: 3px 3px 0px #1a1a1a;
            outline: none;
        }
        
        .search-input:focus {
            transform: translate(-1px, -1px);
            box-shadow: 4px 4px 0px #1a1a1a;
        }

        /* LINK ITEMS */
        .links-list-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .link-item-card {
            background: #ffffff;
            border: 2.5px solid #1a1a1a;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 4px 4px 0px #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .link-item-card:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px #1a1a1a;
            background-color: #fffdec;
        }

        .link-details {
            flex: 1;
            min-width: 0;
        }

        .link-details h4 {
            font-size: 14px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 4px;
            word-break: break-word;
        }

        .link-details h4 i {
            margin-right: 8px;
            width: 20px;
        }

        .link-details p {
            font-size: 11px;
            font-weight: 600;
            color: #555;
            word-break: break-all;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            border-radius: 20px;
            border: 1.5px solid #1a1a1a;
            margin-top: 6px;
        }
        
        .badge-active { background: #94ffd8; }
        .badge-inactive { background: #ff6b6b; color: white; }
        .badge-scheduled { background: #ffde4d; }

        .click-count {
            display: inline-block;
            margin-top: 6px;
            margin-left: 8px;
            font-size: 10px;
            font-weight: 700;
            color: #b460f3;
        }

        .short-url-badge {
            display: inline-block;
            margin-top: 6px;
            margin-left: 8px;
            font-size: 9px;
            font-weight: 600;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* ACTION BUTTONS */
        .link-controls-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .link-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            border: 2px solid #1a1a1a;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #1a1a1a;
            box-shadow: 2px 2px 0px #1a1a1a;
            transition: all 0.1s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .btn-edit { background: #ffde4d; }
        .btn-delete { background: #ff6b6b; color: white; }
        .btn-toggle { background: #94ffd8; }

        .btn-action:hover {
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px #1a1a1a;
        }

        .btn-action:active {
            transform: translate(1px, 1px);
            box-shadow: 0px 0px 0px #1a1a1a;
        }

        /* ORDER BUTTONS */
        .link-ordering {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        
        .link-ordering .btn-action {
            padding: 3px 8px;
            font-size: 10px;
            background: #94ffd8;
        }

        /* ALERT */
        .alert {
            padding: 10px 14px;
            border: 2.5px solid #1a1a1a;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 13px;
            box-shadow: 4px 4px 0px #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .alert-success { background: #94ffd8; color: #1a1a1a; }
        .alert-error { background: #ff6b6b; color: white; }
        
        .close-alert {
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            background: none;
            border: none;
            color: inherit;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            font-weight: 700;
            color: #555;
            border: 2.5px dashed #1a1a1a;
            border-radius: 14px;
            background: #fff;
            font-size: 13px;
        }

        /* MODAL IMPORT */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            border: 3px solid #1a1a1a;
            border-radius: 16px;
            padding: 24px;
            max-width: 400px;
            width: 90%;
            box-shadow: 8px 8px 0px #1a1a1a;
        }
        
        .modal-content h3 {
            margin-bottom: 16px;
        }
        
        .modal-content input {
            width: 100%;
            padding: 10px;
            border: 2px solid #1a1a1a;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .close-modal {
            float: right;
            cursor: pointer;
            font-size: 24px;
            font-weight: bold;
        }

        /* TOAST */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1a1a1a;
            color: white;
            padding: 10px 16px;
            border-radius: 10px;
            border: 2px solid #1a1a1a;
            font-weight: 700;
            font-size: 12px;
            display: none;
            z-index: 1001;
            animation: slideIn 0.3s ease;
            box-shadow: 4px 4px 0px rgba(0,0,0,0.2);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .dash-header {
                flex-direction: column;
                text-align: center;
            }
            
            .user-profile-info {
                flex-direction: column;
            }
            
            .link-item-card {
                flex-direction: column;
                gap: 12px;
            }
            
            .link-controls-wrapper {
                width: 100%;
                justify-content: space-between;
            }
        }

        @media (max-width: 600px) {
            .stats-number {
                font-size: 18px;
            }
            
            .stats-label {
                font-size: 9px;
            }
            
            .btn-retro span {
                display: none;
            }
            
            .btn-retro {
                padding: 8px 10px;
            }
            
            .btn-retro i {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .btn-action span {
                display: none;
            }
            
            .btn-action {
                padding: 6px 8px;
            }
            
            .link-ordering .btn-action span {
                display: inline;
            }
        }

        /* Dashboard - fix auto zoom */
.search-input,
.btn-action,
.btn-retro {
    font-size: 16px; /* Ubah dari 13px/12px jadi 16px */
}

@media (max-width: 768px) {
    .search-input {
        font-size: 16px !important;
    }
}
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <header class="dash-header">
        <div class="user-profile-info">
            <img src="<?= $foto_path ?>" alt="Avatar" class="dash-avatar" id="avatarImg">
            <div>
                <h2>Hello, <?= htmlspecialchars($user['nama'] ?? 'User') ?>!</h2>
                <p>@<?= htmlspecialchars($user['username'] ?? 'username') ?></p>
            </div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <a href="tambah_link.php" class="btn-retro btn-yellow"><i class="fas fa-plus"></i> <span>Tambah</span></a>
            <a href="profil.php" class="btn-retro btn-purple"><i class="fas fa-user-edit"></i> <span>Profil</span></a>
            <a href="analytics.php" class="btn-retro btn-blue"><i class="fas fa-chart-line"></i> <span>Stats</span></a>
            <a href="api_export.php" class="btn-retro btn-green"><i class="fas fa-download"></i> <span>Export</span></a>
            <button onclick="showImportModal()" class="btn-retro btn-blue"><i class="fas fa-upload"></i> <span>Import</span></button>
            <a href="u.php?user=<?= $user['username'] ?? '' ?>" target="_blank" class="btn-retro btn-blue"><i class="fas fa-external-link-alt"></i> <span>Lihat</span></a>
            <a href="logout.php" class="btn-retro btn-danger"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a>
        </div>
    </header>

    <?php if ($success): ?>
        <div class="alert alert-success" id="successAlert">
            <span>✅ <?= htmlspecialchars($success) ?></span>
            <button class="close-alert" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error" id="errorAlert">
            <span>❌ <?= htmlspecialchars($error) ?></span>
            <button class="close-alert" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stats-display" style="background: #b460f3;">
            <div class="stats-label">Total Tautan</div>
            <div class="stats-number"><?= $count_data['total_link'] ?? 0 ?></div>
        </div>
        <div class="stats-display" style="background: #ff76ce;">
            <div class="stats-label">Total Klik</div>
            <div class="stats-number"><?= number_format($klik_data['total_klik'] ?? 0, 0, ',', '.') ?></div>
        </div>
        <div class="stats-display" style="background: #94ffd8; color: #1a1a1a;">
            <div class="stats-label">Tautan Aktif</div>
            <div class="stats-number"><?= $aktif_data['aktif_link'] ?? 0 ?></div>
        </div>
        <div class="stats-display" style="background: #ffde4d; color: #1a1a1a;">
            <div class="stats-label">Visitor Unik</div>
            <div class="stats-number"><?= number_format($visitor_data['total_visitor'] ?? 0, 0, ',', '.') ?></div>
        </div>
    </div>

    <main class="dash-card">
        <div class="card-title">
            <i class="fas fa-list"></i> Kelola Tautan
        </div>
        
        <div class="search-box">
            <input type="text" id="searchLink" class="search-input" placeholder="🔍 Cari tautan...">
        </div>
        
        <div class="links-list-container" id="linksContainer">
            <?php if (!$links || mysqli_num_rows($links) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox" style="font-size: 32px; opacity: 0.5; margin-bottom: 10px; display: block;"></i>
                    Belum ada tautan. Klik <strong>Tambah</strong> untuk mulai!
                </div>
            <?php else: ?>
                <?php while ($link = mysqli_fetch_assoc($links)): 
                    // Ambil short URL
                    $short_query = mysqli_query($conn, "SELECT short_code FROM short_urls WHERE link_id = " . $link['id']);
                    $short_data = mysqli_fetch_assoc($short_query);
                    $short_code = $short_data['short_code'] ?? '';
                    
                    // Cek apakah link terjadwal
                    $is_scheduled = (!empty($link['scheduled_start']) || !empty($link['scheduled_end']));
                    $schedule_text = '';
                    if (!empty($link['scheduled_start'])) {
                        $schedule_text .= 'Mulai: ' . date('d/m/Y H:i', strtotime($link['scheduled_start']));
                    }
                    if (!empty($link['scheduled_end'])) {
                        $schedule_text .= ' Selesai: ' . date('d/m/Y H:i', strtotime($link['scheduled_end']));
                    }
                ?>
                    <div class="link-item-card" data-id="<?= $link['id'] ?>" data-judul="<?= strtolower(htmlspecialchars($link['judul'])) ?>">
                        <div class="link-details">
                            <h4><i class="<?= htmlspecialchars($link['icon'] ?? 'fas fa-link') ?>"></i> <?= htmlspecialchars($link['judul']) ?></h4>
                            <p><?= htmlspecialchars($link['url']) ?></p>
                            <div>
                                <?php if ($is_scheduled): ?>
                                    <span class="badge-status badge-scheduled">⏰ Terjadwal</span>
                                <?php elseif ($link['aktif'] == 1): ?>
                                    <span class="badge-status badge-active">✓ Aktif</span>
                                <?php else: ?>
                                    <span class="badge-status badge-inactive">✗ Nonaktif</span>
                                <?php endif; ?>
                                <span class="click-count"><i class="fas fa-chart-simple"></i> <?= number_format($link['jumlah_klik'] ?? 0, 0, ',', '.') ?></span>
                                <?php if ($short_code): ?>
                                    <span class="short-url-badge"><i class="fas fa-link"></i> s/<?= $short_code ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($schedule_text): ?>
                                <div style="font-size: 9px; color: #888; margin-top: 4px;">📅 <?= $schedule_text ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="link-controls-wrapper">
                            <div class="link-actions">
                                <a href="edit_link.php?id=<?= $link['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i><span> Edit</span></a>
                                <button onclick="toggleStatus(<?= $link['id'] ?>, <?= $link['aktif'] ?>)" class="btn-action btn-toggle">
                                    <i class="fas <?= $link['aktif'] == 1 ? 'fa-eye-slash' : 'fa-eye' ?>"></i><span> <?= $link['aktif'] == 1 ? 'Nonaktif' : 'Aktif' ?></span>
                                </button>
                                <button onclick="copyLink('<?= isset($_SERVER['HTTPS']) ? 'https://' : 'http://' . $_SERVER['HTTP_HOST'] ?>/klik.php?id=<?= $link['id'] ?>')" class="btn-action" style="background: #b460f3; color: white;"><i class="fas fa-copy"></i><span> Salin</span></button>
                                <button onclick="deleteLink(<?= $link['id'] ?>)" class="btn-action btn-delete"><i class="fas fa-trash"></i><span> Hapus</span></button>
                            </div>

                            <div class="link-ordering">
                                <button onclick="updateOrder(<?= $link['id'] ?>, 'naik')" class="btn-action" style="background: #94ffd8;"><i class="fas fa-arrow-up"></i><span></span></button>
                                <button onclick="updateOrder(<?= $link['id'] ?>, 'turun')" class="btn-action" style="background: #94ffd8;"><i class="fas fa-arrow-down"></i><span></span></button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal Import -->
<div id="importModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeImportModal()">&times;</span>
        <h3><i class="fas fa-upload"></i> Import Data</h3>
        <p style="margin-bottom: 16px; font-size: 13px;">Pilih file JSON hasil export sebelumnya</p>
        <input type="file" id="importFile" accept=".json">
        <div class="modal-buttons">
            <button onclick="closeImportModal()" class="btn-action" style="background: #ddd;">Batal</button>
            <button onclick="importData()" class="btn-action" style="background: #10b981; color: white;">Import</button>
        </div>
    </div>
</div>

<!-- Modal Avatar -->
<div id="avatarModal" class="modal">
    <div class="modal-content" style="max-width: 90%; padding: 12px;">
        <span class="close-modal" onclick="closeAvatarModal()">&times;</span>
        <img id="modalAvatarImg" class="modal-img" src="" style="max-width: 100%; border-radius: 12px;">
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
// Toast notification
function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.background = isError ? '#ff6b6b' : '#1a1a1a';
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Delete link
function deleteLink(id) {
    if (confirm('⚠️ Hapus tautan ini?')) {
        fetch(`api_hapus.php?id=${id}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('✅ Tautan dihapus');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('❌ Gagal hapus', true);
            }
        })
        .catch(() => showToast('❌ Error server', true));
    }
}

// Update order
function updateOrder(id, action) {
    fetch(`api_urutan.php?id=${id}&aksi=${action}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('✅ Urutan berubah');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('❌ ' + (data.message || 'Gagal'), true);
        }
    })
    .catch(() => showToast('❌ Error server', true));
}

// Toggle status
function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus == 1 ? 0 : 1;
    fetch(`api_toggle.php?id=${id}&status=${newStatus}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('✅ Status berubah');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('❌ Gagal', true);
        }
    })
    .catch(() => showToast('❌ Error', true));
}

// Copy link
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('📋 Link tersalin!');
    }).catch(() => {
        showToast('❌ Gagal salin', true);
    });
}

// Search filter
document.getElementById('searchLink')?.addEventListener('input', function(e) {
    const keyword = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.link-item-card');
    let visible = 0;
    
    items.forEach(item => {
        const judul = item.getAttribute('data-judul') || '';
        if (judul.includes(keyword)) {
            item.style.display = 'flex';
            visible++;
        } else {
            item.style.display = 'none';
        }
    });
});

// Import modal
function showImportModal() {
    document.getElementById('importModal').style.display = 'flex';
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}

function importData() {
    const fileInput = document.getElementById('importFile');
    const file = fileInput.files[0];
    
    if (!file) {
        showToast('Pilih file JSON terlebih dahulu', true);
        return;
    }
    
    const formData = new FormData();
    formData.append('import_file', file);
    
    showToast('⏳ Mengimpor data...');
    
    fetch('api_import.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Gagal import', true);
        }
        closeImportModal();
    })
    .catch(() => {
        showToast('❌ Error server', true);
        closeImportModal();
    });
}

// Avatar modal
const avatarModal = document.getElementById('avatarModal');
const avatarImg = document.getElementById('avatarImg');
const modalAvatarImg = document.getElementById('modalAvatarImg');

function closeAvatarModal() {
    avatarModal.style.display = 'none';
}

avatarImg?.addEventListener('click', function() {
    avatarModal.style.display = 'flex';
    modalAvatarImg.src = this.src;
});

window.addEventListener('click', (e) => {
    if (e.target === avatarModal) closeAvatarModal();
    if (e.target === document.getElementById('importModal')) closeImportModal();
});

// Auto close alert
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => setTimeout(() => alert.remove(), 3000));
}, 3000);
</script>

</body>
</html>