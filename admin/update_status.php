<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $admin_id = $_SESSION['user_id'];
    
    // Update application status
    $stmt = $conn->prepare("UPDATE applications SET status = ?, processed_by = ?, processed_at = NOW() WHERE id = ?");
    $stmt->bind_param('sii', $status, $admin_id, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Status lamaran berhasil diupdate";
    } else {
        $_SESSION['error'] = "Gagal mengupdate status lamaran";
    }
}

header('Location: applications.php');
exit();