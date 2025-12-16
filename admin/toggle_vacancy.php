<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    $_SESSION['error'] = 'Database connection error.';
    header('Location: vacancies.php');
    exit();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = (int)$_GET['status'];
    
    $stmt = $conn->prepare("UPDATE job_postings SET is_visible = ? WHERE id = ?");
    $stmt->bind_param('ii', $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Status lowongan berhasil diupdate";
    } else {
        $_SESSION['error'] = "Gagal mengupdate status lowongan";
    }
}

header('Location: vacancies.php');
exit();
?>