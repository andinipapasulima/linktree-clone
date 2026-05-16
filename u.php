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

$stmt_links = mysqli_prepare($conn, "SELECT * FROM links WHERE user_id = ? AND aktif = 1 ORDER BY urutan ASC");
mysqli_stmt_bind_param($stmt_links, "i", $user['id']);
mysqli_stmt_execute($stmt_links);
$links = mysqli_stmt_get_result($stmt_links);

$foto_path = "uploads/" . $user['foto'];
if (!file_exists($foto_path) || empty($user['foto'])) {
    $foto_path = "uploads/default.png";
}

$warna_tema = !empty($user['warna_tema']) ? $user['warna_tema'] : '#6366f1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['nama']) ?> | Linktree Premium</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ==========================================================================
           ULTRA PREMIUM NEO-BRUTALISME INTERACTIVE (MEMUKAU VIBES)
           ========================================================================== */
        body.public-page {
            background-color: #fbc7d4; 
            background-image: radial-gradient(#1a1a1a 1.5px, transparent 1.5px);
            background-size: 24px 24px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 7rem 1rem 4rem 1rem;
            position: relative;
            overflow-x: hidden;
            perspective: 1000px;
            transition: background-color 0.3s ease;
        }

        /* ==========================================================================
           FITUR 1: SETELAN TEMA GELAP (CYBERPUNK MATRIX)
           ========================================================================== */
        body.dark-theme {
            background-color: #121212;
            background-image: radial-gradient(#00ff66 1.5px, transparent 1.5px);
        }

        body.dark-theme .profile-section,
        body.dark-theme .btn-public-link,
        body.dark-theme .search-input,
        body.dark-theme .modal-content {
            background: #1e1e1e;
            color: #ffffff;
            border-color: #00ff66;
            box-shadow: 10px 10px 0px #00ff66;
        }

        body.dark-theme .profile-name,
        body.dark-theme .profile-bio,
        body.dark-theme .btn-public-link {
            color: #ffffff;
        }

        body.dark-theme .btn-public-link:hover {
            background-color: #00ff66;
            color: #121212;
            box-shadow: 13px 13px 0px #ffffff;
        }

        body.dark-theme .profile-badge {
            background: #00ff66;
            color: #121212;
            box-shadow: 3px 3px 0px #ffffff;
        }

        body.dark-theme .profile-avatar {
            border-color: #00ff66;
            box-shadow: 5px 5px 0px #ffffff;
            background: #1e1e1e;
        }

        body.dark-theme .search-input::placeholder {
            color: #666;
        }

        /* SAKLAR TEMA GAYA RETRO GAME */
        .theme-toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            background: #ffde4d;
            border: 3px solid #1a1a1a;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 4px 4px 0px #1a1a1a;
            font-size: 14px;
            transition: all 0.1s ease;
        }
        .theme-toggle-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px #1a1a1a;
        }
        .theme-toggle-btn:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px #1a1a1a;
        }

        body.dark-theme .theme-toggle-btn {
            background: #b460f3;
            color: #fff;
            border-color: #00ff66;
            box-shadow: 4px 4px 0px #00ff66;
        }

        /* FITUR 3: SEARCH BAR RETRO */
        .search-container {
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .search-input {
            width: 100%;
            padding: 14px 20px;
            font-size: 15px;
            font-weight: 700;
            border: 3px solid #1a1a1a;
            border-radius: 14px;
            box-shadow: 5px 5px 0px #1a1a1a;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .search-input:focus {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0px var(--focus-color, #ff76ce);
        }

        body.dark-theme .search-input:focus {
            box-shadow: 7px 7px 0px #ffffff;
        }

        /* Elemen Dekoratif Melayang */
        .shape {
            position: absolute;
            border: 3px solid #1a1a1a;
            box-shadow: 4px 4px 0px #1a1a1a;
            z-index: 0;
            pointer-events: none;
        }
        body.dark-theme .shape { border-color: #00ff66; box-shadow: 4px 4px 0px #ffffff; }
        
        .shape-1 { width: 50px; height: 50px; background: #94ffd8; top: 12%; left: 8%; border-radius: 12px; animation: floatX 7s ease-in-out infinite; }
        .shape-2 { width: 65px; height: 65px; background: #ff76ce; bottom: 15%; right: 7%; border-radius: 50%; animation: floatY 6s ease-in-out infinite; }
        .shape-3 { width: 45px; height: 45px; background: #ffde4d; top: 35%; right: 10%; transform: rotate(45deg); animation: floatX 8s ease-in-out infinite 1s; }
        .shape-4 { width: 35px; height: 35px; background: #b460f3; bottom: 40%; left: 12%; border-radius: 8px; animation: floatY 5s ease-in-out infinite 2s; }

        @keyframes floatX {
            0%, 100% { transform: translateX(0px) translateY(0px) rotate(0deg); }
            50% { transform: translateX(15px) translateY(-25px) rotate(15deg); }
        }
        @keyframes floatY {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-30px) scale(1.08) rotate(-10deg); }
        }

        .public-container {
            width: 100%;
            max-width: 430px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
            animation: pageReveal 0.6s cubic-bezier(0.23, 1, 0.32, 1) both;
        }

        @keyframes pageReveal {
            0% { opacity: 0; transform: translateY(40px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .profile-section {
            background: #ffffff;
            padding: 35px 24px;
            border-radius: 28px;
            border: 4px solid #1a1a1a;
            box-shadow: 10px 10px 0px #1a1a1a;
            width: 100%;
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
            transition: transform 0.1s ease, box-shadow 0.1s ease, background-color 0.3s, border-color 0.3s;
        }

        .shimmer-wrapper {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            border-radius: 24px;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }

        .shimmer-wrapper::before {
            content: '';
            position: absolute;
            top: var(--mouse-y, -50%);
            left: var(--mouse-x, -50%);
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,118,206,0.15) 0%, transparent 70%);
            transform: translate(-50%, -50%);
        }

        .profile-badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a1a;
            color: #fff;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            z-index: 2;
            box-shadow: 3px 3px 0px #ff76ce;
            transition: all 0.3s;
        }

        .profile-avatar {
            width: 125px;
            height: 125px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #1a1a1a;
            box-shadow: 5px 5px 0px #1a1a1a;
            margin-bottom: 1.4rem;
            background: white;
            position: relative;
            z-index: 2;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s, border-color 0.3s;
            cursor: pointer;
        }

        .profile-avatar:hover { transform: scale(1.12) rotate(8deg); }
        .profile-name { font-size: 28px; font-weight: 900; color: #1a1a1a; text-transform: uppercase; letter-spacing: -0.5px; margin-bottom: 0.5rem; position: relative; z-index: 2; }
        .profile-bio { font-size: 14.5px; font-weight: 600; color: #4a4a4a; line-height: 1.6; position: relative; z-index: 2; }

        .public-links {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .btn-public-link {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            color: #1a1a1a;
            padding: 20px 24px;
            border-radius: 18px;
            text-decoration: none;
            font-weight: 800;
            font-size: 16px;
            border: 3px solid #1a1a1a;
            box-shadow: 6px 6px 0px #1a1a1a;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s ease, background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        /* Animasi mengecil saat link di-filter/disembunyikan */
        .btn-public-link.hidden-link {
            display: none;
        }

        .btn-public-link::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: skewX(-20deg);
        }

        .btn-public-link:hover::after {
            left: 200%;
            transition: all 0.6s ease;
        }

        .btn-public-link:hover {
            background-color: <?= $warna_tema ?>;
            color: #ffffff; 
            transform: translate(-5px, -5px);
            box-shadow: 11px 11px 0px #1a1a1a;
        }

        .btn-public-link:active {
            transform: translate(5px, 5px);
            box-shadow: 0px 0px 0px #1a1a1a;
        }

        .muted {
            color: #1a1a1a;
            background: white;
            padding: 10px 20px;
            border: 3px solid #1a1a1a;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 4px 4px 0px #1a1a1a;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }
        body.dark-theme .muted { background: #1e1e1e; color: #fff; border-color: #00ff66; box-shadow: 4px 4px 0px #00ff66; }

        /* MODAL POP-UP STYLE */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999; opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active { opacity: 1; pointer-events: auto; }

        .modal-content {
            position: relative; background: #ffffff; padding: 16px; border-radius: 24px;
            border: 4px solid #1a1a1a; box-shadow: 12px 12px 0px #1a1a1a;
            max-width: 90%; max-height: 80vh;
            transform: scale(0.8) rotate(-3deg);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.25), background-color 0.3s, border-color 0.3s;
        }

        .modal-overlay.active .modal-content { transform: scale(1) rotate(0deg); }
        .modal-img { max-width: 100%; max-height: 70vh; border-radius: 14px; border: 3px solid #1a1a1a; display: block; object-fit: cover; }
        body.dark-theme .modal-img { border-color: #00ff66; }

        .btn-close-modal {
            position: absolute; top: -20px; right: -20px; width: 40px; height: 40px;
            background: #ff6b6b; color: #ffffff; border: 3px solid #1a1a1a; border-radius: 50%;
            font-size: 24px; font-weight: bold; cursor: pointer; box-shadow: 3px 3px 0px #1a1a1a;
            display: flex; align-items: center; justify-content: center; z-index: 10;
        }
        body.dark-theme .btn-close-modal { border-color: #00ff66; box-shadow: 3px 3px 0px #ffffff; }
    </style>
</head>
<body class="public-page">

<button class="theme-toggle-btn" id="themeToggle">🕹️ MODE: POP</button>

<div class="shape shape-1"></div>
<div class="shape shape-2"></div>
<div class="shape shape-3"></div>
<div class="shape shape-4"></div>

<?php if (isset($_SESSION['login'])): ?>
    <div style="position: fixed; top: 20px; left: 20px; z-index: 999;">
        <a href="dashboard.php" class="btn btn-outline" style="padding: 10px 18px; font-size: 13px; background:#ffffff; border: 3px solid #1a1a1a; box-shadow: 3px 3px 0px #1a1a1a;">
            ← BACK TO DASHBOARD
        </a>
    </div>
<?php endif; ?>

<div class="public-container">
    <div class="profile-section" id="profileCard">
        <div class="shimmer-wrapper"></div>
        <span class="profile-badge">🔥 OFFICIAL LINK</span>
        <img src="<?= $foto_path ?>" alt="Foto Profil" class="profile-avatar">
        <h1 class="profile-name"><?= htmlspecialchars($user['nama']) ?></h1>
        <?php if (!empty($user['bio'])): ?>
            <p class="profile-bio"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="search-container">
        <input type="text" id="searchInput" class="search-input" placeholder="🔍 Cari tautan penting di sini...">
    </div>

    <div class="public-links" id="linksContainer">
        <?php if (mysqli_num_rows($links) === 0): ?>
            <div class="muted" id="noLinksMsg">Belum ada link yang dibagikan.</div>
        <?php else: ?>
            <?php while ($link = mysqli_fetch_assoc($links)): ?>
                <a href="klik.php?id=<?= $link['id'] ?>" target="_blank" rel="noopener noreferrer" class="btn-public-link">
                    <?= htmlspecialchars($link['judul']) ?>
                </a>
            <?php endwhile; ?>
            <div class="muted" id="notFoundMsg" style="display: none;">Tautan yang kamu cari tidak ditemukan.</div>
        <?php endif; ?>
    </div>
</div>

<div id="avatarModal" class="modal-overlay">
    <div class="modal-content">
        <button id="closeModal" class="btn-close-modal">×</button>
        <img src="<?= $foto_path ?>" alt="Foto Profil Full" class="modal-img">
    </div>
</div>

<script>
    // ==========================================================================
    // LOGIKA FITUR 1: THEME SWITCHER (POP VS MATRIX)
    // ==========================================================================
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-theme');
        if(body.classList.contains('dark-theme')) {
            themeToggle.innerText = "📟 MODE: MATRIX";
        } else {
            themeToggle.innerText = "🕹️ MODE: POP";
        }
    });

    // ==========================================================================
    // LOGIKA FITUR 3: DYNAMIC LIVE SEARCH BAR 
    // ==========================================================================
    const searchInput = document.getElementById('searchInput');
    const linkButtons = document.querySelectorAll('.btn-public-link');
    const notFoundMsg = document.getElementById('notFoundMsg');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const keyword = e.target.value.toLowerCase().trim();
            let matchesFound = 0;

            linkButtons.forEach(link => {
                const judulLink = link.innerText.toLowerCase();
                
                if (judulLink.includes(keyword)) {
                    link.classList.remove('hidden-link');
                    matchesFound++;
                } else {
                    link.classList.add('hidden-link');
                }
            });

            // Tampilkan pesan jika hasil ketikan tidak ada yang cocok
            if (matchesFound === 0 && keyword !== '') {
                if(notFoundMsg) notFoundMsg.style.display = 'block';
            } else {
                if(notFoundMsg) notFoundMsg.style.display = 'none';
            }
        });
    }

    // ==========================================================================
    // LOGIKA INTERAKSI MOUSE (GLOW & 3D TILTING)
    // ==========================================================================
    const card = document.getElementById('profileCard');

    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const wrapper = card.querySelector('.shimmer-wrapper');
        wrapper.style.setProperty('--mouse-x', `${x}px`);
        wrapper.style.setProperty('--mouse-y', `${y}px`);
        
        const force = 4;
        const rotateX = ((y / rect.height) - 0.5) * -force;
        const rotateY = ((x / rect.width) - 0.5) * force;
        
        card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
    });

    // ==========================================================================
    // LOGIKA POP-UP MODAL AVATAR
    // ==========================================================================
    const avatarImg = document.querySelector('.profile-avatar');
    const modal = document.getElementById('avatarModal');
    const closeBtn = document.getElementById('closeModal');

    avatarImg.addEventListener('click', () => {
        modal.classList.add('active');
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
</script>

</body>
</html>