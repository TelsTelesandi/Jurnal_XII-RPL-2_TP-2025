<?php
// ...existing code...

session_start();

// kosongkan semua variabel session
$_SESSION = [];

// hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// hancurkan session di server
session_destroy();

// redirect ke halaman login
header('Location: login.php');
exit();
?>