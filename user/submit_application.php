<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/PdfGenerator.php';

// Pastikan login role user
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: ../login.php');
    exit();
}

// Add this check before processing the application
$check_stmt = $conn->prepare("SELECT id FROM applications WHERE user_id = ?");
$check_stmt->bind_param('i', $_SESSION['user_id']);
$check_stmt->execute();

if ($check_stmt->get_result()->fetch_assoc()) {
    $_SESSION['error'] = "Anda sudah melamar pekerjaan lain. Tidak dapat melamar lebih dari satu lowongan.";
    header('Location: vacancies.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $job_id = (int)$_POST['job_id'];
    $cover_letter = trim($_POST['cover_letter']);

    // Get user and job data
    $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    $job = $conn->query("SELECT * FROM job_postings WHERE id = $job_id")->fetch_assoc();

    // Generate PDF
    $pdf = generateApplicationPDF($user, $job, $cover_letter);
    
    // Create upload directories if not exists
    $uploadDir = __DIR__ . '/../uploads/applications/';
    $photoDir = __DIR__ . '/../uploads/photos/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    if (!is_dir($photoDir)) mkdir($photoDir, 0777, true);

    // Handle photo upload
    $photo_path = null;
    if (!empty($_FILES['photo']['name'])) {
        $photo_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png'];
        
        if (in_array($photo_ext, $allowed_types)) {
            $photo_name = "photo_{$user_id}_{$job_id}_" . date('Ymd_His') . '.' . $photo_ext;
            $photo_dest = $photoDir . $photo_name;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_dest)) {
                $photo_path = 'photos/' . $photo_name;
            }
        }
    }

    // Generate filename and save PDF
    $filename = "application_{$user_id}_{$job_id}_" . date('Ymd_His') . '.pdf';
    $filepath = $uploadDir . $filename;
    $pdf->Output('F', $filepath);

    // Store in database with photo
    $sql = "INSERT INTO applications (user_id, job_id, cover_letter, pdf_path, photo, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iisss', $user_id, $job_id, $cover_letter, $filename, $photo_path);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Lamaran berhasil dikirim! Kami akan meninjau lamaran Anda segera.";
        echo "<script>
            alert('Lamaran berhasil dikirim!');
            window.location.href = 'applications.php';
        </script>";
        exit();
    } else {
        $_SESSION['error'] = "Gagal mengirim lamaran. Silakan coba lagi.";
        header('Location: vacancies.php');
        exit();
    }
}

$stmt->close();
$conn->close();
exit();