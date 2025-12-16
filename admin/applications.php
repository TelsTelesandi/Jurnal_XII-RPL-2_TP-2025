<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// --- ADDED: filter preparation ---
$filter_job_id = isset($_GET['job_id']) && is_numeric($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
// get job list for dropdown
$jobs_list = $conn->query("SELECT id, title FROM job_postings ORDER BY title");

// prepare applications list depending on filter
if ($filter_job_id > 0) {
    $stmt = $conn->prepare("
        SELECT a.*, u.username, j.title AS job_title
        FROM applications a
        JOIN users u ON a.user_id = u.id
        JOIN job_postings j ON a.job_id = j.id
        WHERE a.job_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->bind_param('i', $filter_job_id);
    $stmt->execute();
    $applications = $stmt->get_result();
    $stmt->close();
} else {
    $query = "SELECT a.*, u.username, j.title as job_title 
              FROM applications a 
              JOIN users u ON a.user_id = u.id 
              JOIN job_postings j ON a.job_id = j.id 
              ORDER BY a.created_at DESC";
    $applications = $conn->query($query);
}

// ambil judul posisi terpilih untuk ditampilkan sebagai "chip"
$selected_title = '';
if ($filter_job_id > 0) {
    $stmtJ = $conn->prepare("SELECT title FROM job_postings WHERE id = ?");
    $stmtJ->bind_param('i', $filter_job_id);
    $stmtJ->execute();
    $resJ = $stmtJ->get_result();
    if ($resJ && $rj = $resJ->fetch_assoc()) $selected_title = $rj['title'];
    $stmtJ->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Lamaran</title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="applications.php" class="active">📝 Lamaran</a>
            <a href="vacancies.php">💼 Lowongan</a>
            <a href="manage_admins.php">👥 Kelola Admin</a>
        </div>
        <div class="user-menu">
            <span class="admin-badge">👑 Admin</span>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning'])): ?>
            <div class="alert alert-warning">
                <?php 
                    echo $_SESSION['warning'];
                    unset($_SESSION['warning']);
                ?>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <h2 class="content-title">📝 Kelola Lamaran</h2>

            <!-- ENHANCED FILTER PANEL - replace the old filter block with this -->
            <div class="filter-panel enhanced">
                <form method="get" class="filter-form" aria-label="Filter posisi">
                    <div class="filter-left">
                        <label for="job-filter" class="filter-label">Posisi</label>
                        <div class="select-wrap">
                            <span class="select-icon">🏷️</span>
                            <select id="job-filter" name="job_id" onchange="this.form.submit()">
                                <option value="0">Semua Posisi</option>
                                <?php if ($jobs_list && $jobs_list->num_rows): ?>
                                    <?php while ($jobRow = $jobs_list->fetch_assoc()): ?>
                                        <option value="<?= $jobRow['id'] ?>" <?= $filter_job_id === (int)$jobRow['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($jobRow['title']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            <button type="button" class="btn-clear" title="Reset filter"
                                    onclick="document.getElementById('job-filter').value='0'; this.form.submit();">
                                ✖ Reset
                            </button>
                        </div>

                        <?php if ($selected_title): ?>
                            <div class="filter-chip" title="Posisi terpilih">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:6px;vertical-align:middle">
                                    <path d="M5 12h14" stroke="#4361ee" stroke-width="2" stroke-linecap="round"></path>
                                </svg>
                                <span>Dipilih: <strong><?= htmlspecialchars($selected_title) ?></strong></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="filter-right">
                        <div class="filter-stats" aria-live="polite">
                            Menampilkan: <span class="count"><?= isset($applications) && is_object($applications) ? $applications->num_rows : 0 ?></span> lamaran
                        </div>
                    </div>
                </form>
            </div>
            <!-- END enhanced filter -->

            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Status CV</th>
                        <th>Status Interview</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($app = $applications->fetch_assoc()): 
                        $statusClass = match($app['status']) {
                            'pending' => 'status-pending',
                            'approved' => 'status-approved',
                            'rejected' => 'status-rejected',
                            default => 'status-pending'
                        };
                        
                        $interviewClass = match($app['interview_result']) {
                            'accepted' => 'status-approved',
                            'rejected' => 'status-rejected',
                            'pending' => 'status-pending',
                            default => ''
                        };
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($app['username']) ?></td>
                        <td><?= htmlspecialchars($app['job_title']) ?></td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= ucfirst($app['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($app['status'] === 'approved'): ?>
                                <?php if (empty($app['interview_date'])): ?>
                                    <a href="schedule_interview.php?id=<?= $app['id'] ?>" 
                                       class="btn-schedule">
                                        📅 Jadwalkan Interview
                                    </a>
                                <?php else: ?>
                                    <div class="interview-status">
                                        <div class="interview-info">
                                            📅 <?= date('d M Y', strtotime($app['interview_date'])) ?>
                                            ⏰ <?= date('H:i', strtotime($app['interview_time'])) ?>
                                        </div>
                                        <?php if($app['interview_result']): ?>
                                            <span class="status-badge <?= $interviewClass ?>">
                                                <?php 
                                                    echo match($app['interview_result']) {
                                                        'accepted' => '✅ Diterima',
                                                        'rejected' => '❌ Tidak Lolos',
                                                        'pending' => '⏳ Menunggu Hasil',
                                                        default => '-'
                                                    };
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                        <a href="schedule_interview.php?id=<?= $app['id'] ?>" 
                                           class="btn-edit-schedule" 
                                           title="Lihat/Edit Jadwal & Hasil Interview">
                                            <?= $app['interview_result'] ? '✏️ Edit Hasil' : '📝 Isi Hasil' ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($app['created_at'])) ?></td>
                        <td>
                            <a href="view_application.php?id=<?= $app['id'] ?>" class="btn-view">
                                👁️ Lihat Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

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
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .nav-menu a:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }

    .nav-menu a.active {
        background: #6688ee;
        box-shadow: 0 2px 8px rgba(102, 136, 238, 0.3);
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

    /* filter form small styling (kept minimal to match page) */
    .filter-form select { font-size:14px; }
    .filter-form label { font-size:14px; }

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

    .filter-panel {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        padding: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        border: 1px solid #e6eefc;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(67,97,238,0.06);
        margin-bottom: 18px;
    }

    .filter-panel.enhanced {
        background: #f0f4ff;
        border-color: #d1e7ff;
        position: relative;
        overflow: hidden;
    }

    .filter-panel.enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(67, 97, 238, 0.05);
        border-radius: 10px;
        z-index: 0;
    }

    .filter-form {
        display: flex;
        gap: 18px;
        align-items: center;
        width: 100%;
        position: relative;
        z-index: 1;
    }

    .filter-left, .filter-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .filter-label {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 14px;
    }

    .select-wrap {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: white;
        border: 1px solid #e6eefc;
        padding: 6px 8px;
        border-radius: 8px;
    }

    .select-wrap select {
        border: none;
        outline: none;
        padding: 6px 8px;
        background: transparent;
        font-size: 14px;
        color: var(--text-dark);
        min-width: 220px;
    }

    .btn-clear {
        background: transparent;
        border: 1px solid #e6eefc;
        color: var(--text-dark);
        padding: 6px 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        transition: all .15s;
    }

    .btn-clear:hover {
        background: #f3f6ff;
        transform: translateY(-2px);
    }

    .search-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        border: 1px solid #e6eefc;
        padding: 6px 8px;
        border-radius: 8px;
    }

    .search-wrap input[type="text"] {
        border: none;
        outline: none;
        padding: 8px 10px;
        font-size: 14px;
        min-width: 260px;
        background: transparent;
        color: var(--text-dark);
    }

    .btn-search {
        background: var(--primary);
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: background .15s, transform .12s;
    }

    .btn-search:hover { background: #3251d4; transform: translateY(-2px); }

    .filter-stats {
        color: var(--text-light);
        font-size: 13px;
        margin-left: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #edf2f7;
    }

    th {
        font-weight: 600;
        color: var(--text-dark);
        background: #f8fafc;
    }

    tr:hover {
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

    .btn-view {
        color: var(--text-dark);
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 6px;
        background: #e2e8f0;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-view:hover {
        background: #cbd5e0;
        transform: translateY(-1px);
    }

    .btn-schedule {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--primary);
        color: white;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        margin-left: 10px;
        transition: all 0.3s ease;
    }

    .btn-schedule:hover {
        background: #3251d4;
        transform: translateY(-1px);
    }

    .interview-status {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .interview-info {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e6effd;
        color: var(--primary);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
    }

    .btn-edit-schedule {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--success);
        color: white;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.3s ease;
        width: fit-content;
    }

    .btn-edit-schedule:hover {
        background: #0b876a;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .alert {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #34d399;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffd700;
    }

    /* Responsive: stack on small screens */
    @media (max-width: 780px) {
        .filter-panel { flex-direction: column; align-items: stretch; gap: 10px; }
        .filter-form { flex-direction: column; gap: 10px; }
        .search-wrap input[type="text"], .select-wrap select { min-width: 100%; }
        .filter-stats { text-align: right; width: 100%; }
    }
</style>
</body>
</html>
