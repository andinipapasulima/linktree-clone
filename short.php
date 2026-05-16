<?php
include 'koneksi.php';

$short_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($short_code)) {
    header("Location: index.php");
    exit;
}

$query = mysqli_query($conn, "SELECT l.*, s.short_code FROM short_urls s JOIN links l ON s.link_id = l.id WHERE s.short_code = '$short_code' AND l.aktif = 1");
$link = mysqli_fetch_assoc($query);

if ($link) {
    // Update klik
    mysqli_query($conn, "UPDATE links SET jumlah_klik = jumlah_klik + 1 WHERE id = " . $link['id']);
    header("Location: " . $link['url']);
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>