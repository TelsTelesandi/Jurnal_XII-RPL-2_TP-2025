<?php
// Simple entry point: redirect to login or dashboards if already logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If logged in, send to appropriate dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard_admin.php');
        exit;
    }
    if ($_SESSION['role'] === 'production') {
        header('Location: dashboard_production.php');
        exit;
    }
}

// Default: go to login page
header('Location: login.php');
exit;
?>