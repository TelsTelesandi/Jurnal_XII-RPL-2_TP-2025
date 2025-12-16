<?php
session_start();
require_once '../config/database.php';

// ✅ Cek login user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

// ✅ Ambil detail lowongan
$job_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM job_postings WHERE id = ? AND is_visible = 1");
$stmt->bind_param('i', $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    header('Location: vacancies.php');
    exit();
}

// ✅ Cek apakah user sudah melamar
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, status FROM applications WHERE job_id = ? AND user_id = ?");
$stmt->bind_param('ii', $job_id, $user_id);
$stmt->execute();
$existing_application = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($job['title']) ?></title>
</head>
<body>
    <div class="top-bar">
        <div class="menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="vacancies.php">💼 Lowongan</a>
            <a href="applications.php">📝 Lamaran</a>
        </div>
    </div>

    <div class="container">
        <div class="content-box">
            <h2 class="content-title">💼 <?= htmlspecialchars($job['title']) ?></h2>
            
            <div class="job-info">
                <div class="section">
                    <h3 class="section-title">📝 Deskripsi Pekerjaan</h3>
                    <div class="section-content">
                        <?= nl2br(htmlspecialchars($job['description'] ?? '')) ?>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title">📋 Persyaratan</h3>
                    <div class="section-content">
                        <?= nl2br(htmlspecialchars($job['requirements'] ?? '')) ?>
                    </div>
                </div>

                <div class="button-group">
                    <a href="vacancies.php" class="btn btn-back">← Kembali</a>
                    <?php if ($existing_application): ?>
                        <div class="status-badge">
                            ℹ️ Status: <?= isset($existing_application['status']) ? 
                                ucfirst($existing_application['status']) : 'Pending' ?>
                        </div>
                    <?php else: ?>
                        <a href="apply_job.php?id=<?= $job['id'] ?>" class="btn btn-apply">
                            📤 Lamar Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #2c44b3;
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
        }

        .top-bar {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            height: 64px;
            box-sizing: border-box;
        }

        .menu {
            display: flex;
            gap: 20px;
        }

        .menu a {
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .menu a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .content-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }

        .content-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-dark);
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            margin-bottom: 25px;
        }

        .section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .section-content {
            color: var(--text-dark);
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-back {
            background: #e2e8f0;
            color: var(--text-dark);
        }

        .btn-back:hover {
            background: #cbd5e0;
        }

        .btn-apply {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 4px rgba(67,97,238,0.2);
        }

        .btn-apply:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(67,97,238,0.3);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 6px;
            background: #f3f4f6;
            color: var(--text-dark);
            font-weight: 500;
        }
    </style>
</body>
</html>
