<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

if ($id === 0 || !in_array($aksi, ['naik', 'turun'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Parameter']); exit;
}

// Ambil data link saat ini
$query = mysqli_query($conn, "SELECT * FROM links WHERE id = $id");
$current_link = mysqli_fetch_assoc($query);

if (!$current_link) {
    echo json_encode(['status' => 'error', 'message' => 'Data Not Found']); exit;
}

$user_id = $current_link['user_id'];
$urutan_sekarang = $current_link['urutan'];

// Mencari link pendamping untuk ditukar posisinya
if ($aksi === 'naik') {
    $operator = '<'; $order = 'DESC';
} else {
    $operator = '>'; $order = 'ASC';
}

$swap_query = mysqli_query($conn, "SELECT * FROM links WHERE user_id = $user_id AND urutan $operator $urutan_sekarang ORDER BY urutan $order LIMIT 1");
$target_swap = mysqli_fetch_assoc($swap_query);

if ($target_swap) {
    $target_id = $target_swap['id'];
    $urutan_baru = $target_swap['urutan'];

    // Tukar nilai kolom urutan di database
    mysqli_query($conn, "UPDATE links SET urutan = $urutan_baru WHERE id = $id");
    mysqli_query($conn, "UPDATE links SET urutan = $urutan_sekarang WHERE id = $target_id");

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bound reached']);
}