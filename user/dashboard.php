<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Get unread notifications
$notifications = $conn->query("
    SELECT * FROM notifications 
    WHERE recipient_user_id = $user_id
    ORDER BY created_at DESC
    LIMIT 10
");

$unread_count = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE recipient_user_id = $user_id AND is_read = 0")->fetch_assoc()['total'];

// Helper function
function getTimeAgo($date) {
    $time = strtotime($date);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', $time);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <div class="brand"></div>
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="vacancies.php">💼 Lowongan</a>
            <a href="applications.php">📝 Lamaran</a>
        </div>
        <div class="user-section">
            <!-- Notification Icon -->
            <div class="notification-container">
                <button class="notification-btn" id="notif-btn">
                    🔔
                    <?php if ($unread_count > 0): ?>
                        <span class="notif-badge"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
                    <?php endif; ?>
                </button>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notif-dropdown">
                    <div class="notif-header">
                        <h4>📬 Notifikasi</h4>
                        <div class="header-actions">
                            <?php if ($unread_count > 0): ?>
                                <button class="btn-mark-all" onclick="markAllAsRead()">Tandai semua</button>
                            <?php endif; ?>
                            <a href="notifications.php" class="btn-see-all">Lihat semua →</a>
                        </div>
                    </div>

                    <div class="notif-list">
                        <?php if ($notifications && $notifications->num_rows > 0): ?>
                            <?php while ($notif = $notifications->fetch_assoc()): ?>
                                <div class="notif-item <?= !$notif['is_read'] ? 'unread' : '' ?>" 
                                     id="notif-<?= $notif['id'] ?>"
                                     onclick="markAsRead(<?= $notif['id'] ?>)">
                                    <div class="notif-icon">
                                        <?php
                                            $notifType = $notif['type'] ?? '';
                                            if (strpos($notifType, 'approved') !== false) echo '✅';
                                            elseif (strpos($notifType, 'accepted') !== false) echo '🎉';
                                            elseif (strpos($notifType, 'rejected') !== false) echo '❌';
                                            elseif (strpos($notifType, 'interview') !== false) echo '📅';
                                            else echo '📢';
                                        ?>
                                    </div>
                                    <div class="notif-content">
                                        <p class="notif-title"><?= htmlspecialchars($notif['message']) ?></p>
                                        <p class="notif-time"><?= getTimeAgo($notif['created_at']) ?></p>
                                    </div>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="notif-dot"></span>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="notif-empty">
                                <div class="notif-empty-icon">📭</div>
                                <p class="notif-empty-text">Tidak ada notifikasi</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="notif-footer">
                        <a href="notifications.php">📬 Lihat semua notifikasi</a>
                    </div>
                </div>
            </div>

            <a href="../logout.php" class="logout">Keluar</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                <div class="user-email">📧 <?php echo htmlspecialchars($user['email']); ?></div>
            </div>

            <div class="content-box">
                <h2 class="content-title">💼 Lowongan Tersedia</h2>
                <?php
                $jobs = $conn->query("SELECT * FROM job_postings WHERE is_visible = 1 ORDER BY created_at DESC LIMIT 5");
                if ($jobs->num_rows > 0):
                    while ($job = $jobs->fetch_assoc()): ?>
                        <div class="job-item">
                            <div class="job-title">
                                <?php echo htmlspecialchars($job['title']); ?>
                            </div>
                            <div class="job-meta">
                                <span>📅 <?php echo date('d M Y', strtotime($job['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile;
                else: ?>
                    <p style="color:var(--text-light);text-align:center;padding:20px;">Tidak ada lowongan tersedia.</p>
                <?php endif; ?>
            </div>

            <div class="content-box">
                <h2 class="content-title">📝 Lamaran Saya</h2>
                <?php
                $applications = $conn->query("SELECT a.*, j.title 
                                           FROM applications a 
                                           JOIN job_postings j ON a.job_id = j.id 
                                           WHERE a.user_id = $user_id 
                                           ORDER BY a.created_at DESC");
                
                if ($applications->num_rows > 0):
                    while ($app = $applications->fetch_assoc()): 
                        $statusClass = match($app['status']) {
                            'pending' => 'status-pending',
                            'approved' => 'status-approved',
                            'rejected' => 'status-rejected',
                            default => 'status-pending'
                        };
                    ?>
                        <div class="job-item">
                            <div class="job-title">
                                <?php echo htmlspecialchars($app['title']); ?>
                            </div>
                            <div class="job-meta">
                                <!-- CV Status -->
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>

                                <!-- Interview Status -->
                                <?php if($app['status'] === 'approved' && isset($app['interview_result']) && $app['interview_result']): ?>
                                    <span class="status-badge status-<?= $app['interview_result'] ?>">
                                        <?php if($app['interview_result'] === 'accepted'): ?>
                                            ✅ Lolos Interview
                                        <?php elseif($app['interview_result'] === 'rejected'): ?>
                                            ❌ Tidak Lolos Interview
                                        <?php else: ?>
                                            ⏳ Menunggu Hasil Interview
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <span>📅 <?php echo date('d M Y', strtotime($app['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile;
                else: ?>
                    <p style="color:var(--text-light);text-align:center;padding:20px;">Belum ada lamaran.</p>
                <?php endif; ?>
            </div>
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
        
        * { box-sizing: border-box; }

        body { 
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        /* UPDATED: Top bar untuk match dengan vacancies */
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
            position: relative;
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

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
            height: 100%;
        }

        .notification-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            position: relative;
            color: white;
            transition: transform 0.2s;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            transform: scale(1.15);
        }

        .logout {
            background: var(--danger);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.3s;
            font-weight: 500;
            white-space: nowrap;
            border: none;
            cursor: pointer;
        }

        .logout:hover {
            background: #dc2f45;
            transform: translateY(-2px);
        }

        /* Notification Styles */
        .notification-container {
            position: relative;
        }

        .notification-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            position: relative;
            color: white;
            transition: transform 0.2s;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            transform: scale(1.15);
        }

        .notif-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef233c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            width: 400px;
            max-width: min(400px, calc(100vw - 40px));
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .notification-dropdown {
                width: calc(100vw - 20px);
                max-width: calc(100vw - 20px);
                left: 50%;
                transform: translateX(-50%);
            }
        }

        @media (max-width: 480px) {
            .notification-dropdown {
                width: calc(100vw - 16px);
                max-width: calc(100vw - 16px);
            }
        }

        .notification-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notif-header {
            padding: 18px 20px;
            border-bottom: 2px solid #e2e8f0;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
        }

        .notif-header h4 {
            margin: 0 0 12px 0;
            font-size: 18px;
            color: var(--text-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            align-items: center;
        }

        .btn-mark-all {
            background: var(--primary);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
            z-index: 10;
            pointer-events: auto;
        }

        .btn-mark-all:hover {
            background: #3251d4;
            transform: translateY(-1px);
        }

        .btn-mark-all:active {
            transform: translateY(0);
        }

        .btn-see-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            padding: 6px 0;
        }

        .btn-see-all:hover {
            color: #3251d4;
            text-decoration: underline;
        }

        .notif-list {
            max-height: 450px;
            overflow-y: auto;
            background: white;
        }

        .notif-list::-webkit-scrollbar {
            width: 6px;
        }

        .notif-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notif-list::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .notif-list::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .notif-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f4f8;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item:hover {
            background: #f8fafc;
            padding-left: 22px;
        }

        .notif-item.unread {
            background: #f1f6ff;
            border-left: 3px solid var(--primary);
        }

        .notif-item.unread:hover {
            background: #e8f0ff;
        }

        .notif-icon {
            font-size: 24px;
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #f0f4f8;
            border-radius: 8px;
        }

        .notif-item.unread .notif-icon {
            background: #e8f0ff;
        }

        .notif-content {
            flex: 1;
            min-width: 0;
        }

        .notif-title {
            margin: 0 0 6px 0;
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .notif-item.unread .notif-title {
            font-weight: 600;
        }

        .notif-time {
            margin: 0;
            color: var(--text-light);
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .notif-dot {
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
            margin-top: 8px;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .notif-footer {
            padding: 16px 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            background: #f8fafc;
        }

        .notif-footer a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            padding: 8px 16px;
            border-radius: 6px;
        }

        .notif-footer a:hover {
            background: #e8f0ff;
            color: #3251d4;
            transform: translateY(-1px);
        }

        .notif-empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .notif-empty-icon {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .notif-empty-text {
            margin: 0;
            font-size: 14px;
            color: var(--text-light);
        }

        .main-content {
            padding-top: 30px;
            padding-bottom: 30px;
            background: var(--bg-light);
            min-height: calc(100vh - 64px);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .user-info {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .user-name {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-dark);
            font-weight: 600;
        }

        .user-email {
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 5px;
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
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            color: var(--text-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .job-item {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .job-item:last-child {
            border-bottom: none;
        }

        .job-item:hover {
            background: #f8fafc;
            transform: translateX(5px);
        }

        .job-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-size: 16px;
        }

        .job-meta {
            color: var(--text-light);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-accepted { background: #d1fae5; color: #065f46; }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 20px;
            color: #166534;
            cursor: pointer;
            padding: 0 5px;
        }
    </style>

    <script>
        const notifBtn = document.getElementById('notif-btn');
        const notifDropdown = document.getElementById('notif-dropdown');

        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
            
            // Pastikan dropdown tidak keluar dari viewport
            if (notifDropdown.classList.contains('show')) {
                const rect = notifDropdown.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                
                // Jika dropdown keluar dari kanan
                if (rect.right > viewportWidth) {
                    notifDropdown.style.left = 'auto';
                    notifDropdown.style.right = '0';
                    notifDropdown.style.transform = 'translateX(0)';
                }
                
                // Jika dropdown keluar dari bawah
                if (rect.bottom > viewportHeight) {
                    notifDropdown.style.top = 'auto';
                    notifDropdown.style.bottom = '100%';
                    notifDropdown.style.marginTop = '0';
                    notifDropdown.style.marginBottom = '8px';
                }
            }
        });

        document.addEventListener('click', function(e) {
            // Jangan tutup dropdown jika klik pada tombol di dalam dropdown
            if (e.target.closest('.btn-mark-all') || e.target.closest('.btn-see-all')) {
                return;
            }
            
            if (!notifDropdown.contains(e.target) && e.target !== notifBtn) {
                notifDropdown.classList.remove('show');
                // Reset posisi saat ditutup
                notifDropdown.style.left = '0';
                notifDropdown.style.right = 'auto';
                notifDropdown.style.top = 'calc(100% + 8px)';
                notifDropdown.style.bottom = 'auto';
                notifDropdown.style.marginTop = '0';
                notifDropdown.style.marginBottom = '0';
                notifDropdown.style.transform = 'translateX(0)';
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (notifDropdown.classList.contains('show')) {
                const rect = notifDropdown.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                
                // Reset dulu
                notifDropdown.style.left = '0';
                notifDropdown.style.right = 'auto';
                notifDropdown.style.top = 'calc(100% + 8px)';
                notifDropdown.style.bottom = 'auto';
                notifDropdown.style.marginTop = '0';
                notifDropdown.style.marginBottom = '0';
                notifDropdown.style.transform = 'translateX(0)';
                
                // Cek lagi setelah reset
                setTimeout(() => {
                    const newRect = notifDropdown.getBoundingClientRect();
                    if (newRect.right > viewportWidth) {
                        notifDropdown.style.left = 'auto';
                        notifDropdown.style.right = '0';
                    }
                    if (newRect.bottom > viewportHeight) {
                        notifDropdown.style.top = 'auto';
                        notifDropdown.style.bottom = '100%';
                        notifDropdown.style.marginTop = '0';
                        notifDropdown.style.marginBottom = '8px';
                    }
                }, 10);
            }
        });

        function markAsRead(notifId) {
            fetch('mark_notification.php?id=' + notifId, { method: 'GET' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const card = document.getElementById('notif-' + notifId);
                        if (card) {
                            card.classList.remove('unread');
                        }
                        location.reload();
                    }
                })
                .catch(err => console.error('Error:', err));
        }

        function markAllAsRead(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            fetch('mark_all_notifications.php', { method: 'GET' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hapus class unread dari semua notifikasi
                        const unreadItems = document.querySelectorAll('.notif-item.unread');
                        unreadItems.forEach(item => {
                            item.classList.remove('unread');
                            // Hapus dot indicator
                            const dot = item.querySelector('.notif-dot');
                            if (dot) dot.remove();
                            // Update background icon
                            const icon = item.querySelector('.notif-icon');
                            if (icon) {
                                icon.style.background = '#f0f4f8';
                            }
                        });
                        
                        // Update badge notifikasi di tombol
                        const badge = document.querySelector('.notif-badge');
                        if (badge) {
                            badge.remove();
                        }
                        
                        // Sembunyikan tombol "Tandai semua"
                        const markAllBtn = document.querySelector('.btn-mark-all');
                        if (markAllBtn) {
                            markAllBtn.style.display = 'none';
                        }
                    } else {
                        console.error('Error:', data.message);
                        alert('Gagal menandai semua notifikasi sebagai sudah dibaca');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Terjadi kesalahan saat menandai notifikasi');
                });
        }
    </script>
</body>
</html>
