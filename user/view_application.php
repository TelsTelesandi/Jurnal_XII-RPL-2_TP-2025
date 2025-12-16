<?php
session_start();
require_once '../config/database.php';

// Cek login user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

// Handle PDF Viewer
if (isset($_GET['pdf'])) {
    $app_id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];
    
    // Pastikan user hanya bisa melihat PDF lamarannya sendiri
    $stmt = $conn->prepare("SELECT pdf_path FROM applications WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $app_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $pdf_path = __DIR__ . '/../uploads/applications/' . basename($row['pdf_path']);
        if (file_exists($pdf_path)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="application.pdf"');
            readfile($pdf_path);
            exit;
        }
    }
    die('PDF tidak ditemukan.');
}

// Handle Upload Surat Sehat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['health_certificate'])) {
    $id = (int)$_POST['app_id'];
    $user_id = (int)$_SESSION['user_id'];
    
    // Verifikasi lamaran milik user dan status accepted
    $stmt = $conn->prepare("SELECT id, interview_result FROM applications WHERE id = ? AND user_id = ? AND interview_result = 'accepted'");
    $stmt->bind_param('ii', $id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if (!$res->fetch_assoc()) {
        $_SESSION['error'] = 'Lamaran tidak valid atau hasil interview belum diterima.';
        header('Location: view_application.php?id=' . $id);
        exit();
    }
    
    // Handle file upload
    $file = $_FILES['health_certificate'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = 'Tipe file tidak didukung. Hanya JPG, PNG, GIF, atau PDF.';
        header('Location: view_application.php?id=' . $id);
        exit();
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
        header('Location: view_application.php?id=' . $id);
        exit();
    }
    
    // Buat folder jika belum ada
    $upload_dir = __DIR__ . '/../uploads/health_certificates/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate nama file unik
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = 'health_cert_' . $id . '_' . time() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Simpan ke database
        $health_cert_path = 'health_certificates/' . $file_name;
        $stmt = $conn->prepare("UPDATE applications SET health_certificate = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param('sii', $health_cert_path, $id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Surat sehat berhasil diupload!';
        } else {
            $_SESSION['error'] = 'Gagal menyimpan data ke database.';
        }
    } else {
        $_SESSION['error'] = 'Gagal mengupload file.';
    }
    
    header('Location: view_application.php?id=' . $id);
    exit();
}

// Ambil ID lamaran
$id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($id <= 0) {
    header('Location: applications.php');
    exit();
}

