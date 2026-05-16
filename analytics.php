<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id_user'] ?? 0;

if ($user_id === 0) {
    header("Location: logout.php");
    exit;
}

// Ambil data user
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query_user);

// Ambil semua link dengan statistik
$links = mysqli_query($conn, "SELECT * FROM links WHERE user_id = '$user_id' ORDER BY jumlah_klik DESC");

// Total statistik
$total_links = mysqli_num_rows($links);
$total_clicks = 0;
$top_link = null;
$top_clicks = 0;

while ($link = mysqli_fetch_assoc($links)) {
    $total_clicks += $link['jumlah_klik'];
    if ($link['jumlah_klik'] > $top_clicks) {
        $top_clicks = $link['jumlah_klik'];
        $top_link = $link;
    }
}

// Reset pointer
mysqli_data_seek($links, 0);

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
    <title>Analytics | Neo-Linktree</title>
    <style>
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

        .analytics-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: #ffffff;
            padding: 24px;
            border-radius: 20px;
            border: 4px solid #1a1a1a;
            box-shadow: 8px 8px 0px #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 10px 16px;
            background: #94ffd8;
            border: 3px solid #1a1a1a;
            border-radius: 10px;
            text-decoration: none;
            color: #1a1a1a;
            font-weight: 800;
            box-shadow: 3px 3px 0px #1a1a1a;
            transition: all 0.1s ease;
        }

        .back-link:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #1a1a1a;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 4px solid #1a1a1a;
            border-radius: 18px;
            padding: 20px;
            text-align: center;
            box-shadow: 6px 6px 0px #1a1a1a;
        }

        .stat-card h3 {
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 10px;
            opacity: 0.7;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 900;
        }

        .main-card {
            background: white;
            border: 4px solid #1a1a1a;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 8px 8px 0px #1a1a1a;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #1a1a1a;
        }

        .table th {
            background: #b460f3;
            color: white;
            font-weight: 800;
        }

        .rank-1 { background: #ffde4d; }
        .rank-2 { background: #e0e0e0; }
        .rank-3 { background: #cd7f32; color: white; }

        .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 8px;
            width: 100px;
        }

        .progress-fill {
            background: #b460f3;
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>

<div class="analytics-container">
    <div class="header">
        <div>
            <h2>📊 Analytics Dashboard</h2>
            <p>@<?= htmlspecialchars($user['username'] ?? '') ?></p>
        </div>
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Tautan</h3>
            <div class="stat-number"><?= $total_links ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Klik</h3>
            <div class="stat-number"><?= number_format($total_clicks, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <h3>Rata-rata Klik/Link</h3>
            <div class="stat-number"><?= $total_links > 0 ? number_format($total_clicks / $total_links, 0, ',', '.') : 0 ?></div>
        </div>
    </div>

    <div class="main-card">
        <h3 style="margin-bottom: 1.5rem;">🏆 Peringkat Tautan Terpopuler</h3>
        
        <?php if ($total_links === 0): ?>
            <div class="empty-state" style="text-align: center; padding: 40px;">
                Belum ada data tautan.
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Peringkat</th><th>Judul</th><th>Klik</th><th>Persentase</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    $max_clicks = $top_clicks > 0 ? $top_clicks : 1;
                    while ($link = mysqli_fetch_assoc($links)): 
                        $percentage = ($link['jumlah_klik'] / $max_clicks) * 100;
                        $rowClass = '';
                        if ($rank == 1) $rowClass = 'rank-1';
                        elseif ($rank == 2) $rowClass = 'rank-2';
                        elseif ($rank == 3) $rowClass = 'rank-3';
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td style="font-weight: 800;">#<?= $rank++ ?></td>
                            <td><?= htmlspecialchars($link['judul']) ?></td>
                            <td><?= number_format($link['jumlah_klik'], 0, ',', '.') ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <small><?= round($percentage, 1) ?>%</small>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>