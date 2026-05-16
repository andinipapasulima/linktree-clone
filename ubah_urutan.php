<?php
session_start();
include 'koneksi.php';

// Pastikan yang akses sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$user_id = isset($_SESSION['id_user']) ? intval($_SESSION['id_user']) : 0;

if ($id <= 0 || !in_array($aksi, ['naik', 'turun'])) {
    header("Location: dashboard.php");
    exit;
}

// 1. Ambil data link yang mau diubah beserta nilai urutannya saat ini
$query = mysqli_query($conn, "SELECT * FROM links WHERE id = $id AND user_id = $user_id");
$link_sekarang = mysqli_fetch_assoc($query);

if (!$link_sekarang) {
    header("Location: dashboard.php");
    exit;
}

$urutan_sekarang = $link_sekarang['urutan'];

// 2. Cari link "lawan" yang mau ditukar urutannya
if ($aksi === 'naik') {
    // Kalau naik, berarti mencari urutan yang lebih kecil (<) dari urutan sekarang (diambil yang paling dekat/terbesar)
    $query_lawan = mysqli_query($conn, "SELECT * FROM links WHERE user_id = $user_id AND urutan < $urutan_sekarang ORDER BY urutan DESC LIMIT 1");
} else {
    // Kalau turun, berarti mencari urutan yang lebih besar (>) dari urutan sekarang (diambil yang paling dekat/terkecil)
    $query_lawan = mysqli_query($conn, "SELECT * FROM links WHERE user_id = $user_id AND urutan > $urutan_sekarang ORDER BY urutan ASC LIMIT 1");
}

$link_lawan = mysqli_fetch_assoc($query_lawan);

// 3. Jika lawan ditemukan, lakukan eksekusi pertukaran nilai urutan
if ($link_lawan) {
    $id_lawan = $link_lawan['id'];
    $urutan_lawan = $link_lawan['urutan'];

    // Tukar urutan di database
    mysqli_query($conn, "UPDATE links SET urutan = $urutan_lawan WHERE id = $id");
    mysqli_query($conn, "UPDATE links SET urutan = $urutan_sekarang WHERE id = $id_lawan");
}

// Kembalikan ke halaman dashboard
header("Location: dashboard.php");
exit;