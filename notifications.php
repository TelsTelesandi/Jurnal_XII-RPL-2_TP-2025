<?php
// Simple notifications list + mark-read endpoint for admin & user
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

// require login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /cvjurnal/login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

// ambil notifikasi untuk user (user melihat hanya notifikasi yang ditujukan padanya atau all/user)
$notes = [];
$tbl = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
if ($tbl && mysqli_num_rows($tbl) > 0) {
    $stmt = $conn->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ?) ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = method_exists($stmt,'get_result') ? $stmt->get_result() : null;
        $notes = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    } else {
        // fallback raw query
        $q = "SELECT id, title, message, is_read, created_at FROM notifications WHERE is_read IN (0,1) ORDER BY created_at DESC";
        $r = mysqli_query($conn, $q);
        if ($r) $notes = mysqli_fetch_all($r, MYSQLI_ASSOC);
    }
}

// ganti <notif_id> dan <app_id> sesuai data Anda
// UPDATE notifications SET application_id = <app_id> WHERE id = <notif_id>;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Notifications</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container mt-4">
    <h3>Notifications</h3>
    <?php if (empty($notes)): ?>
        <div class="alert alert-info">No notifications.</div>
    <?php else: foreach ($notes as $n): ?>
        <div class="card mb-2 <?php echo empty($n['is_read']) ? 'border-primary' : ''; ?>">
            <div class="card-body">
                <h6><?php echo htmlspecialchars($n['title'] ?? ''); ?></h6>
                <p class="small text-muted"><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></p>
                <p><?php echo nl2br(htmlspecialchars($n['message'] ?? '')); ?></p>
                <?php
                    $aid = (int)($n['application_id'] ?? 0);
                    if ($aid > 0) {
                        // pilih URL sesuai role (admin melihat di admin, user di sisi user)
                        $viewUrl = (($_SESSION['role'] ?? '') === 'admin')
                            ? "/cvjurnal/admin/view_application.php?id={$aid}"
                            : "/cvjurnal/user/view_application.php?id={$aid}";
                        // tampilkan sebagai tombol (form GET) agar benar-benar button, bukan link teks
                        echo '<form method="get" action="' . htmlspecialchars($viewUrl) . '" style="display:inline-block;margin-right:6px;">';
                        echo '<button type="submit" class="btn btn-sm btn-primary">View related application</button>';
                        echo '</form>';
                    }
                ?>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
</body>
</html>