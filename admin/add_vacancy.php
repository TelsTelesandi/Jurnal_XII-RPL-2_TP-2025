<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Pastikan admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $max_applicants = max(0, (int)($_POST['max_applicants'] ?? 0));
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;

    // Validasi input
    if (empty($title) || empty($description) || empty($requirements)) {
        $_SESSION['error'] = 'Semua field wajib diisi.';
        header('Location: add_vacancy_form.php');
        exit();
    }

    // Simpan lowongan
    $stmt = $conn->prepare("INSERT INTO job_postings (title, description, requirements, is_visible, max_applicants, created_at) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    
    if ($stmt) {
        $stmt->bind_param('sssii', $title, $description, $requirements, $is_visible, $max_applicants);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = '✅ Lowongan berhasil ditambahkan!';
            header('Location: vacancies.php');
        } else {
            $_SESSION['error'] = '❌ Gagal menyimpan lowongan.';
            header('Location: add_vacancy_form.php');
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Error preparing statement: ' . $conn->error;
        header('Location: add_vacancy_form.php');
    }
    exit();
}

header('Location: vacancies.php');
exit();
?>