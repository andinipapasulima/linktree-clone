<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); 
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$user_id = $_SESSION['id_user'] ?? 0;

if ($id === 0 || !in_array($aksi, ['naik', 'turun'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Parameter']); 
    exit;
}

// Gunakan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM links WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$current_link = mysqli_fetch_assoc($result);

if (!$current_link) {
    echo json_encode(['status' => 'error', 'message' => 'Data Not Found']); 
    exit;
}

$urutan_sekarang = $current_link['urutan'];

// Cari link untuk ditukar
if ($aksi === 'naik') {
    $operator = '<'; 
    $order = 'DESC';
} else {
    $operator = '>'; 
    $order = 'ASC';
}

$swap_query = mysqli_prepare($conn, "SELECT * FROM links WHERE user_id = ? AND urutan $operator ? ORDER BY urutan $order LIMIT 1");
mysqli_stmt_bind_param($swap_query, "ii", $user_id, $urutan_sekarang);
mysqli_stmt_execute($swap_query);
$swap_result = mysqli_stmt_get_result($swap_query);
$target_swap = mysqli_fetch_assoc($swap_result);

if ($target_swap) {
    $target_id = $target_swap['id'];
    $urutan_baru = $target_swap['urutan'];

    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        $stmt1 = mysqli_prepare($conn, "UPDATE links SET urutan = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt1, "iii", $urutan_baru, $id, $user_id);
        mysqli_stmt_execute($stmt1);
        
        $stmt2 = mysqli_prepare($conn, "UPDATE links SET urutan = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt2, "iii", $urutan_sekarang, $target_id, $user_id);
        mysqli_stmt_execute($stmt2);
        
        mysqli_commit($conn);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate urutan']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bound reached']);
}
?>