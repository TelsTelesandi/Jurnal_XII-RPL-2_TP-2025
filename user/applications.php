<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

// Ambil data lamaran
$query = "SELECT a.*, j.title AS job_title 
          FROM applications a
          LEFT JOIN job_postings j ON a.job_id = j.id 
          WHERE a.user_id = ?
          ORDER BY a.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Lamaran</title>
</head>
<body>
    <!-- Navbar -->
    <div class="top-bar">
        <div class="nav-menu">
            <div class="brand"></div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="vacancies.php">💼 Lowongan</a>
            <a href="applications.php" class="active">📝 Lamaran</a>
        </div>
        <a href="../logout.php" class="logout">Keluar</a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <h2 class="content-title">📋 Riwayat Lamaran Saya</h2>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($app = $result->fetch_assoc()): ?>
                    <div class="job-item">
                        <div class="job-title"><?= htmlspecialchars($app['job_title']) ?></div>
                        <div class="job-meta">
                            <div class="status-group">
                                <div>📅 <?= date('d M Y', strtotime($app['created_at'])) ?></div>
                                
                                <!-- CV Status -->
                                <?php if($app['status'] === 'approved'): ?>
                                    <div class="status-badge success">✓ CV Diterima</div>
                                <?php endif; ?>

                                <!-- Interview Status -->
                                <?php if($app['status'] === 'approved' && $app['interview_result']): ?>
                                    <div class="status-badge <?= $app['interview_result'] ?>">
                                        <?php if($app['interview_result'] === 'accepted'): ?>
                                            ✅ Lolos Interview
                                        <?php elseif($app['interview_result'] === 'rejected'): ?>
                                            ❌ Tidak Lolos Interview
                                        <?php else: ?>
                                            ⏳ Menunggu Hasil Interview
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="job-actions">
                                <a href="view_application.php?id=<?= $app['id'] ?>" class="btn-view">
                                    👁️ Detail Lamaran
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:var(--text-light);text-align:center;padding:20px;">
                    Belum ada lamaran.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- CSS disamakan dengan vacancies -->
    <style>
        :root {
            --primary: #4361ee;
            --danger: #ef233c;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f7fafc;
            --success: #16a34a;
            --success-bg: #dcfce7;
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
            align-items: center;
        }

        .brand { width: 34px; height: 34px; }

        .brand img {
            height: 34px;
            width: auto;
            display: block;
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .content-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .job-item {
            padding: 20px;
            border-bottom: 1px solid #edf2f7;
            transition: all 0.3s ease;
        }

        .job-item:hover {
            background: #f8fafc;
            transform: translateX(5px);
        }

        .job-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .job-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .status-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .status-badge.success {
            background: var(--success-bg);
            color: var(--success);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-view {
            background: #e2e8f0;
            color: var(--text-dark);
            border: none;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 6px;
            text-align: center;
            display: inline-block;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            z-index: 1;
        }

        .btn-view:hover {
            background: #cbd5e0;
            transform: translateY(-1px);
            text-decoration: none;
        }

        .alert-danger {
            background: #ffd5d5;
            color: #842029;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c2c7;
        }
    </style>
</body>
</html>
