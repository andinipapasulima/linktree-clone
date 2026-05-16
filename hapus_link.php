<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "DELETE FROM links WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $id_user);
mysqli_stmt_execute($stmt);

header("Location: dashboard.php");
exit;
?>