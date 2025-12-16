<?php
session_start();
require_once '../config/database.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Lowongan</title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="applications.php">📝 Lamaran</a>
            <a href="vacancies.php" class="active">💼 Lowongan</a>
            <a href="manage_admins.php">👥 Kelola Admin</a>
        </div>
        <div class="user-menu">
            <span class="admin-badge">👑 Admin</span>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <div class="content-title">
                <span>💼 Kelola Lowongan</span>
                <a href="add_vacancy_form.php" class="btn-add">➕ Tambah Lowongan</a>
            </div>

            <?php
            $query = "SELECT * FROM job_postings ORDER BY created_at DESC";
            $vacancies = $conn->query($query);

            if ($vacancies->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Posisi</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Total Pelamar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($vacancy = $vacancies->fetch_assoc()):
                        $app_count = $conn->query("SELECT COUNT(*) as total FROM applications WHERE job_id = " . (int)$vacancy['id'])->fetch_assoc()['total'];
                        $max = (int)$vacancy['max_applicants'];
                        $remaining = $max > 0 ? max(0, $max - $app_count) : null;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vacancy['title']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $vacancy['is_visible'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $vacancy['is_visible'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                <?php if ($max > 0): ?>
                                    <div style="margin-top:6px;font-size:13px;color:var(--text-light);">
                                        Kuota: <?php echo $app_count . ' / ' . $max; ?>
                                        <?php if ($remaining === 0): ?>
                                            <span style="color:#dc2626;font-weight:600;margin-left:8px;">(Penuh)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($vacancy['created_at'])); ?></td>
                            <td><?php echo $app_count; ?> pelamar</td>
                            <td>
                                <a href="edit_vacancy.php?id=<?php echo $vacancy['id']; ?>" class="action-btn btn-edit">✏️ Edit</a>
                                <a href="toggle_vacancy.php?id=<?php echo $vacancy['id']; ?>&status=<?php echo $vacancy['is_visible'] ? '0' : '1'; ?>" 
                                   class="btn-toggle <?php echo $vacancy['is_visible'] ? 'active' : 'inactive'; ?>"
                                   onclick="return confirm('Yakin ingin <?php echo $vacancy['is_visible'] ? 'menonaktifkan' : 'mengaktifkan'; ?> lowongan ini?')">
                                    <?php echo $vacancy['is_visible'] ? '🔴 Nonaktifkan' : '🟢 Aktifkan'; ?>
                                </a>
                                <a href="delete_vacancy.php?id=<?php echo $vacancy['id']; ?>" class="action-btn btn-delete"
                                   onclick="return confirm('Yakin ingin menghapus lowongan ini? Semua lamaran terkait akan ikut terhapus.')">
                                    🗑️ Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:var(--text-light);text-align:center;padding:20px;">
                    Belum ada lowongan tersedia.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ✅ CSS dipindahkan ke bawah -->
    <style>
        :root {
            --primary: #4361ee;
            --danger: #ef233c;
            --success: #0e9f6e;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f7fafc;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-light);
        }

        /* ==== NAVBAR ==== */
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

        /* ==== CONTAINER ==== */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .content-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .content-title {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            color: var(--text-dark);
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-add {
            background: var(--success);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-add:hover { background: #0b876a; }

        /* ==== TABLE ==== */
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

        .table tr:hover { background: #f8fafc; }

        /* ==== BUTTONS ==== */
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            margin-right: 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-view { background: var(--primary); color: white; }
        .btn-edit { background: #f59e0b; color: white; }
        .btn-delete { background: var(--danger); color: white; }

        .btn-toggle {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-toggle:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-toggle.active {
            background: #ef4444;
            color: white;
        }

        .btn-toggle.active:hover {
            background: #dc2626;
        }

        .btn-toggle.inactive {
            background: #10b981;
            color: white;
        }

        .btn-toggle.inactive:hover {
            background: #059669;
        }

        /* ==== STATUS ==== */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ==== ALERT ==== */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</body>
</html>
