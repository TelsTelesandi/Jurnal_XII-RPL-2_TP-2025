<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// 🔒 Cek login admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 🔹 Handle PDF Viewer
if (isset($_GET['pdf'])) {
    $app_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT pdf_path FROM applications WHERE id = ?");
    $stmt->bind_param('i', $app_id);
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

// 🔹 Ambil ID lamaran
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php');
    exit();
}

// 🔹 Ambil data lamaran
$sql = "SELECT a.*, 
               COALESCE(u.full_name, u.username) AS applicant_name, 
               u.email AS applicant_email,
               j.title AS job_title
        FROM applications a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN job_postings j ON a.job_id = j.id
        WHERE a.id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    die('Lamaran tidak ditemukan.');
}

// Ambil data processor (admin yang memproses)
$processor_name = '';
if (!empty($app['processed_by'])) {
    $stmt = $conn->prepare("SELECT COALESCE(full_name, username) AS name FROM users WHERE id = ?");
    $stmt->bind_param('i', $app['processed_by']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $processor_name = $row['name'];
    }
    $stmt->close();
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

    <!-- 🔹 Navbar -->
    <div class="top-bar">
        <div class="nav-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="applications.php">📝 Lamaran</a>
            <a href="vacancies.php">💼 Lowongan</a>
        </div>
        <div class="user-menu">
            <span class="admin-badge">👑 Admin</span>
        </div>
    </div>

    <!-- 🔹 Konten -->
    <div class="container">
        <div class="content-box">
            <h2 class="content-title">📋 Detail Lamaran: <?= htmlspecialchars($app['job_title'] ?? '-') ?></h2>

            <div class="info-group">
                <div class="info-label">Pelamar:</div>
                <div class="info-value">
                    <?= htmlspecialchars($app['applicant_name'] ?? '-') ?> 
                    (<?= htmlspecialchars($app['applicant_email'] ?? '-') ?>)
                </div>
            </div>

            <div class="info-group">
                <div class="info-label">Status:</div>
                <?php
                // ✅ Versi aman untuk PHP 8.3
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

            <?php if ($processor_name): ?>
                <div class="info-group">
                    <div class="info-label">Diproses oleh:</div>
                    <div class="info-value">
                        <?= htmlspecialchars($processor_name) ?> 
                        pada <?= !empty($app['processed_at']) ? date('d M Y H:i', strtotime($app['processed_at'])) : '-' ?>
                    </div>
                </div>
            <?php endif; ?>

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
                <div class="info-label">Foto Pelamar:</div>
                <div class="info-value">
                    <?php 
                    if (!empty($app['photo'])): 
                        $photo_path = "../uploads/" . $app['photo'];
                        if (file_exists($photo_path)): ?>
                            <img src="<?= $photo_path ?>" alt="Foto Pelamar" class="photo-preview">
                        <?php else: ?>
                            <span style="color: var(--text-light);">File foto tidak ditemukan</span>
                        <?php endif; 
                    else: ?>
                        <span style="color: var(--text-light);">Belum ada foto</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (($app['status'] ?? '') === 'pending'): ?>
                <div class="btn-group">
                    <a href="process_application.php?id=<?= $id ?>&action=approve" class="btn btn-accept">✅ Terima</a>
                    <a href="process_application.php?id=<?= $id ?>&action=reject" class="btn btn-reject">❌ Tolak</a>
                </div>
            <?php endif; ?>

            <!-- 🔹 Surat Sehat yang dikirim User -->
            <?php if (($app['interview_result'] ?? '') === 'accepted' && !empty($app['health_certificate'])): ?>
                <div class="health-certificate-section">
                    <h3>📄 Surat Kesehatan dari User</h3>
                    <div class="doc-preview">
                        <p style="color: var(--text-dark); margin-bottom: 10px;">
                            <strong>Status:</strong> <span class="status-badge status-approved">✅ Sudah Dikirim</span>
                        </p>
                        <a href="../uploads/<?= htmlspecialchars($app['health_certificate']) ?>" target="_blank" class="btn-view">
                            👁️ Lihat Surat Kesehatan
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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
            box-sizing: border-box;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .nav-menu a.active {
            background: #6688ee;
            box-shadow: 0 2px 8px rgba(102, 136, 238, 0.3);
        }

        .user-menu .admin-badge {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 6px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .content-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }

        .content-title {
            font-size: 20px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            font-weight: 600;
        }

        .info-group { margin-bottom: 15px; }
        .info-label { font-weight: 600; }
        .info-value { color: var(--text-light); margin-top: 3px; }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .btn-group { display: flex; gap: 15px; margin-top: 25px; }
        .btn { text-decoration: none; padding: 10px 22px; border-radius: 6px; color: white; font-weight: 500; }
        .btn-accept { background: var(--success); }
        .btn-reject { background: var(--danger); }

        .file-link { color: var(--primary); text-decoration: none; }
        .file-link:hover { text-decoration: underline; }

        .photo-preview {
            margin-top: 10px;
            max-width: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .health-certificate-section {
            margin-top: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .health-certificate-section h3 {
            margin-top: 0;
            color: var(--text-dark);
            font-size: 16px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--success);
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { font-weight: 500; margin-bottom: 5px; display: block; }
        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid rgba(0,0,0,0.1);
            font-size: 14px;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-submit:hover { background: #365fcf; }

        .doc-preview {
            background: white;
            padding: 12px;
            border-radius: 6px;
            margin-top: 10px;
        }

        .btn-view {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            margin-right: 10px;
            background: var(--primary);
        }

        .btn-view:hover { background: #365fcf; }
    </style>

</body>
</html>
