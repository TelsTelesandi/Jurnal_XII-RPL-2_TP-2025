<?php
date_default_timezone_set('Asia/Jakarta');
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_keuangan';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>