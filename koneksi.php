<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_linktree";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Konfigurasi website
define('SITE_NAME', 'Neo-Linktree');
define('SITE_URL', 'http://localhost/'); // Sesuaikan dengan URL website Anda
?>