// Ambil data lamaran (hanya lamaran milik user ini)
$sql = "SELECT a.*, 
               j.title AS job_title,
               j.description AS job_description
        FROM applications a
        LEFT JOIN job_postings j ON a.job_id = j.id
        WHERE a.id = ? AND a.user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    $_SESSION['error'] = 'Lamaran tidak ditemukan.';
    header('Location: applications.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lamaran</title>
</head>
<body>

    <!-- Navbar -->
    <div class="top-bar">
        <div class="nav-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="vacancies.php">💼 Lowongan</a>
            <a href="applications.php" class="active">📝 Lamaran</a>
        </div>
        <a href="../logout.php" class="logout">Keluar</a>
    </div>

    <!-- Konten -->
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <h2 class="content-title">📋 Detail Lamaran: <?= htmlspecialchars($app['job_title'] ?? '-') ?></h2>

            <div class="info-group">
                <div class="info-label">Posisi yang Dilamar:</div>
                <div class="info-value"><?= htmlspecialchars($app['job_title'] ?? '-') ?></div>
            </div>

            <div class="info-group">
                <div class="info-label">Status:</div>
                <?php
                $status = $app['status'] ?? 'pending';
                $statusClass = match ($status) {
                    'pending' => 'status-pending',
                    'approved' => 'status-approved',
                    'rejected' => 'status-rejected',
                    default => 'status-pending'
                };
                ?>
                <div class="info-value">
                    <span class="status-badge <?= $statusClass ?>">
                        <?= ucfirst($status) ?>
                    </span>
                </div>
            </div>

            <div class="info-group">
                <div class="info-label">Tanggal Lamaran:</div>
                <div class="info-value">
                    <?= !empty($app['created_at']) ? date('d M Y H:i', strtotime($app['created_at'])) : '-' ?>
                </div>
            </div>

            <?php if (!empty($app['cover_letter'])): ?>
                <div class="info-group">
                    <div class="info-label">Cover Letter:</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($app['cover_letter'])) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($app['pdf_path'])): ?>
                <div class="info-group">
                    <div class="info-label">File Lamaran:</div>
                    <a href="?id=<?= $id ?>&pdf=1" target="_blank" class="file-link">📄 Lihat PDF Lamaran</a>
                </div>
            <?php endif; ?>

            <div class="info-group">
                <div class="info-label">Foto:</div>
                <div class="info-value">
                    <?php 
                    if (!empty($app['photo'])): 
                        $photo_path = "../uploads/" . $app['photo'];
                        if (file_exists($photo_path)): ?>
                            <img src="<?= $photo_path ?>" alt="Foto" class="photo-preview">
                        <?php else: ?>
                            <span style="color: var(--text-light);">File foto tidak ditemukan</span>
                        <?php endif; 
                    else: ?>
                        <span style="color: var(--text-light);">Belum ada foto</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php 
            // Hasil interview hanya muncul jika CV sudah di-approve
            $status = $app['status'] ?? 'pending';
            
            // Tampilkan jadwal interview ke user jika sudah ada
            if ($status === 'approved' && !empty($app['interview_date']) && !empty($app['interview_time']) && !empty($app['interview_location'])): ?>
                <div class="info-group">
                    <div class="info-label">Jadwal Interview:</div>
                    <div class="info-value">
                        <div>📅 <?= date('d M Y', strtotime($app['interview_date'])) ?></div>
                        <div>🕐 <?= date('H:i', strtotime($app['interview_time'])) ?> WIB</div>
                        <div>📍 <?= htmlspecialchars($app['interview_location']) ?></div>
                        <?php if (!empty($app['interview_notes'])): ?>
                            <div class="feedback-box" style="margin-top:8px; font-style: normal;">
                                <?= nl2br(htmlspecialchars($app['interview_notes'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php 
            if ($status === 'approved'): 
                if (!empty($app['interview_result'])): ?>
                    <div class="info-group">
                        <div class="info-label">Hasil Interview:</div>
                        <div class="info-value">
                            <?php if($app['interview_result'] === 'accepted'): ?>
                                <span class="status-badge status-approved">✅ Lolos Interview</span>
                            <?php elseif($app['interview_result'] === 'rejected'): ?>
                                <span class="status-badge status-rejected">❌ Tidak Lolos Interview</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">⏳ Menunggu Hasil Interview</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($app['interview_feedback'])): ?>
                        <div class="info-group">
                            <div class="info-label">Feedback Interview:</div>
                            <div class="info-value feedback-box"><?= nl2br(htmlspecialchars($app['interview_feedback'])) ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Surat Sehat - Hanya muncul jika diterima -->
                    <?php if ($app['interview_result'] === 'accepted'): ?>
                        <div class="info-group">
                            <div class="info-label">📄 Surat Sehat:</div>
                            <div class="info-value">
                                <?php if (!empty($app['health_certificate'])): ?>
                                    <div style="margin-bottom: 15px;">
                                        <p style="color: #0e9f6e; font-weight: 600;">✅ Surat sehat sudah diupload</p>
                                        <a href="../uploads/<?= htmlspecialchars($app['health_certificate']) ?>" target="_blank" class="file-link">📥 Download Surat Sehat</a>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data" style="margin-top: 10px;">
                                        <input type="hidden" name="app_id" value="<?= $id ?>">
                                        <label style="font-size: 13px; color: var(--text-light);">Ganti Surat Sehat:</label>
                                        <div style="display: flex; gap: 10px; margin-top: 8px;">
                                            <input type="file" name="health_certificate" accept="image/*,.pdf" style="flex: 1; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;" required>
                                            <button type="submit" class="btn-upload">Upload</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="app_id" value="<?= $id ?>">
                                        <p style="color: var(--text-light); margin-bottom: 10px;">Silakan upload surat sehat untuk melengkapi berkas.</p>
                                        <div style="display: flex; gap: 10px;">
                                            <input type="file" name="health_certificate" accept="image/*,.pdf" style="flex: 1; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;" required>
                                            <button type="submit" class="btn-upload">Upload</button>
                                        </div>
                                        <small style="display: block; margin-top: 8px; color: var(--text-light);">Format: JPG, PNG, GIF, atau PDF. Maksimal 5MB.</small>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="info-group">
                        <div class="info-label">Hasil Interview:</div>
                        <div class="info-value">
                            <span class="status-badge status-pending">⏳ Menunggu Hasil Interview</span>
                        </div>
                    </div>
                <?php endif; 
            elseif ($status === 'pending'): ?>
                <div class="info-group">
                    <div class="info-label">Hasil Interview:</div>
                    <div class="info-value">
                        <span style="color: var(--text-light); font-style: italic;">
                            ⏸️ Hasil interview akan muncul setelah melakukan interview
                        </span>
                    </div>
                </div>
            <?php elseif ($status === 'rejected'): ?>
                <div class="info-group">
                    <div class="info-label">Hasil Interview:</div>
                    <div class="info-value">
                        <span style="color: var(--text-light); font-style: italic;">
                            ⏸️ CV Anda ditolak, tidak ada proses interview
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="btn-group">
                <a href="applications.php" class="btn btn-back">← Kembali ke Daftar Lamaran</a>
            </div>
        </div>
    </div>

    <!-- CSS -->
    <style>
        :root {
            --primary: #4361ee;
            --danger: #ef233c;
            --success: #0e9f6e;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f7fafc;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .top-bar {
            background: linear-gradient(135deg, var(--primary), #2c44b3);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-menu {
            display: flex;
            gap: 20px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-menu a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }

        .nav-menu a.active {
            background: #6688ee;
            color: white;
            box-shadow: 0 2px 8px rgba(102, 136, 238, 0.3);
        }

        .nav-menu a.active:hover {
            background: #7799ff;
            transform: translateY(-1px);
        }

        .logout {
            background: var(--danger);
            padding: 8px 20px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(239,35,60,0.2);
        }

        .logout:hover {
            background: #dc2f45;
            transform: translateY(-1px);
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .content-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .content-title {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            color: var(--text-dark);
            font-weight: 600;
        }

        .info-group {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #edf2f7;
        }

        .info-group:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-value {
            color: var(--text-dark);
            line-height: 1.6;
        }

        .feedback-box {
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid var(--primary);
            font-style: italic;
            color: var(--text-dark);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .file-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .file-link:hover {
            color: #2c44b3;
            text-decoration: underline;
        }

        .photo-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            margin-top: 10px;
        }

        .btn-group {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-back {
            background: #e2e8f0;
            color: var(--text-dark);
        }

        .btn-back:hover {
            background: #cbd5e0;
            transform: translateY(-1px);
        }

        .btn-upload {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-upload:hover {
            background: #2c44b3;
            transform: translateY(-1px);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</body>
</html>
