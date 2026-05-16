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
$stmt_user = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

// Ambil semua link dengan statistik (ordered by klik DESC)
$stmt_links = mysqli_prepare($conn, "SELECT * FROM links WHERE user_id = ? ORDER BY jumlah_klik DESC");
mysqli_stmt_bind_param($stmt_links, "i", $user_id);
mysqli_stmt_execute($stmt_links);
$links_result = mysqli_stmt_get_result($stmt_links);

$total_links = 0;
$total_clicks = 0;
$top_link = null;
$top_clicks = 0;
$links_data = [];

while ($link = mysqli_fetch_assoc($links_result)) {
    $links_data[] = $link;
    $total_clicks += $link['jumlah_klik'];
    $total_links++;
    if ($link['jumlah_klik'] > $top_clicks) {
        $top_clicks = $link['jumlah_klik'];
        $top_link = $link;
    }
}

// Data untuk chart klik per link (top 7)
$chart_labels = [];
$chart_data   = [];
$chart_colors = ['#b460f3','#ff76ce','#94ffd8','#ffde4d','#ff6b6b','#60d0f3','#f3a460'];

$display_links = array_slice($links_data, 0, 7);
foreach ($display_links as $l) {
    $chart_labels[] = mb_strimwidth($l['judul'], 0, 18, '…');
    $chart_data[]   = (int)$l['jumlah_klik'];
}

// Klik 7 hari terakhir (per hari) — dari tabel visitors
$stmt_daily = mysqli_prepare($conn, "
    SELECT DATE(visited_at) as tanggal, COUNT(*) as jumlah
    FROM visitors
    WHERE user_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(visited_at)
    ORDER BY tanggal ASC
");
mysqli_stmt_bind_param($stmt_daily, "i", $user_id);
mysqli_stmt_execute($stmt_daily);
$daily_result = mysqli_stmt_get_result($stmt_daily);

$daily_map = [];
while ($row = mysqli_fetch_assoc($daily_result)) {
    $daily_map[$row['tanggal']] = (int)$row['jumlah'];
}

// Isi 7 hari terakhir (termasuk hari tanpa data)
$daily_labels = [];
$daily_data   = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_labels[] = date('d/m', strtotime($date));
    $daily_data[]   = $daily_map[$date] ?? 0;
}

