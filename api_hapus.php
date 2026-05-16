<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']); exit;
}

// Eksekusi penghapusan di database
$delete = mysqli_query($conn, "DELETE FROM links WHERE id = $id");

if ($delete) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database failure']);
}