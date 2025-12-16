<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Deteksi apakah kolom user_id ada
$hasUserIdCol = false;
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'user_id'");
if ($colCheck && mysqli_num_rows($colCheck) > 0) $hasUserIdCol = true;

// Get all notifications (support dua skema)
$notifications = null;
if ($hasUserIdCol) {
    $stmtNotif = $conn->prepare("SELECT * FROM notifications WHERE (recipient_user_id = ? OR user_id = ?) ORDER BY created_at DESC");
    if ($stmtNotif) {
        $stmtNotif->bind_param('ii', $user_id, $user_id);
        $stmtNotif->execute();
        $notifications = method_exists($stmtNotif, 'get_result') ? $stmtNotif->get_result() : null;
    }
} else {
    $stmtNotif = $conn->prepare("SELECT * FROM notifications WHERE (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ?) ORDER BY created_at DESC");
    if ($stmtNotif) {
        $stmtNotif->bind_param('i', $user_id);
        $stmtNotif->execute();
        $notifications = method_exists($stmtNotif, 'get_result') ? $stmtNotif->get_result() : null;
    }
}

// Helper untuk normalisasi pesan jadwal interview (samakan dengan email/admin)
function normalizeScheduleMessage($conn, $notif) {
    $title = $notif['title'] ?? '';
    $appId = (int)($notif['application_id'] ?? 0);
    $isScheduleNotif = $appId > 0 && stripos($title, 'jadwal interview') !== false;

    if (!$isScheduleNotif) return $notif['message'];

    $stmt = $conn->prepare("SELECT interview_date, interview_time, interview_location, interview_notes FROM applications WHERE id = ? LIMIT 1");
    if (!$stmt) return $notif['message'];

    $stmt->bind_param('i', $appId);
    $stmt->execute();
    $res = method_exists($stmt, 'get_result') ? $stmt->get_result() : null;
    $msg = $notif['message'];

    if ($res && ($row = $res->fetch_assoc())) {
        $datePart = !empty($row['interview_date']) ? date('d M Y', strtotime($row['interview_date'])) : '-';
        $timePart = !empty($row['interview_time']) ? date('H:i', strtotime($row['interview_time'])) : '00:00';
        $locPart  = !empty($row['interview_location']) ? $row['interview_location'] : '-';
        $msg = "Jadwal: {$datePart} {$timePart} WIB. Lokasi: {$locPart}.";
        if (!empty($row['interview_notes'])) {
            $msg .= " Catatan: " . $row['interview_notes'];
        }
    }
    $stmt->close();
    return $msg;
}

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
    <title>Notifikasi - Dashboard User</title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="vacancies.php">💼 Lowongan</a>
            <a href="applications.php">📝 Lamaran</a>
        </div>
        <a href="../logout.php" class="logout">Keluar</a>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="notifications-header">
                <h1>📬 Semua Notifikasi</h1>
                <?php 
                    $unread = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE recipient_user_id = $user_id AND is_read = 0")->fetch_assoc()['total'];
                    if ($unread > 0):
                ?>
                    <button class="btn-mark-all" onclick="markAllAsRead()">
                        Tandai semua (<?= $unread ?>)
                    </button>
                <?php endif; ?>
            </div>

            <div class="content-box">
                <?php if ($notifications && $notifications->num_rows > 0): ?>
                    <div class="notifications-list">
                        <?php while ($notif = $notifications->fetch_assoc()): ?>
                            <div class="notification-card <?= !$notif['is_read'] ? 'unread' : '' ?>" id="notif-<?= $notif['id'] ?>">
                                <div class="notif-left">
                                    <div class="notif-icon">
                                        <?php
                                            $notifType = $notif['type'] ?? '';
                                            if (strpos($notifType, 'approved') !== false) {
                                                echo '✅';
                                            } elseif (strpos($notifType, 'accepted') !== false) {
                                                echo '🎉';
                                            } elseif (strpos($notifType, 'rejected') !== false) {
                                                echo '❌';
                                            } elseif (strpos($notifType, 'interview') !== false) {
                                                echo '📅';
                                            } else {
                                                echo '📢';
                                            }
                                        ?>
                                    </div>
                                </div>

                                <div class="notif-middle">
                                    <h4 class="notif-message"><?= htmlspecialchars(normalizeScheduleMessage($conn, $notif)) ?></h4>
                                    <p class="notif-time"><?= getTimeAgo($notif['created_at']) ?></p>
                                </div>

                                <div class="notif-right">
                                    <?php if (!$notif['is_read']): ?>
                                        <button class="btn-mark" onclick="markAsRead(<?= $notif['id'] ?>)" title="Tandai sudah dibaca">
                                            ✓
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn-delete" onclick="deleteNotif(<?= $notif['id'] ?>)" title="Hapus">
                                        ×
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>Tidak ada notifikasi</p>
                    </div>
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

        .top-bar {
            background: linear-gradient(135deg, var(--primary), #2c44b3);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
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
            font-size: 14px;
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
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.3s;
        }

        .logout:hover {
            background: #dc2f45;
        }

        .main-content {
            padding-top: 30px;
            padding-bottom: 30px;
            min-height: calc(100vh - 70px);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .notifications-header h1 {
            margin: 0;
            font-size: 28px;
            color: var(--text-dark);
        }

        .btn-mark-all {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-mark-all:hover {
            background: #2c44b3;
            transform: translateY(-2px);
        }

        .content-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notification-card {
            display: flex;
            gap: 16px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 4px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .notification-card:hover {
            background: #f1f5ff;
            border-left-color: var(--primary);
        }

        .notification-card.unread {
            background: #f1f6ff;
            border-left-color: var(--primary);
            box-shadow: 0 2px 8px rgba(67,97,238,0.1);
        }

        .notif-left {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-shrink: 0;
        }

        .notif-icon {
            font-size: 28px;
            min-width: 40px;
            text-align: center;
        }

        .notif-middle {
            flex: 1;
            min-width: 0;
        }

        .notif-message {
            margin: 0 0 6px 0;
            font-size: 15px;
            font-weight: 500;
            color: var(--text-dark);
            word-break: break-word;
        }

        .notif-time {
            margin: 0;
            font-size: 13px;
            color: var(--text-light);
        }

        .notif-right {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
        }

        .btn-mark,
        .btn-delete {
            background: none;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
            color: var(--text-light);
        }

        .btn-mark:hover {
            background: rgba(14,159,110,0.1);
            color: var(--success);
        }

        .btn-delete:hover {
            background: rgba(239,35,60,0.1);
            color: var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .empty-state p {
            margin: 0;
            font-size: 16px;
            color: var(--text-light);
        }

        @media (max-width: 640px) {
            .notifications-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .notification-card {
                flex-direction: column;
                gap: 12px;
            }

            .notif-right {
                justify-content: flex-start;
            }
        }
    </style>

    <script>
        function markAsRead(notifId) {
            fetch('./mark_notification.php?id=' + notifId, { method: 'GET' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const card = document.getElementById('notif-' + notifId);
                        card.classList.remove('unread');
                    }
                })
                .catch(err => console.error('Error:', err));
        }

        function markAllAsRead() {
            fetch('./mark_all_notifications.php', { method: 'GET' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => console.error('Error:', err));
        }

        function deleteNotif(notifId) {
            if (confirm('Hapus notifikasi ini?')) {
                fetch('./delete_notification.php?id=' + notifId, { method: 'GET' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const card = document.getElementById('notif-' + notifId);
                            card.style.opacity = '0';
                            card.style.transition = 'opacity 0.3s ease';
                            setTimeout(() => card.remove(), 300);
                        } else {
                            alert('Gagal menghapus notifikasi');
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert('Terjadi kesalahan');
                    });
            }
        }
    </script>
</body>
</html>