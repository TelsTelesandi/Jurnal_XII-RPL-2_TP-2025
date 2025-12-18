<?php
// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "project"; // gunakan nama DB sesuai yang kamu buat

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set timezone untuk MySQL connection
mysqli_query($conn, "SET time_zone = '+07:00'");
?>
