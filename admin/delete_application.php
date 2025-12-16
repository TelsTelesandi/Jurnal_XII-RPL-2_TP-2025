<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// pastikan koneksi ada
if (!isset($conn) || $conn === null) {
    error_log('Database connection is missing in delete_application.php');
    $_SESSION['error'] = 'Database connection error.';
    header('Location: applications.php');
    exit();
}

// pastikan admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: applications.php');
    exit();
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = 'ID aplikasi tidak valid.';
    header('Location: applications.php');
    exit();
}

// cari nama kolom file yang ada pada tabel applications
$possibleCols = ['cv_file_path', 'pdf_path', 'cv_path', 'cv_file', 'file_path'];
$fileCol = null;
$colsRes = mysqli_query($conn, "SHOW COLUMNS FROM `applications`");
if ($colsRes) {
    while ($col = mysqli_fetch_assoc($colsRes)) {
        $fname = $col['Field'] ?? '';
        if (in_array($fname, $possibleCols, true)) {
            $fileCol = $fname;
            break;
        }
    }
    mysqli_free_result($colsRes);
}

// ambil path file CV (jika ada) lalu hapus file fisik
if ($fileCol !== null) {
    $select_stmt = mysqli_prepare($conn, "SELECT `$fileCol` FROM `applications` WHERE id = ? LIMIT 1");
    if ($select_stmt === false) {
        error_log('Prepare failed (SELECT): ' . mysqli_error($conn));
        $_SESSION['error'] = 'Query error.';
        header('Location: applications.php');
        exit();
    }
    mysqli_stmt_bind_param($select_stmt, 'i', $id);
    mysqli_stmt_execute($select_stmt);
    mysqli_stmt_bind_result($select_stmt, $file_path_value);
    $has_row = mysqli_stmt_fetch($select_stmt);
    mysqli_stmt_close($select_stmt);

    if ($has_row && !empty($file_path_value)) {
        $filePath = __DIR__ . '/../' . ltrim($file_path_value, '/\\');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
}

// hapus record aplikasi (boleh dihapus untuk status apa pun)
$delete_stmt = mysqli_prepare($conn, "DELETE FROM `applications` WHERE id = ?");
if ($delete_stmt === false) {
    error_log('Prepare failed (DELETE): ' . mysqli_error($conn));
    $_SESSION['error'] = 'Query error.';
    header('Location: applications.php');
    exit();
}
mysqli_stmt_bind_param($delete_stmt, 'i', $id);
$exec_ok = mysqli_stmt_execute($delete_stmt);
$affected = mysqli_stmt_affected_rows($delete_stmt);
mysqli_stmt_close($delete_stmt);

if ($exec_ok && $affected > 0) {
    $_SESSION['success'] = 'Lamaran berhasil dihapus.';
} else {
    error_log('Delete failed: ' . mysqli_error($conn));
    $_SESSION['error'] = 'Gagal menghapus Lamaran.';
}

header('Location: applications.php');
exit();
?>