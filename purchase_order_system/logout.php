<?php
// Mulai session dengan aman
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_start();
}

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Hapus cookie remember_token jika ada
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}

// Bersihkan dan hancurkan session
session_unset();
session_destroy();

// Hindari output sebelum header
if (ob_get_length()) { ob_end_clean(); }

// Header no-cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect ke login dengan pesan sukses
$message = urlencode('Anda telah berhasil logout.');
header('Location: login.php?logout=success&message=' . $message);
exit;
