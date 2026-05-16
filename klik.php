<?php
include 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Ambil URL tujuan berdasarkan ID
    $stmt = mysqli_prepare($conn, "SELECT url FROM links WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $link = mysqli_fetch_assoc($result);

    if ($link) {
        // 2. Tambah jumlah klik (+1) di database
        $upd = mysqli_prepare($conn, "UPDATE links SET jumlah_klik = jumlah_klik + 1 WHERE id = ?");
        mysqli_stmt_bind_param($upd, "i", $id);
        mysqli_stmt_execute($upd);

        // 3. Alihkan halaman ke URL asli tujuan
        header("Location: " . $link['url']);
        exit;
    }
}

// Jika ID salah atau link tidak ada, lempar ke dashboard atau halaman utama
header("Location: dashboard.php");
exit;