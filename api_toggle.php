<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); 
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status = isset($_GET['status']) ? intval($_GET['status']) : 0;
$user_id = $_SESSION['id_user'] ?? 0;

if ($id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']); 
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE links SET aktif = ? WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "iii", $status, $id, $user_id);
$update = mysqli_stmt_execute($stmt);

if ($update) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status']);
}
?>