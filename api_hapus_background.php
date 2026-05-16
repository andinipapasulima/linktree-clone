<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['id_user'];

// Ambil nama file background saat ini
$query = mysqli_query($conn, "SELECT background_image FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

if ($user['background_image']) {
    // Hapus file fisik
    $file_path = "uploads/backgrounds/" . $user['background_image'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Update database hapus background
    $update = mysqli_query($conn, "UPDATE users SET background_image = NULL WHERE id = '$user_id'");
    
    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Background dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada background']);
}
?>