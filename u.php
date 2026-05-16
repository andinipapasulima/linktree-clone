<?php
session_start(); 
include 'koneksi.php';

$username = isset($_GET['user']) ? trim($_GET['user']) : '';

if ($username === '') {
    die("User tidak ditemukan.");
}

$stmt_user = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt_user, "s", $username);
mysqli_stmt_execute($stmt_user);
$res_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($res_user);

if (!$user) {
    die("Halaman tidak ditemukan.");
}

// Update last active
mysqli_query($conn, "UPDATE users SET last_active = NOW() WHERE id = " . $user['id']);

// Query links dengan pengecekan jadwal
$current_time = date('Y-m-d H:i:s');
$stmt_links = mysqli_prepare($conn, "SELECT * FROM links WHERE user_id = ? AND aktif = 1 AND (scheduled_start IS NULL OR scheduled_start <= ?) AND (scheduled_end IS NULL OR scheduled_end >= ?) ORDER BY urutan ASC");
mysqli_stmt_bind_param($stmt_links, "iss", $user['id'], $current_time, $current_time);
mysqli_stmt_execute($stmt_links);
$links = mysqli_stmt_get_result($stmt_links);

$foto_path = "uploads/" . $user['foto'];
if (!file_exists($foto_path) || empty($user['foto'])) {
    $foto_path = "uploads/default.png";
}

$warna_tema = !empty($user['warna_tema']) ? $user['warna_tema'] : '#6366f1';
$total_links = mysqli_num_rows($links);

// Background image
$bg_style = '';
if (!empty($user['background_image']) && file_exists("uploads/backgrounds/" . $user['background_image'])) {
    $bg_style = "background-image: url('uploads/backgrounds/" . $user['background_image'] . "'); background-size: cover; background-position: center; background-attachment: fixed;";
}

// Visitor tracking
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$device = 'Unknown';
$browser = 'Unknown';

if (strpos($user_agent, 'Mobile') !== false) $device = 'Mobile';
elseif (strpos($user_agent, 'Tablet') !== false) $device = 'Tablet';
else $device = 'Desktop';

if (strpos($user_agent, 'Chrome') !== false) $browser = 'Chrome';
elseif (strpos($user_agent, 'Firefox') !== false) $browser = 'Firefox';
elseif (strpos($user_agent, 'Safari') !== false) $browser = 'Safari';
elseif (strpos($user_agent, 'Edge') !== false) $browser = 'Edge';
else $browser = 'Other';

