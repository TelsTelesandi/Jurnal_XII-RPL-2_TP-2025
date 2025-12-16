<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Lowongan Tersedia';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle; ?></title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <div class="brand"></div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="vacancies.php" class="active">💼 Lowongan</a>
            <a href="applications.php">📝 Lamaran</a>
        </div>
        <a href="../logout.php" class="logout">Keluar</a>
    </div>

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
            <h2 class="content-title">💼 Lowongan Tersedia</h2>
            <?php
            $jobs = $conn->query("SELECT * FROM job_postings WHERE is_visible = 1 ORDER BY created_at DESC");
            
            if ($jobs->num_rows > 0):
                while ($job = $jobs->fetch_assoc()): 
                    // Check if user already has an application
                    $check_stmt = $conn->prepare("SELECT id FROM applications WHERE user_id = ?");
                    $check_stmt->bind_param('i', $_SESSION['user_id']);
                    $check_stmt->execute();
                    $has_application = $check_stmt->get_result()->fetch_assoc();

                    // Check job application count and max applicants
                    $app_count = $conn->query("SELECT COUNT(*) as total FROM applications WHERE job_id = " . (int)$job['id'])->fetch_assoc()['total'];
                    $max = (int)($job['max_applicants'] ?? 0);
                    $is_full = ($max > 0 && $app_count >= $max);
            ?>
                <div class="job-item">
                    <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                    <div class="job-meta">
                        <div>📅 Dibuat: <?php echo date('d M Y', strtotime($job['created_at'])); ?></div>
                        <?php if (!empty($job['description'])): ?>
                            <div class="job-desc"><?php echo nl2br(htmlspecialchars($job['description'])); ?></div>
                        <?php endif; ?>
                        <div class="job-actions">
                            <?php if ($has_application): ?>
                                <button class="btn-disabled">
                                    ⚠️ Sudah Melamar Pekerjaan Lain
                                </button>
                            <?php elseif ($is_full): ?>
                                <button class="btn-disabled">
                                    🔒 Kuota Penuh
                                </button>
                            <?php else: ?>
                                <a href="apply_job.php?id=<?= $job['id'] ?>" class="btn-apply">
                                    Lamar Sekarang
                                </a>
                            <?php endif; ?>
                            <a href="view_job.php?id=<?= $job['id'] ?>" class="btn-view">
                                👁️ Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile;
            else: ?>
                <p style="color:var(--text-light);text-align:center;padding:20px;">
                    Tidak ada lowongan tersedia saat ini.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- CSS dipindahkan ke bawah -->
    <style>
        :root {
            --primary: #4361ee;
            --danger: #ef233c;
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
            margin-bottom: 10px;
        }

        .job-meta {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .job-desc {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.6;
            margin: 10px 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #2c44b3;
            transform: translateY(-1px);
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
        }

        .btn-view:hover {
            background: #cbd5e0;
            transform: translateY(-1px);
        }

        .alert-danger {
            background: #ffd5d5;
            color: #842029;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c2c7;
        }

        .btn-disabled {
            background: #e2e8f0;
            color: #64748b;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #cbd5e0;
            cursor: not-allowed;
        }

        .btn-apply {
            background: linear-gradient(135deg, var(--primary), #2c44b3);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(67, 97, 238, 0.2);
        }

        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
            background: linear-gradient(135deg, #3251d4, #1e3299);
        }

        .btn-apply:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(67, 97, 238, 0.2);
        }

        .btn-apply::before {
            content: '📝';
            font-size: 18px;
        }
    </style>
</body>
</html>
