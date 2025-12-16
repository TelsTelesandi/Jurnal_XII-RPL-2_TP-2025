<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete related applications first
        $stmt = $conn->prepare("DELETE FROM applications WHERE job_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Then delete the vacancy
        $stmt = $conn->prepare("DELETE FROM job_postings WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Commit if all successful
        $conn->commit();
        $_SESSION['success'] = "Lowongan berhasil dihapus";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Gagal menghapus lowongan";
    }
}

header('Location: vacancies.php');
exit();