mysqli_query($conn, "INSERT INTO visitors (user_id, visitor_ip, visitor_device, visitor_browser) VALUES ({$user['id']}, '$ip', '$device', '$browser')");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?= htmlspecialchars($user['nama']) ?> | Neo-Linktree</title>
    <meta name="description" content="<?= htmlspecialchars($user['bio'] ?? '') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($user['nama']) ?> | Neo-Linktree">
    <meta property="og:description" content="<?= htmlspecialchars($user['bio'] ?? '') ?>">
    <meta property="og:image" content="<?= $foto_path ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta name="theme-color" content="<?= $warna_tema ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.public-page {
            background-color: #fbc7d4;
            background-image: radial-gradient(#1a1a1a 1px, transparent 1px);
            background-size: 20px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 80px 16px 40px 16px;
            position: relative;
            transition: all 0.3s ease;
            <?php if (!empty($bg_style)): ?>
            <?= $bg_style ?>
            <?php endif; ?>
        }

        /* Dark Theme */
        body.dark-theme {
            background-color: #121212;
            background-image: radial-gradient(#00ff66 1px, transparent 1px);
        }

        body.dark-theme .profile-section,
        body.dark-theme .btn-public-link,
        body.dark-theme .search-input {
            background: #1e1e1e;
            color: #ffffff;
            border-color: #00ff66;
            box-shadow: 6px 6px 0px #00ff66;
        }

        body.dark-theme .profile-name,
        body.dark-theme .profile-bio {
            color: #ffffff;
        }

        body.dark-theme .btn-public-link:hover {
            background-color: #00ff66;
            color: #121212;
        }

        body.dark-theme .profile-badge {
            background: #00ff66;
            color: #121212;
        }

        body.dark-theme .last-active {
            color: #aaa;
        }

        /* Theme Toggle */
        .theme-toggle-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 999;
            background: #ffde4d;
            border: 2px solid #1a1a1a;
            padding: 6px 12px;
            border-radius: 30px;
            font-weight: 800;
            cursor: pointer;
            font-size: 11px;
            box-shadow: 3px 3px 0px #1a1a1a;
            transition: all 0.1s ease;
        }

        .theme-toggle-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #1a1a1a;
        }

        /* Back Button */
        .back-btn {
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 999;
            background: #ffde4d;
            border: 2px solid #1a1a1a;
            padding: 6px 12px;
            border-radius: 30px;
            font-weight: 800;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
            color: #1a1a1a;
            box-shadow: 3px 3px 0px #1a1a1a;
            transition: all 0.1s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #1a1a1a;
        }

        body.dark-theme .back-btn {
            background: #b460f3;
            color: white;
            border-color: #00ff66;
        }

        /* Container */
        .public-container {
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
            z-index: 10;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Profile Section */
        .profile-section {
            background: #ffffff;
            padding: 30px 20px;
            border-radius: 24px;
            border: 3px solid #1a1a1a;
            box-shadow: 8px 8px 0px #1a1a1a;
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .profile-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a1a;
            color: #fff;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            white-space: nowrap;
            box-shadow: 2px 2px 0px #ff76ce;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1a1a1a;
            box-shadow: 5px 5px 0px #1a1a1a;
            margin-bottom: 16px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
        }

        .profile-name {
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 8px;
            word-break: break-word;
        }

        .profile-bio {
            font-size: 13px;
            font-weight: 600;
            color: #4a4a4a;
            line-height: 1.5;
            word-break: break-word;
        }

        .last-active {
            font-size: 10px;
            opacity: 0.5;
            margin-top: 10px;
        }

        /* Search Input */
        .search-input {
            width: 100%;
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 600;
            border: 2px solid #1a1a1a;
            border-radius: 50px;
            box-shadow: 4px 4px 0px #1a1a1a;
            outline: none;
            margin-bottom: 20px;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px <?= $warna_tema ?>;
        }

        /* Links */
        .public-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-public-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            padding: 14px 20px;
            border-radius: 60px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border: 2px solid #1a1a1a;
            box-shadow: 5px 5px 0px #1a1a1a;
            transition: all 0.2s ease;
            gap: 12px;
        }

        .btn-public-link .link-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .btn-public-link i:first-child {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .btn-public-link .arrow-icon {
            font-size: 14px;
            opacity: 0.6;
            transition: transform 0.2s ease;
        }

        .btn-public-link:hover {
            background-color: <?= $warna_tema ?>;
            color: #ffffff;
            transform: translate(-3px, -3px);
            box-shadow: 8px 8px 0px #1a1a1a;
        }

        .btn-public-link:hover .arrow-icon {
            transform: translateX(5px);
            opacity: 1;
        }

        .btn-public-link.hidden-link {
            display: none;
        }

        /* Empty State */
        .muted {
            background: white;
            padding: 30px;
            text-align: center;
            border: 2px solid #1a1a1a;
            border-radius: 16px;
            font-weight: 600;
            box-shadow: 5px 5px 0px #1a1a1a;
        }

        body.dark-theme .muted {
            background: #1e1e1e;
            color: white;
            border-color: #00ff66;
        }

        /* Footer */
        .visitor-counter {
            text-align: center;
            margin-top: 24px;
            font-size: 10px;
            opacity: 0.5;
            padding-bottom: 20px;
        }

        /* Avatar Modal */
        .avatar-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
        }

        .avatar-modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .avatar-modal-img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 16px;
            border: 3px solid white;
        }

        .close-avatar-modal {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 600px) {
            body.public-page {
                padding: 70px 12px 30px 12px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
            }
            
            .profile-name {
                font-size: 20px;
            }
            
            .profile-bio {
                font-size: 12px;
            }
            
            .profile-badge {
                font-size: 9px;
                padding: 3px 12px;
                top: -10px;
            }
            
            .btn-public-link {
                padding: 12px 16px;
                font-size: 13px;
            }
            
            .btn-public-link i:first-child {
                font-size: 16px;
                width: 20px;
            }
            
            .search-input {
                padding: 10px 16px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .profile-avatar {
                width: 70px;
                height: 70px;
            }
            
            .profile-name {
                font-size: 18px;
            }
            
            .btn-public-link {
                padding: 10px 14px;
                font-size: 12px;
            }
            
            .theme-toggle-btn, .back-btn {
                padding: 5px 10px;
                font-size: 10px;
            }
        }

        /* Halaman publik - fix auto zoom */
.search-input {
    font-size: 16px !important;
}

@media (max-width: 600px) {
    .search-input {
        font-size: 16px !important;
    }
}
    </style>
    <?php if (!empty($user['custom_css'])): ?>
        <style><?= $user['custom_css'] ?></style>
    <?php endif; ?>
</head>
<body class="public-page">

<button class="theme-toggle-btn" id="themeToggle">🎨 Mode</button>

<?php if (isset($_SESSION['login'])): ?>
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
<?php endif; ?>

<div class="public-container">
    <div class="profile-section">
        <span class="profile-badge">✨ @<?= htmlspecialchars($user['username']) ?></span>
        <img src="<?= $foto_path ?>" alt="Foto Profil" class="profile-avatar" id="avatarImg">
        <h1 class="profile-name"><?= htmlspecialchars($user['nama']) ?></h1>
        <?php if (!empty($user['bio'])): ?>
            <p class="profile-bio"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        <?php endif; ?>
        <?php if ($user['last_active']): ?>
            <div class="last-active"><i class="fas fa-clock"></i> Last active: <?= date('d M Y', strtotime($user['last_active'])) ?></div>
        <?php endif; ?>
    </div>

    <input type="text" id="searchInput" class="search-input" placeholder="🔍 Cari tautan...">

    <div class="public-links" id="linksContainer">
        <?php if ($total_links === 0): ?>
            <div class="muted">
                <i class="fas fa-link" style="font-size: 32px; opacity: 0.5; margin-bottom: 10px; display: block;"></i>
                Belum ada tautan yang dibagikan.
            </div>
        <?php else: ?>
            <?php while ($link = mysqli_fetch_assoc($links)): ?>
                <a href="klik.php?id=<?= $link['id'] ?>" target="_blank" class="btn-public-link" data-judul="<?= strtolower(htmlspecialchars($link['judul'])) ?>">
                    <div class="link-content">
                        <i class="<?= htmlspecialchars($link['icon'] ?? 'fas fa-link') ?>"></i>
                        <span><?= htmlspecialchars($link['judul']) ?></span>
                    </div>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </a>
            <?php endwhile; ?>
            <div class="muted" id="notFoundMsg" style="display: none;">
                <i class="fas fa-search"></i><br>
                Tidak ada tautan yang ditemukan
            </div>
        <?php endif; ?>
    </div>
    
    <div class="visitor-counter">
        <i class="fas fa-chart-line"></i> Neo-Linktree
    </div>
</div>

<!-- Avatar Modal -->
<div id="avatarModal" class="avatar-modal">
    <div class="avatar-modal-content">
        <span class="close-avatar-modal" id="closeAvatarModal">&times;</span>
        <img id="modalAvatarImg" class="avatar-modal-img" src="">
    </div>
</div>

<script>
// Theme Toggle
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

themeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-theme');
    if(body.classList.contains('dark-theme')) {
        themeToggle.innerHTML = '🌙 Dark';
    } else {
        themeToggle.innerHTML = '🎨 Pop';
    }
});

