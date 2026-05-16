<?php
include 'koneksi.php';
header('Content-Type: application/json');

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (strlen($username) < 3) {
    echo json_encode(['available' => false, 'message' => 'Username terlalu pendek']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    echo json_encode(['available' => false, 'message' => 'Username sudah dipakai']);
} else {
    echo json_encode(['available' => true, 'message' => 'Username tersedia']);
}
?>