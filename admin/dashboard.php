<?php
session_start();
require_once '../config/database.php';

// 🔒 Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Judul halaman
$pageTitle = 'Admin Dashboard';

// 🔢 Query Statistik
$total_apps = $conn->query("SELECT COUNT(*) AS count FROM applications")->fetch_assoc()['count'];
$active_jobs = $conn->query("SELECT COUNT(*) AS count FROM job_postings WHERE is_visible = 1")->fetch_assoc()['count'];
$pending_apps = $conn->query("SELECT COUNT(*) AS count FROM applications WHERE status='pending'")->fetch_assoc()['count'];

// 📋 Query Lamaran Terbaru
$recent_apps = $conn->query("
    SELECT a.*, u.username, j.title 
    FROM applications a 
    JOIN users u ON a.user_id = u.id 
    JOIN job_postings j ON a.job_id = j.id 
    ORDER BY a.created_at DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>

    <!-- 🔹 Navbar -->
    <div class="top-bar">
        <div class="nav-menu">
            <?php
            // Tentukan halaman aktif berdasarkan nama file
            $current_page = basename($_SERVER['PHP_SELF']);
            $menus = [
                'dashboard.php' => ['📊', 'Dashboard'],
                'applications.php' => ['📝', 'Lamaran'],
                'vacancies.php' => ['💼', 'Lowongan'],
                'manage_admins.php' => ['👥', 'Kelola Admin']
            ];
            foreach ($menus as $file => $item) {
                $isActive = $current_page === $file ? 'active' : '';
                echo "<a href='$file' class='$isActive'>{$item[0]} {$item[1]}</a>";
            }
            ?>
        </div>

        <div class="user-menu">
            <span class="admin-badge">👑 Admin</span>
            <a href="../logout.php" class="logout">Keluar</a>
        </div>
    </div>

    <!-- 🔹 Konten -->
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="content-box">
            <h2 class="content-title">📊 Statistik Overview</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $total_apps ?></div>
                    <div class="stat-label">Total Lamaran</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $active_jobs ?></div>
                    <div class="stat-label">Lowongan Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $pending_apps ?></div>
                    <div class="stat-label">Lamaran Pending</div>
                </div>
            </div>
        </div>

        <!-- Lamaran Terbaru -->
        <div class="content-box">
            <h2 class="content-title">📝 Lamaran Terbaru</h2>
            <?php if ($recent_apps->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Posisi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $recent_apps->fetch_assoc()): 
                            $statusClass = match($app['status']) {
                                'pending' => 'status-pending',
                                'approved' => 'status-approved',
                                'rejected' => 'status-rejected',
                                default => 'status-pending'
                            };
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($app['username']) ?></td>
                            <td><?= htmlspecialchars($app['title']) ?></td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($app['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:var(--text-light);text-align:center;padding:20px;">
                    Belum ada lamaran yang diajukan.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Move all CSS here, just before closing body tag -->
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

        /* 🔹 Navbar */
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
            align-items: center;
            gap: 20px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 25px;
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

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 15px;
        }

        .logout {
            background: var(--danger);
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 15px;
            transition: background 0.3s;
        }

        .logout:hover {
            background: #b91c1c;
        }

        /* 🔹 Konten utama */
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 14px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--text-dark);
        }

        .table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
</body>
</html>