// Search Filter
const searchInput = document.getElementById('searchInput');
const linkButtons = document.querySelectorAll('.btn-public-link');
const notFoundMsg = document.getElementById('notFoundMsg');

if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const keyword = e.target.value.toLowerCase().trim();
        let matchesFound = 0;

        linkButtons.forEach(link => {
            const judul = link.getAttribute('data-judul') || link.innerText.toLowerCase();
            if (judul.includes(keyword)) {
                link.classList.remove('hidden-link');
                matchesFound++;
            } else {
                link.classList.add('hidden-link');
            }
        });

        if (notFoundMsg) {
            if (matchesFound === 0 && keyword !== '' && linkButtons.length > 0) {
                notFoundMsg.style.display = 'block';
            } else {
                notFoundMsg.style.display = 'none';
            }
        }
    });
}

// Avatar Modal
const avatarImg = document.getElementById('avatarImg');
const avatarModal = document.getElementById('avatarModal');
const modalAvatarImg = document.getElementById('modalAvatarImg');
const closeAvatarModal = document.getElementById('closeAvatarModal');

avatarImg?.addEventListener('click', () => {
    avatarModal.style.display = 'flex';
    modalAvatarImg.src = avatarImg.src;
});

closeAvatarModal?.addEventListener('click', () => {
    avatarModal.style.display = 'none';
});

avatarModal?.addEventListener('click', (e) => {
    if (e.target === avatarModal) {
        avatarModal.style.display = 'none';
    }
});

// ESC key to close modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && avatarModal.style.display === 'flex') {
        avatarModal.style.display = 'none';
    }
});
</script>

</body>
</html>