// Device breakdown
$stmt_device = mysqli_prepare($conn, "
    SELECT visitor_device, COUNT(*) as jumlah
    FROM visitors WHERE user_id = ?
    GROUP BY visitor_device
");
mysqli_stmt_bind_param($stmt_device, "i", $user_id);
mysqli_stmt_execute($stmt_device);
$device_result = mysqli_stmt_get_result($stmt_device);

$device_labels = [];
$device_data   = [];
while ($row = mysqli_fetch_assoc($device_result)) {
    $device_labels[] = $row['visitor_device'] ?: 'Unknown';
    $device_data[]   = (int)$row['jumlah'];
}

// Total visitor
$stmt_visitor = mysqli_prepare($conn, "SELECT COUNT(DISTINCT visitor_ip) as total FROM visitors WHERE user_id = ?");
mysqli_stmt_bind_param($stmt_visitor, "i", $user_id);
mysqli_stmt_execute($stmt_visitor);
$visitor_row  = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_visitor));
$total_visitor = $visitor_row['total'] ?? 0;

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background-color: #e3fafc;
            background-image: radial-gradient(#1a1a1a 1.2px, transparent 1.2px);
            background-size: 20px 20px;
            color: #1a1a1a;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .container { max-width: 960px; margin: 0 auto; }

        /* HEADER */
        .header {
            background: #ffffff;
            padding: 20px 24px;
            border-radius: 20px;
            border: 4px solid #1a1a1a;
            box-shadow: 8px 8px 0px #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        .header h2 { font-size: 20px; font-weight: 900; text-transform: uppercase; }
        .header p  { font-size: 12px; color: #666; font-weight: 600; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            background: #94ffd8;
            border: 3px solid #1a1a1a;
            border-radius: 10px;
            text-decoration: none;
            color: #1a1a1a;
            font-weight: 800;
            font-size: 12px;
            box-shadow: 3px 3px 0px #1a1a1a;
            transition: all 0.1s ease;
        }
        .back-link:hover { transform: translate(-2px,-2px); box-shadow: 5px 5px 0px #1a1a1a; }

        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border: 3px solid #1a1a1a;
            border-radius: 18px;
            padding: 18px 14px;
            text-align: center;
            box-shadow: 5px 5px 0px #1a1a1a;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover { transform: translate(-2px,-2px); box-shadow: 7px 7px 0px #1a1a1a; }

        .stat-card h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; opacity: 0.65; }
        .stat-number { font-size: 30px; font-weight: 900; }
        .stat-card .stat-icon { font-size: 18px; margin-bottom: 6px; }

        /* CHART GRID */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .chart-card {
            background: white;
            border: 3px solid #1a1a1a;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 6px 6px 0px #1a1a1a;
        }

        .chart-card.full-width {
            grid-column: 1 / -1;
        }

        .chart-title {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .chart-wrapper { position: relative; height: 220px; }
        .chart-wrapper.tall { height: 260px; }

        /* TABLE */
        .table-card {
            background: white;
            border: 3px solid #1a1a1a;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 6px 6px 0px #1a1a1a;
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; min-width: 480px; }
        thead tr { background: #b460f3; }
        th, td { padding: 11px 14px; text-align: left; }
        th { color: white; font-weight: 800; font-size: 11px; text-transform: uppercase; }
        td { font-size: 13px; border-bottom: 1.5px solid #f0f0f0; font-weight: 600; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #faf8ff; }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px; height: 26px;
            border-radius: 50%;
            font-weight: 900;
            font-size: 12px;
            border: 2px solid #1a1a1a;
        }
        .rank-1 { background: #ffde4d; }
        .rank-2 { background: #e0e0e0; }
        .rank-3 { background: #cd7f32; color: white; }
        .rank-other { background: #f0f0f0; }

        .progress-bar { background: #e8e8e8; border-radius: 20px; overflow: hidden; height: 7px; width: 90px; display: inline-block; vertical-align: middle; margin-right: 6px; }
        .progress-fill { background: #b460f3; height: 100%; border-radius: 20px; }

        .badge-active { background: #94ffd8; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 800; border: 1.5px solid #1a1a1a; }
        .badge-inactive { background: #ff6b6b; color: white; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 800; border: 1.5px solid #1a1a1a; }

        .empty-state { text-align: center; padding: 40px; opacity: 0.5; font-weight: 700; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
            .chart-card.full-width { grid-column: 1; }
        }

        @media (max-width: 480px) {
            .stat-number { font-size: 22px; }
            .header { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div>
            <h2>📊 Analytics Dashboard</h2>
            <p>@<?= htmlspecialchars($user['username'] ?? '') ?></p>
        </div>
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🔗</div>
            <h3>Total Tautan</h3>
            <div class="stat-number"><?= $total_links ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👆</div>
            <h3>Total Klik</h3>
            <div class="stat-number"><?= number_format($total_clicks, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <h3>Visitor Unik</h3>
            <div class="stat-number"><?= number_format($total_visitor, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <h3>Rata-rata Klik</h3>
            <div class="stat-number"><?= $total_links > 0 ? number_format($total_clicks / $total_links, 1, ',', '.') : 0 ?></div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="charts-grid">

        <!-- Grafik klik 7 hari terakhir -->
        <div class="chart-card full-width">
            <div class="chart-title"><i class="fas fa-chart-line" style="color:#b460f3"></i> Visitor 7 Hari Terakhir</div>
            <div class="chart-wrapper tall">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>

        <!-- Bar chart per link -->
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-bar" style="color:#ff76ce"></i> Klik per Tautan</div>
            <div class="chart-wrapper">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <!-- Doughnut device -->
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-mobile-alt" style="color:#94ffd8"></i> Perangkat Pengunjung</div>
            <div class="chart-wrapper">
                <canvas id="deviceChart"></canvas>
            </div>
        </div>

    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="chart-title" style="margin-bottom:16px"><i class="fas fa-trophy" style="color:#ffde4d"></i> Peringkat Tautan Terpopuler</div>

        <?php if ($total_links === 0): ?>
            <div class="empty-state">Belum ada data tautan.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Klik</th>
                        <th>Persentase</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    $max  = $top_clicks > 0 ? $top_clicks : 1;
                    foreach ($links_data as $link):
                        $pct = round(($link['jumlah_klik'] / $max) * 100, 1);
                        $badge_class = match(true) { $rank===1=>'rank-1', $rank===2=>'rank-2', $rank===3=>'rank-3', default=>'rank-other' };
                    ?>
                    <tr>
                        <td><span class="rank-badge <?= $badge_class ?>"><?= $rank++ ?></span></td>
                        <td>
                            <i class="<?= htmlspecialchars($link['icon'] ?? 'fas fa-link') ?>" style="margin-right:6px;opacity:.7"></i>
                            <?= htmlspecialchars($link['judul']) ?>
                        </td>
                        <td><strong><?= number_format($link['jumlah_klik'], 0, ',', '.') ?></strong></td>
                        <td>
                            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
                            <small><?= $pct ?>%</small>
                        </td>
                        <td>
                            <?php if ($link['aktif']): ?>
                                <span class="badge-active">✓ Aktif</span>
                            <?php else: ?>
                                <span class="badge-inactive">✗ Nonaktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<script>
// ─── Warna & font global Chart.js ────────────────────────────────────────────
Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.font.weight = '700';
Chart.defaults.color       = '#1a1a1a';

// ─── 1. Line chart — visitor harian ─────────────────────────────────────────
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($daily_labels) ?>,
        datasets: [{
            label: 'Visitor',
            data: <?= json_encode($daily_data) ?>,
            borderColor: '#b460f3',
            backgroundColor: 'rgba(180,96,243,0.12)',
            borderWidth: 3,
            pointBackgroundColor: '#b460f3',
            pointBorderColor: '#1a1a1a',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a1a1a',
                titleColor: '#ffde4d',
                bodyColor: '#fff',
                borderColor: '#1a1a1a',
                borderWidth: 2,
                padding: 10,
                callbacks: {
                    label: ctx => ` ${ctx.parsed.y} visitor`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: 'rgba(0,0,0,0.06)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// ─── 2. Bar chart — klik per link ────────────────────────────────────────────
const barLabels  = <?= json_encode($chart_labels) ?>;
const barData    = <?= json_encode($chart_data) ?>;
const barColors  = <?= json_encode($chart_colors) ?>;

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: barLabels,
        datasets: [{
            label: 'Klik',
            data: barData,
            backgroundColor: barColors.slice(0, barData.length),
            borderColor: '#1a1a1a',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a1a1a',
                titleColor: '#ffde4d',
                bodyColor: '#fff',
                padding: 10,
                callbacks: { label: ctx => ` ${ctx.parsed.y} klik` }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.06)' } },
            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
    }
});

// ─── 3. Doughnut — device ────────────────────────────────────────────────────
const deviceLabels = <?= json_encode($device_labels) ?>;
const deviceData   = <?= json_encode($device_data) ?>;

if (deviceData.length > 0) {
    new Chart(document.getElementById('deviceChart'), {
        type: 'doughnut',
        data: {
            labels: deviceLabels,
            datasets: [{
                data: deviceData,
                backgroundColor: ['#b460f3','#ff76ce','#ffde4d','#94ffd8','#ff6b6b'],
                borderColor: '#1a1a1a',
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, font: { size: 11 }, usePointStyle: true }
                },
                tooltip: {
                    backgroundColor: '#1a1a1a',
                    titleColor: '#ffde4d',
                    bodyColor: '#fff',
                    padding: 10,
                    callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} visitor` }
                }
            }
        }
    });
} else {
    document.getElementById('deviceChart').closest('.chart-card').querySelector('.chart-wrapper').innerHTML =
        '<div style="display:flex;align-items:center;justify-content:center;height:100%;opacity:.4;font-weight:700">Belum ada data visitor</div>';
}
</script>
</body>
</html>