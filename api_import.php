<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['id_user'];

if ($_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal upload file']);
    exit;
}

$content = file_get_contents($_FILES['import_file']['tmp_name']);
$data = json_decode($content, true);

if (!$data || !isset($data['links'])) {
    echo json_encode(['status' => 'error', 'message' => 'Format file tidak valid']);
    exit;
}

$success = 0;
$failed = 0;

foreach ($data['links'] as $link) {
    $judul = mysqli_real_escape_string($conn, $link['judul']);
    $url = mysqli_real_escape_string($conn, $link['url']);
    $icon = mysqli_real_escape_string($conn, $link['icon'] ?? 'fas fa-link');
    
    $query = "INSERT INTO links (user_id, judul, url, icon, urutan, aktif) VALUES ('$user_id', '$judul', '$url', '$icon', 999, 1)";
    if (mysqli_query($conn, $query)) {
        $success++;
    } else {
        $failed++;
    }
}

echo json_encode(['status' => 'success', 'message' => "Import selesai: $success berhasil, $failed gagal"]);
?>