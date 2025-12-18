<?php
/**
 * File: index.php
 * Fungsi: Landing page / halaman utama aplikasi
 * Redirect otomatis ke login jika belum login, atau ke dashboard jika sudah login
 */

require_once 'config/session.php';

// Cek apakah user sudah login
if (isLoggedIn()) {
    // Jika sudah login, redirect ke dashboard
    header('Location: dashboard.php');
} else {
    // Jika belum login, redirect ke halaman login
    header('Location: login.php');
}
exit;
?>
