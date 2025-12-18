<?php
/**
 * File: logout.php
 * Fungsi: Menghapus session dan logout user
 */

require_once 'config/session.php';

// Hapus semua data session
destroyUserSession();

// Redirect ke halaman login
header('Location: login.php');
exit;
?>
