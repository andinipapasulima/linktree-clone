<?php
include 'koneksi.php';

$short_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($short_code)) {
    header("Location: 404.php");
    exit;
}

// FIX: prepared statement, ganti raw SQL injection
$stmt = mysqli_prepare($conn, "
    SELECT l.id, l.url, l.aktif
    FROM short_urls s
    JOIN links l ON s.link_id = l.id
    WHERE s.short_code = ? AND l.aktif = 1
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "s", $short_code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$link   = mysqli_fetch_assoc($result);

if ($link) {
    // Update klik
    $upd = mysqli_prepare($conn, "UPDATE links SET jumlah_klik = jumlah_klik + 1 WHERE id = ?");
    mysqli_stmt_bind_param($upd, "i", $link['id']);
    mysqli_stmt_execute($upd);

    header("Location: " . $link['url']);
    exit;
}

header("Location: 404.php");
exit;