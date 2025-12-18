<?php
/**
 * File: database.php
 * Fungsi: Konfigurasi koneksi database MySQL
 * 
 * File ini berisi pengaturan untuk koneksi ke database.
 * Sesuaikan dengan pengaturan server MySQL Anda.
 */

// Pengaturan database
define('DB_HOST', 'localhost');      // Host database (biasanya localhost)
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL (kosong untuk default Laragon)
define('DB_NAME', 'rollmate');       // Nama database

// Fungsi untuk membuat koneksi database
function getDBConnection() {
    try {
        // Buat koneksi menggunakan PDO (PHP Data Objects)
        // PDO lebih aman dari mysqli karena mendukung prepared statements
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Tampilkan error jika ada
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Hasil query dalam bentuk array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                   // Gunakan prepared statement asli
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        // Jika koneksi gagal, tampilkan pesan error
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Fungsi untuk menutup koneksi database
function closeDBConnection(&$pdo) {
    $pdo = null;
}
?>
