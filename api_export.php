<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id_user'];

$links_query = mysqli_query($conn, "SELECT id, judul, url, icon, urutan, aktif, jumlah_klik FROM links WHERE user_id = '$user_id' ORDER BY urutan ASC");

$data = [
    'export_date' => date('Y-m-d H:i:s'),
    'user' => [
        'id' => $user_id,
        'username' => $_SESSION['username'],
        'nama' => $_SESSION['nama']
    ],
    'links' => []
];

while ($link = mysqli_fetch_assoc($links_query)) {
    $data['links'][] = $link;
}

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="links_export_' . date('Y-m-d') . '.json"');
echo json_encode($data, JSON_PRETTY_PRINT);
?>